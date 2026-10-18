<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/helpers.php';
require_once __DIR__ . '/../app/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'requiere_login' => true,
        'message' => 'Debes iniciar sesión.'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$producto_id = (int)($data['producto_id'] ?? 0);
$cantidad    = (int)($data['cantidad'] ?? 1);
$observaciones = trim($data['observaciones'] ?? '');

if ($producto_id <= 0 || $cantidad <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$pdo = null;
try {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    // 1. Producto con lock
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id AND estado = 'activo' FOR UPDATE");
    $stmt->execute([':id' => $producto_id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        throw new Exception('Producto no encontrado o inactivo.');
    }

    if ((int)$producto['stock'] < $cantidad) {
        throw new Exception('Stock insuficiente. Disponible: ' . $producto['stock']);
    }

    $precioVenta = (float)$producto['precio'];
    $precioCompra = (float)$producto['precio_compra'];
    $subtotal = $precioVenta * $cantidad;
    $iva = $subtotal * 0.19;
    $total = $subtotal + $iva;

    // 2. Generar número de factura
    $anio = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM ventas WHERE YEAR(fecha_registro) = $anio");
    $num = (int)$stmt->fetchColumn() + 1;
    $numeroFactura = 'VENT-' . $anio . '-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);

    // 3. Insertar venta
    $stmt = $pdo->prepare("
        INSERT INTO ventas (numero_factura, usuario_id, subtotal, iva, total, estado, observaciones)
        VALUES (:nf, :uid, :s, :i, :t, 'pagada', :o)
    ");
    $stmt->execute([
        ':nf'  => $numeroFactura,
        ':uid' => $_SESSION['usuario_id'],
        ':s'   => $subtotal,
        ':i'   => $iva,
        ':t'   => $total,
        ':o'   => $observaciones,
    ]);
    $venta_id = (int)$pdo->lastInsertId();

    // 4. Detalle
    $stmt = $pdo->prepare("
        INSERT INTO venta_detalle 
            (venta_id, producto_id, cantidad, precio_unitario, precio_compra_unitario, subtotal)
        VALUES (:vid, :pid, :q, :pu, :pc, :s)
    ");
    $stmt->execute([
        ':vid' => $venta_id,
        ':pid' => $producto_id,
        ':q'   => $cantidad,
        ':pu'  => $precioVenta,
        ':pc'  => $precioCompra,
        ':s'   => $subtotal,
    ]);

    // 5. Descontar stock
    $stockAnt = (int)$producto['stock'];
    $stockNuevo = $stockAnt - $cantidad;

    $pdo->prepare("UPDATE productos SET stock = :s WHERE id = :id")
        ->execute([':s' => $stockNuevo, ':id' => $producto_id]);

    // 6. Movimiento
    $pdo->prepare("
        INSERT INTO movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo, usuario_id)
        VALUES (:p, 'salida', :c, :sa, :sn, :m, :u)
    ")->execute([
        ':p'  => $producto_id,
        ':c'  => $cantidad,
        ':sa' => $stockAnt,
        ':sn' => $stockNuevo,
        ':m'  => 'Venta ' . $numeroFactura,
        ':u'  => $_SESSION['usuario_id'],
    ]);
    $codigoVendedor = 'XM-' . strtoupper(substr(md5((string)$_SESSION['usuario_id']), 0, 6));

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'venta_id' => $venta_id,
        'numero_factura' => $numeroFactura,
        'message' => 'Venta registrada correctamente.'
    ]);

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log("Error al guardar venta: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}