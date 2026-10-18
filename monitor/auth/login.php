<?php
// ============================================================
// Formulario de login del monitor
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['monitor_usuario_id'])) {
    header('Location: ' . MONITOR_URL . 'index.php');
    exit;
}

$mensaje = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'sesion_expirada') {
        $mensaje = 'Tu sesión expiró por inactividad. Inicia sesión de nuevo.';
    } elseif ($_GET['msg'] === 'logout') {
        $mensaje = 'Sesión cerrada correctamente.';
    }
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

$emailPrev = $_SESSION['login_email'] ?? '';
unset($_SESSION['login_email']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Monitor XMotos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= APP_URL ?>css/xmotos.css">
    <link rel="stylesheet" href="<?= MONITOR_URL ?>assets/css/monitor.css">
</head>
<body class="pagina-login">

<div class="login-contenedor">
    <div class="login-tarjeta">
        <div class="login-header">
            <i class="bi bi-speedometer2"></i>
            <h1>Monitor XMotos</h1>
            <p>Panel de observabilidad</p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-info"><?= e($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= MONITOR_URL ?>index.php?route=procesar_login" autocomplete="off">
            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" id="email" name="email"
                       value="<?= e($emailPrev) ?>"
                       required autofocus
                       class="form-control"
                       placeholder="usuario@xmotos.com">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password"
                       required
                       class="form-control"
                       placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-x-primary w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
            </button>
        </form>

        <p class="text-center mt-4 mb-0">
            <small class="text-x-muted">Acceso restringido a personal autorizado</small>
        </p>
    </div>
</div>

</body>
</html>