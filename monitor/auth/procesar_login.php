<?php
// ============================================================
// Procesa el POST del formulario de login
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/bootstrap.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('index.php?route=login');
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validaciones básicas
if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Debes completar todos los campos.';
    $_SESSION['login_email'] = $email;
    redirigir('index.php?route=login');
}

try {
    $pdo = Database::getReplica();

    // Buscar usuario
    $stmt = $pdo->prepare(
        "SELECT id, nombre_completo, email, password_hash, rol,
                estado, intentos_fallidos, bloqueado_hasta
         FROM monitor_usuarios
         WHERE email = :email
         LIMIT 1"
    );
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    // ==== Verificaciones de seguridad ====

    // 1) ¿Existe el usuario?
    if (!$usuario) {
        registrarAcceso(null, $email, 'login_fallido');
        $_SESSION['login_error'] = 'Credenciales incorrectas.';
        $_SESSION['login_email'] = $email;
        redirigir('index.php?route=login');
    }

    // 2) ¿Está activo?
    if ($usuario['estado'] !== 'activo') {
        registrarAcceso((int)$usuario['id'], $email, 'login_fallido');
        $_SESSION['login_error'] = 'Esta cuenta está desactivada.';
        redirigir('index.php?route=login');
    }

    // 3) ¿Está bloqueado temporalmente?
    if ($usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
        registrarAcceso((int)$usuario['id'], $email, 'bloqueo');
        $minutos = ceil((strtotime($usuario['bloqueado_hasta']) - time()) / 60);
        $_SESSION['login_error'] = "Cuenta bloqueada. Intenta en {$minutos} minutos.";
        redirigir('index.php?route=login');
    }

    // 4) ¿La contraseña es correcta?
    if (!password_verify($password, $usuario['password_hash'])) {
        $nuevosIntentos = (int)$usuario['intentos_fallidos'] + 1;

        $bloqueadoHasta = null;
        if ($nuevosIntentos >= 5) {
            $bloqueadoHasta = date('Y-m-d H:i:s', time() + 15 * 60);
            registrarAcceso((int)$usuario['id'], $email, 'bloqueo');
        } else {
            registrarAcceso((int)$usuario['id'], $email, 'login_fallido');
        }

        $stmt = $pdo->prepare(
            "UPDATE monitor_usuarios
             SET intentos_fallidos = :intentos, bloqueado_hasta = :bloqueado
             WHERE id = :id"
        );
        $stmt->execute([
            ':intentos'  => $nuevosIntentos,
            ':bloqueado' => $bloqueadoHasta,
            ':id'        => $usuario['id'],
        ]);

        $_SESSION['login_error'] = $bloqueadoHasta
            ? 'Demasiados intentos fallidos. Cuenta bloqueada por 15 minutos.'
            : 'Credenciales incorrectas.';
        $_SESSION['login_email'] = $email;
        redirigir('index.php?route=login');
    }

    // ==== Login exitoso ====

    // Regenerar ID de sesión (previene session fixation)
    session_regenerate_id(true);

    // Guardar datos en sesión
    $_SESSION['monitor_usuario_id'] = (int)$usuario['id'];
    $_SESSION['monitor_usuario']    = $usuario['nombre_completo'];
    $_SESSION['monitor_email']      = $usuario['email'];
    $_SESSION['monitor_rol']        = $usuario['rol'];
    $_SESSION['ultima_actividad']   = time();

    // Resetear contadores
    $stmt = $pdo->prepare(
        "UPDATE monitor_usuarios
         SET intentos_fallidos = 0,
             bloqueado_hasta = NULL,
             ultimo_login = NOW()
         WHERE id = :id"
    );
    $stmt->execute([':id' => $usuario['id']]);

    registrarAcceso((int)$usuario['id'], $email, 'login_ok');

    redirigir('index.php?route=dashboard');

} catch (PDOException $e) {
    error_log('Error en login: ' . $e->getMessage());
    $_SESSION['login_error'] = 'Error interno. Intenta de nuevo.';
    redirigir('index.php?route=login');
}