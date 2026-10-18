<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/helpers.php';
require_once __DIR__ . '/../app/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'requiere_login' => true,
        'message' => 'Debes iniciar sesión.'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$producto_id = (int)($data['producto_id'] ?? 0);

if ($producto_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Producto inválido.']);
    exit;
}

try {
    $pdo = Database::getConnection();

    // Verificar que el producto existe
    $stmt = $pdo->prepare("SELECT id FROM productos WHERE id = :id");
    $stmt->execute([':id' => $producto_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Producto no existe.']);
        exit;
    }

    // Insertar (ignora si ya existe)
    $stmt = $pdo->prepare("INSERT IGNORE INTO favoritos (usuario_id, producto_id) VALUES (:u, :p)");
    $stmt->execute([
        ':u' => $_SESSION['usuario_id'],
        ':p' => $producto_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Favorito agregado.'
    ]);

} catch (PDOException $e) {
    error_log("Error al agregar favorito: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al agregar a favoritos.'
    ]);
}