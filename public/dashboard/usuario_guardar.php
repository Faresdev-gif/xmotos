<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';

$pdo = Database::getConnection();

$accion = $_GET['accion'] ?? '';
$id = (int)($_GET['id'] ?? 0);

// =====================================================
// ACCIÓN: TOGGLE ACTIVO/INACTIVO (botón basura)
// =====================================================
if ($accion === 'toggle_estado' && $id > 0) {
    // No permitir que el admin se inactive a sí mismo
    if ($id === (int)$_SESSION['usuario_id']) {
        header('Location: ' . base_url('dashboard/usuarios.php?error=No puedes inactivar tu propia cuenta'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET estado = IF(estado = 'activo', 'inactivo', 'activo') 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        header('Location: ' . base_url('dashboard/usuarios.php?msg=Estado actualizado correctamente'));
        exit;
    } catch (PDOException $e) {
        error_log("Error al cambiar estado: " . $e->getMessage());
        header('Location: ' . base_url('dashboard/usuarios.php?error=No se pudo actualizar el estado'));
        exit;
    }
}

// =====================================================
// ACCIÓN: TOGGLE VERIFICADO (botón persona)
// =====================================================
if ($accion === 'toggle' && $id > 0) {
    try {
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET verificado = IF(verificado = 1, 0, 1) 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        header('Location: ' . base_url('dashboard/usuarios.php?msg=Estado de verificación actualizado'));
        exit;
    } catch (PDOException $e) {
        error_log("Error al cambiar verificación: " . $e->getMessage());
        header('Location: ' . base_url('dashboard/usuarios.php?error=No se pudo actualizar la verificación'));
        exit;
    }
}

// Si no hay acción válida, volver al listado
header('Location: ' . base_url('dashboard/usuarios.php'));
exit;
