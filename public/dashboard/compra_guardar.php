<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';

$accion = $_GET['accion'] ?? '';
$id = (int)($_GET['id'] ?? 0);

$pdo = Database::getConnection();

// CANCELAR COMPRA
if ($accion === 'cancelar' && $id > 0) {
    $pdo->beginTransaction();
    try {
        // Obtener detalles
        $stmt = $pdo->prepare("SELECT * FROM compras_detalle WHERE compra_id = :id");
        $stmt->execute([':id' => $id]);
        $detalles = $stmt->fetchAll();

        // Revertir stock
        foreach ($detalles as $d) {
            $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $d['producto_id']]);
            $prod = $stmt->fetch();
            $stockAnt = (int)$prod['stock'];
            $stockNuevo = max(0, $stockAnt - (int)$d['cantidad']);

            $pdo->prepare("UPDATE productos SET stock = :s WHERE id = :id")
                ->execute([':s' => $stockNuevo, ':id' => $d['producto_id']]);

            // Movimiento
            $pdo->prepare("
                INSERT INTO movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo, usuario_id)
                VALUES (:p, 'ajuste', :c, :sa, :sn, :m, :u)
            ")->execute([
                ':p' => $d['producto_id'],
                ':c' => $d['cantidad'],
                ':sa' => $stockAnt,
                ':sn' => $stockNuevo,
                ':m' => 'Cancelación compra #' . $id,
                ':u' => $_SESSION['usuario_id'],
            ]);
        }

        $pdo->prepare("UPDATE compras SET estado = 'cancelada' WHERE id = :id")
            ->execute([':id' => $id]);

        $pdo->commit();
        header('Location: ' . base_url('dashboard/compras.php?msg=Compra cancelada'));
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: ' . base_url('dashboard/compras.php?error=' . urlencode($e->getMessage())));
        exit;
    }
}

// REGISTRAR COMPRA
$proveedor_id = (int)($_POST['proveedor_id'] ?? 0);
$numero_factura = trim($_POST['numero_factura'] ?? '');
$producto_id = (int)($_POST['producto_id'] ?? 0);
$cantidad = (int)($_POST['cantidad'] ?? 1);
$precio_compra = (float)($_POST['precio_compra'] ?? 0);
$observaciones = trim($_POST['observaciones'] ?? '');

if ($proveedor_id <= 0 || $numero_factura === '' || $producto_id <= 0 || $cantidad <= 0 || $precio_compra <= 0) {
    header('Location: ' . base_url('dashboard/compra_form.php?error=Completa todos los campos'));
    exit;
}

$pdo->beginTransaction();
try {
    $subtotal = $precio_compra * $cantidad;
    $iva = $subtotal * 0.19;
    $total = $subtotal + $iva;

    // Insertar compra
    $stmt = $pdo->prepare("
        INSERT INTO compras (numero_factura, proveedor_id, usuario_id, subtotal, iva, total, observaciones)
        VALUES (:nf, :pid, :uid, :s, :i, :t, :o)
    ");
    $stmt->execute([
        ':nf'  => $numero_factura,
        ':pid' => $proveedor_id,
        ':uid' => $_SESSION['usuario_id'],
        ':s'   => $subtotal,
        ':i'   => $iva,
        ':t'   => $total,
        ':o'   => $observaciones,
    ]);
    $compra_id = (int)$pdo->lastInsertId();

    // Detalle
    $pdo->prepare("
        INSERT INTO compras_detalle (compra_id, producto_id, cantidad, precio_compra_unitario, subtotal)
        VALUES (:c, :p, :q, :pu, :s)
    ")->execute([
        ':c' => $compra_id,
        ':p' => $producto_id,
        ':q' => $cantidad,
        ':pu' => $precio_compra,
        ':s' => $subtotal,
    ]);

    // Actualizar stock
    $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = :id FOR UPDATE");
    $stmt->execute([':id' => $producto_id]);
    $prod = $stmt->fetch();
    $stockAnt = (int)$prod['stock'];
    $stockNuevo = $stockAnt + $cantidad;

    $pdo->prepare("UPDATE productos SET stock = :s, precio_compra = :pc WHERE id = :id")
        ->execute([':s' => $stockNuevo, ':pc' => $precio_compra, ':id' => $producto_id]);

    // Movimiento
    $pdo->prepare("
        INSERT INTO movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo, usuario_id)
        VALUES (:p, 'entrada', :c, :sa, :sn, :m, :u)
    ")->execute([
        ':p' => $producto_id,
        ':c' => $cantidad,
        ':sa' => $stockAnt,
        ':sn' => $stockNuevo,
        ':m' => 'Compra ' . $numero_factura,
        ':u' => $_SESSION['usuario_id'],
    ]);

    $pdo->commit();
    header('Location: ' . base_url('dashboard/compras.php?msg=Compra registrada correctamente'));
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: ' . base_url('dashboard/compra_form.php?error=' . urlencode($e->getMessage())));
    exit;
}