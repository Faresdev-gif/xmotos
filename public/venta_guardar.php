<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/helpers.php';
require_once __DIR__ . '/../app/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$producto_id   = (int)($data['producto_id'] ?? 0);
$cliente       = trim($data['cliente'] ?? '');
$documento     = trim($data['documento'] ?? '');
$cantidad      = (int)($data['cantidad'] ?? 1);
$observaciones = trim($data['observaciones'] ?? '');

if ($producto_id <= 0 || $cliente === '' || $documento === '' || $cantidad <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$pdo = null;
try {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT precio FROM productos WHERE id = :id");
    $stmt->execute([':id' => $producto_id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        throw new Exception('Producto no encontrado.');
    }

    $precio   = (float)$producto['precio'];
    $subtotal = $precio * $cantidad;
    $iva      = $subtotal * 0.19;
    $total    = $subtotal + $iva;

    $stmt = $pdo->prepare("
        INSERT INTO ventas (usuario_id, cliente_nombre, cliente_documento, subtotal, iva, total, observaciones)
        VALUES (:u, :cn, :cd, :s, :i, :t, :o)
    ");
    $stmt->execute([
        ':u'  => $_SESSION['usuario_id'],
        ':cn' => $cliente,
        ':cd' => $documento,
        ':s'  => $subtotal,
        ':i'  => $iva,
        ':t'  => $total,
        ':o'  => $observaciones,
    ]);
    $venta_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO venta_detalle (venta_id, producto_id, cantidad, precio_unitario, subtotal)
        VALUES (:v, :p, :c, :pu, :s)
    ");
    $stmt->execute([
        ':v'  => $venta_id,
        ':p'  => $producto_id,
        ':c'  => $cantidad,
        ':pu' => $precio,
        ':s'  => $subtotal,
    ]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al guardar la venta: ' . $e->getMessage()]);
}