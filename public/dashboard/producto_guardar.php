<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';

$pdo = Database::getConnection();

// =====================================================
// ACCIÓN: TOGGLE ACTIVO/INACTIVO
// =====================================================
if (isset($_GET['accion']) && $_GET['accion'] === 'toggle') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("UPDATE productos SET estado = IF(estado = 'activo', 'inactivo', 'activo') WHERE id = :id")
            ->execute([':id' => $id]);
    }
    header('Location: ' . base_url('dashboard/productos.php?msg=Estado actualizado'));
    exit;
}

// =====================================================
// ACCIÓN: ELIMINAR
// =====================================================
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("DELETE FROM productos WHERE id = :id")->execute([':id' => $id]);
    }
    header('Location: ' . base_url('dashboard/productos.php?msg=Producto eliminado'));
    exit;
}

// =====================================================
// ACCIÓN: GUARDAR (crear o editar)
// =====================================================
$id            = (int)($_POST['id'] ?? 0);
$categoria_id  = (int)($_POST['categoria_id'] ?? 0);
$marca         = trim($_POST['marca'] ?? '');
$modelo        = trim($_POST['modelo'] ?? '');
$cilindraje    = trim($_POST['cilindraje'] ?? '');
$precio        = (float)($_POST['precio'] ?? 0);
$precio_compra = (float)($_POST['precio_compra'] ?? 0);
$stock         = (int)($_POST['stock'] ?? 1);
$estado        = $_POST['estado'] ?? 'activo';
$imagen_actual = $_POST['imagen_actual'] ?? '';

if ($categoria_id <= 0 || $marca === '' || $modelo === '' || $precio <= 0) {
    header('Location: ' . base_url('dashboard/producto_form.php?id=' . $id . '&error=Completa los campos obligatorios'));
    exit;
}

// =====================================================
// PROCESAR IMAGEN
// =====================================================
$imagen = $imagen_actual;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $nuevoNombre = 'producto_' . time() . '.' . $ext;
        $ruta = __DIR__ . '/../../public/img/motos/' . $nuevoNombre;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
            $imagen = $nuevoNombre;
        }
    }
}

// =====================================================
// CREAR O ACTUALIZAR
// =====================================================
if ($id > 0) {
    $stmt = $pdo->prepare("
        UPDATE productos SET 
            categoria_id = :cat,
            marca = :marca,
            modelo = :modelo,
            cilindraje = :cil,
            precio = :precio,
            precio_compra = :precio_compra,
            imagen = :img,
            stock = :stock,
            estado = :estado
        WHERE id = :id
    ");
    $stmt->execute([
        ':cat'           => $categoria_id,
        ':marca'         => $marca,
        ':modelo'        => $modelo,
        ':cil'           => $cilindraje,
        ':precio'        => $precio,
        ':precio_compra' => $precio_compra,
        ':img'           => $imagen,
        ':stock'         => $stock,
        ':estado'        => $estado,
        ':id'            => $id,
    ]);
    $msg = 'Producto actualizado correctamente';
} else {
    $stmt = $pdo->prepare("
        INSERT INTO productos 
            (categoria_id, marca, modelo, cilindraje, precio, precio_compra, imagen, stock, estado)
        VALUES 
            (:cat, :marca, :modelo, :cil, :precio, :precio_compra, :img, :stock, :estado)
    ");
    $stmt->execute([
        ':cat'           => $categoria_id,
        ':marca'         => $marca,
        ':modelo'        => $modelo,
        ':cil'           => $cilindraje,
        ':precio'        => $precio,
        ':precio_compra' => $precio_compra,
        ':img'           => $imagen,
        ':stock'         => $stock,
        ':estado'        => $estado,
    ]);
    $msg = 'Producto creado correctamente';
}

header('Location: ' . base_url('dashboard/productos.php?msg=' . urlencode($msg)));
exit;