<?php
// ============================================================
// Front controller del Monitor XMotos
// Toda petición del monitor pasa por aquí
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/bootstrap.php';

// Obtener la ruta solicitada
$route = $_GET['route'] ?? 'dashboard';

// Rutas públicas (no requieren login)
$rutasPublicas = ['login', 'procesar_login', 'logout'];

try {
    if (in_array($route, $rutasPublicas, true)) {
        // ==== Rutas públicas ====
        switch ($route) {
            case 'login':
                require_once __DIR__ . '/../auth/login.php';
                break;
            case 'procesar_login':
                require_once __DIR__ . '/../auth/procesar_login.php';
                break;
            case 'logout':
                require_once __DIR__ . '/../auth/logout.php';
                break;
        }
    } else {
        // ==== Rutas protegidas: requieren login ====
        require_once __DIR__ . '/../auth/requiere_login.php';

        switch ($route) {
            case 'dashboard':
                require_once __DIR__ . '/../pages/dashboard.php';
                break;
            case 'auditoria':
                require_once __DIR__ . '/../pages/auditoria.php';
                break;
            case 'paridad':
                require_once __DIR__ . '/../pages/paridad.php';
                break;
            case 'usuarios':
                require_once __DIR__ . '/../pages/usuarios.php';
                break;
            default:
                http_response_code(404);
                echo "Ruta no encontrada: " . e($route);
        }
    }
} catch (Throwable $e) {
    error_log('Error en front controller: ' . $e->getMessage());
    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        echo "<h1>Error</h1><pre>" . e($e->getMessage()) . "</pre>";
    } else {
        http_response_code(500);
        echo "Error interno del servidor.";
    }
}