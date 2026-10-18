<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['cliente']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$actual = $_POST['password_actual'] ?? '';
$nueva  = $_POST['password_nueva'] ?? '';

if (strlen($nueva) < 8) {
    header('Location: ' . base_url('cliente/perfil.php?error=La contraseña debe tener al menos 8 caracteres'));
    exit;
}

$stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if (!password_verify($actual, $usuario['password'])) {
    header('Location: ' . base_url('cliente/perfil.php?error=La contraseña actual es incorrecta'));
    exit;
}

$nuevoHash = password_hash($nueva, PASSWORD_BCRYPT);
$pdo->prepare("UPDATE usuarios SET password = :p WHERE id = :id")
    ->execute([':p' => $nuevoHash, ':id' => $_SESSION['usuario_id']]);

header('Location: ' . base_url('cliente/perfil.php?msg=Contraseña actualizada correctamente'));
exit;