<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/helpers.php';
require_once __DIR__ . '/../app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . base_url('login.php?rol=cliente'));
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM favoritos WHERE usuario_id = :uid AND producto_id = :pid");
        $stmt->execute([
            ':uid' => $_SESSION['usuario_id'],
            ':pid' => $id
        ]);
    } catch (PDOException $e) {
        error_log("Error al eliminar favorito: " . $e->getMessage());
    }
}

header('Location: ' . base_url('cliente/favoritos.php'));
exit;