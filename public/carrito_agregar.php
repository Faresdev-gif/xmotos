<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../app/config/helpers.php';
require_once __DIR__ . '/../app/config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$producto_id = (int)($data['producto_id'] ?? 0);
$cantidad = (int)($data['cantidad'] ?? 1);

if ($producto_id <= 0 || $cantidad <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        INSERT INTO carrito (usuario_id, producto_id, cantidad) 
        VALUES (:u, :p, :c)
        ON DUPLICATE KEY UPDATE cantidad = cantidad + :c2
    ");
    $stmt->execute([
        ':u' => $_SESSION['usuario_id'],
        ':p' => $producto_id,
        ':c' => $cantidad,
        ':c2' => $cantidad,
    ]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al agregar al carrito.']);
}