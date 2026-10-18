<?php
/**
 * XMOTOS - Cambiar estado de solicitud (dashboard admin)
 *
 * Recibe: ?accion=aprobar|negociacion|contactado|cancelar&id=X
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

$accion = $_GET['accion'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . base_url('dashboard/solicitudes.php'));
    exit;
}

// Mapeo de acciones a estados
$mapaEstados = [
    'aprobar'      => 'aprobado',
    'negociacion'  => 'en_negociacion',
    'cancelar'     => 'cancelado',
];

if (!isset($mapaEstados[$accion])) {
    header('Location: ' . base_url('dashboard/solicitudes.php'));
    exit;
}

$nuevoEstado = $mapaEstados[$accion];

try {
    if ($nuevoEstado === 'aprobado') {
        // Actualizar con fecha y aprobador
        $stmt = $pdo->prepare("
            UPDATE solicitudes 
            SET estado = :estado, 
                aprobado_por = :aprobado_por, 
                fecha_aprobacion = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':estado' => $nuevoEstado,
            ':aprobado_por' => $_SESSION['usuario_id'],
            ':id' => $id,
        ]);
    } else {
        // Cambio simple de estado
        $stmt = $pdo->prepare("UPDATE solicitudes SET estado = :estado WHERE id = :id");
        $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
    }

    header('Location: ' . base_url('dashboard/ver_solicitud.php?id=' . $id . '&msg=Estado actualizado'));
    exit;

} catch (PDOException $e) {
    error_log("Error al actualizar solicitud: " . $e->getMessage());
    header('Location: ' . base_url('dashboard/ver_solicitud.php?id=' . $id . '&error=No se pudo actualizar'));
    exit;
}