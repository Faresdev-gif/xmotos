<?php
// ============================================================
// Middleware: requiere sesión activa
// Incluir al inicio de toda página protegida
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/bootstrap.php';

// 1) Verificar que exista sesión
if (!isset($_SESSION['monitor_usuario_id'])) {
    redirigir('index.php?route=login');
}

// 2) Verificar timeout por inactividad (30 minutos)
$timeout = 30 * 60;
if (isset($_SESSION['ultima_actividad']) &&
    (time() - $_SESSION['ultima_actividad']) > $timeout) {
    session_destroy();
    redirigir('index.php?route=login&msg=sesion_expirada');
}

// 3) Actualizar timestamp de última actividad
$_SESSION['ultima_actividad'] = time();