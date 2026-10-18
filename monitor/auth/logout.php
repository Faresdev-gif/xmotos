<?php
// ============================================================
// Cierra la sesión del monitor
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/bootstrap.php';

if (isset($_SESSION['monitor_usuario_id'])) {
    registrarAcceso(
        (int)$_SESSION['monitor_usuario_id'],
        $_SESSION['monitor_email'] ?? '',
        'logout'
    );
}

// Destruir todas las variables de sesión
$_SESSION = [];

// Borrar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

redirigir('index.php?route=login&msg=logout');