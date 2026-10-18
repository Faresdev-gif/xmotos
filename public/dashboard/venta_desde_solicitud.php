<?php
/**
 * XMOTOS - Convertir solicitud aprobada en venta
 *
 * Recibe: ?id=X (id de la solicitud)
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';

$pdo = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . base_url('dashboard/solicitudes.php'));
    exit;
}

// Obtener la solicitud
$stmt = $pdo->prepare("
    SELECT s.*, p.marca, p.modelo, p.precio AS producto_precio, p.precio_compra
    FROM solicitudes s
    LEFT JOIN productos p ON s.producto_id = p.id
    WHERE s.id = :id AND s.estado = 'aprobado'
");
$stmt->execute([':id' => $id]);
$sol = $stmt->fetch();

if (!$sol) {
    header('Location: ' . base_url('dashboard/solicitudes.php?error=Solicitud no encontrada o no aprobada'));
    exit;
}

if (empty($sol['producto_id'])) {
    header('Location: ' . base_url('dashboard/ver_solicitud.php?id=' . $id . '&error=La solicitud no tiene moto asociada'));
    exit;
}

try {
    $pdo->beginTransaction();

    // Verificar stock
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id AND estado = 'activo' FOR UPDATE");
    $stmt->execute([':id' => $sol['producto_id']]);
    $producto = $stmt->fetch();

    if (!$producto || (int)$producto['stock'] < 1) {
        throw new Exception('Producto no disponible o sin stock.');
    }

    $precioVenta = (float)$producto['precio'];
    $precioCompra = (float)$producto['precio_compra'];
    $cantidad = 1;
    $subtotal = $precioVenta * $cantidad;
    $iva = $subtotal * 0.19;
    $total = $subtotal + $iva;

    // Generar número de factura
    $anio = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM ventas WHERE YEAR(fecha_registro) = $anio");
    $num = (int)$stmt->fetchColumn() + 1;
    $numeroFactura = 'VENT-' . $anio . '-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);

    // Crear venta
    $stmt = $pdo->prepare("
        INSERT INTO ventas (numero_factura, usuario_id, subtotal, iva, total, estado, observaciones)
        VALUES (:nf, :uid, :s, :i, :t, 'pagada', :obs)
    ");
    $stmt->execute([
        ':nf' => $numeroFactura,
        ':uid' => $sol['usuario_id'],
        ':s' => $subtotal,
        ':i' => $iva,
        ':t' => $total,
        ':obs' => 'Generada desde solicitud #' . $id,
    ]);
    $venta_id = (int)$pdo->lastInsertId();

    // Detalle
    $stmt = $pdo->prepare("
        INSERT INTO venta_detalle 
            (venta_id, producto_id, cantidad, precio_unitario, precio_compra_unitario, subtotal)
        VALUES (:v, :p, :q, :pu, :pc, :s)
    ");
    $stmt->execute([
        ':v' => $venta_id,
        ':p' => $producto['id'],
        ':q' => $cantidad,
        ':pu' => $precioVenta,
        ':pc' => $precioCompra,
        ':s' => $subtotal,
    ]);

    // Descontar stock
    $stockAnt = (int)$producto['stock'];
    $stockNuevo = $stockAnt - $cantidad;

    $pdo->prepare("UPDATE productos SET stock = :s WHERE id = :id")
        ->execute([':s' => $stockNuevo, ':id' => $producto['id']]);

    // Movimiento
    $pdo->prepare("
        INSERT INTO movimientos_stock 
            (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo, usuario_id)
        VALUES (:p, 'salida', :c, :sa, :sn, :m, :u)
    ")->execute([
        ':p' => $producto['id'],
        ':c' => $cantidad,
        ':sa' => $stockAnt,
        ':sn' => $stockNuevo,
        ':m' => 'Venta ' . $numeroFactura . ' desde solicitud #' . $id,
        ':u' => $_SESSION['usuario_id'],
    ]);

    // Cambiar estado de la solicitud a "cerrado"
    $pdo->prepare("UPDATE solicitudes SET estado = 'cerrado' WHERE id = :id")
        ->execute([':id' => $id]);

    $pdo->commit();

    header('Location: ' . base_url('dashboard/ventas.php?msg=Venta ' . $numeroFactura . ' registrada correctamente'));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Error al registrar venta desde solicitud: " . $e->getMessage());
    header('Location: ' . base_url('dashboard/ver_solicitud.php?id=' . $id . '&error=' . urlencode($e->getMessage())));
    exit;
}