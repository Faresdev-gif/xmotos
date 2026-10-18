<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/helpers.php';
require_once __DIR__ . '/../app/config/database.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . base_url('login.php?rol=cliente'));
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('cliente/pagos.php'));
    exit;
}

// Capturar datos
$producto_id  = (int)($_POST['producto_id'] ?? 0);
$metodo       = trim($_POST['metodo'] ?? 'pse');
$observaciones= trim($_POST['observaciones'] ?? '');

if ($producto_id <= 0) {
    header('Location: ' . base_url('cliente/pagos.php?error=Producto no válido'));
    exit;
}

$pdo = null;
try {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    // 1. Obtener producto con lock
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id AND estado = 'activo' FOR UPDATE");
    $stmt->execute([':id' => $producto_id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        throw new Exception('Producto no encontrado o inactivo.');
    }

    if ((int)$producto['stock'] < 1) {
        throw new Exception('Producto sin stock disponible.');
    }

    $precioVenta = (float)$producto['precio'];
    $precioCompra = (float)$producto['precio_compra'];
    $subtotal = $precioVenta;
    $iva = $subtotal * 0.19;
    $total = $subtotal + $iva;

    // 2. Generar número de factura
    $anio = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM ventas WHERE YEAR(fecha_registro) = $anio");
    $num = (int)$stmt->fetchColumn() + 1;
    $numeroFactura = 'VENT-' . $anio . '-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);

    // 3. Insertar venta
    $stmt = $pdo->prepare("
        INSERT INTO ventas 
            (numero_factura, usuario_id, subtotal, iva, total, estado, observaciones)
        VALUES 
            (:nf, :uid, :s, :i, :t, 'pagada', :o)
    ");
    $stmt->execute([
        ':nf'  => $numeroFactura,
        ':uid' => $_SESSION['usuario_id'],
        ':s'   => $subtotal,
        ':i'   => $iva,
        ':t'   => $total,
        ':o'   => 'Pago con ' . strtoupper($metodo) . '. ' . $observaciones,
    ]);
    $venta_id = (int)$pdo->lastInsertId();

    // 4. Insertar detalle
    $stmt = $pdo->prepare("
        INSERT INTO venta_detalle 
            (venta_id, producto_id, cantidad, precio_unitario, precio_compra_unitario, subtotal)
        VALUES 
            (:vid, :pid, 1, :pu, :pc, :s)
    ");
    $stmt->execute([
        ':vid' => $venta_id,
        ':pid' => $producto_id,
        ':pu'  => $precioVenta,
        ':pc'  => $precioCompra,
        ':s'   => $subtotal,
    ]);

    // 5. Descontar stock
    $stockAnterior = (int)$producto['stock'];
    $stockNuevo = $stockAnterior - 1;

    $pdo->prepare("UPDATE productos SET stock = :s WHERE id = :id")
        ->execute([':s' => $stockNuevo, ':id' => $producto_id]);

    // 6. Registrar movimiento
    $pdo->prepare("
        INSERT INTO movimientos_stock 
            (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo, usuario_id)
        VALUES 
            (:p, 'salida', 1, :sa, :sn, :m, :u)
    ")->execute([
        ':p'  => $producto_id,
        ':sa' => $stockAnterior,
        ':sn' => $stockNuevo,
        ':m'  => 'Venta ' . $numeroFactura,
        ':u'  => $_SESSION['usuario_id'],
    ]);

    $pdo->commit();

    // Guardar datos en sesión para mostrar en la confirmación
    $_SESSION['venta_exitosa'] = [
        'numero_factura' => $numeroFactura,
        'total'          => $total,
        'metodo'         => $metodo,
        'venta_id'       => $venta_id,
    ];

    header('Location: ' . base_url('cliente/pago_exitoso.php'));
    exit;

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log("Error al procesar pago: " . $e->getMessage());
    header('Location: ' . base_url('cliente/pagos.php?error=' . urlencode($e->getMessage())));
    exit;
}