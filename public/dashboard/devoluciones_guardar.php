<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';

$pdo = Database::getConnection();

$accion = $_GET['accion'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($id > 0 && in_array($accion, ['aprobar', 'rechazar'])) {
    $nuevoEstado = $accion === 'aprobar' ? 'aprobada' : 'rechazada';
    $stmt = $pdo->prepare("UPDATE devoluciones SET estado = :estado WHERE id = :id");
    $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);

    header('Location: ' . base_url('dashboard/devoluciones.php?msg=Devolución ' . $nuevoEstado));
    exit;
}

header('Location: ' . base_url('dashboard/devoluciones.php'));
exit;