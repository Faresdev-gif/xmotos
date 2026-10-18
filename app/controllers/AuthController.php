<?php
/**
 * XMOTOS - Controlador de Autenticación
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

class AuthController {

    // =========================================================
    // REGISTRO
    // =========================================================
    public function registrar(): void {
        $errors = [];

        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $telefono        = trim($_POST['telefono'] ?? '');
        $rol             = trim($_POST['rol'] ?? 'cliente');
        $password        = $_POST['password'] ?? '';
        $confirm         = $_POST['confirm_password'] ?? '';
        $terms           = isset($_POST['terms']);
        $confirm_rol     = isset($_POST['confirm_rol']);

        if ($nombre_completo === '') $errors[] = 'El nombre completo es obligatorio.';
        if ($email === '') $errors[] = 'El correo electrónico es obligatorio.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El correo no tiene un formato válido.';
        if ($telefono === '') $errors[] = 'El teléfono es obligatorio.';
        if (strlen($password) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        if ($password !== $confirm) $errors[] = 'Las contraseñas no coinciden.';
        if (!$terms) $errors[] = 'Debes aceptar los términos y condiciones.';
        if (in_array($rol, ['vendedor', 'administrador'], true) && !$confirm_rol) {
            $errors[] = 'Debes confirmar la verificación de seguridad para este rol.';
        }
        if (!in_array($rol, ['cliente', 'vendedor', 'administrador'], true)) {
            $errors[] = 'El tipo de usuario no es válido.';
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Este correo ya está registrado.';
            }
        } catch (PDOException $e) {
            error_log("Error al verificar correo: " . $e->getMessage());
            $errors[] = 'Error al procesar la solicitud. Intenta de nuevo.';
        }

        if (!empty($errors)) {
            $this->renderFormulario($errors, '');
            return;
        }

        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $tokenVerificacion = bin2hex(random_bytes(32));

            $pdo = Database::getConnection();
            $sql = "INSERT INTO usuarios 
                    (nombre_completo, email, telefono, password, rol, token_verificacion, verificado, fecha_registro) 
                    VALUES (:nombre, :email, :telefono, :password, :rol, :token, 0, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre'    => $nombre_completo,
                ':email'     => $email,
                ':telefono'  => $telefono,
                ':password'  => $passwordHash,
                ':rol'       => $rol,
                ':token'     => $tokenVerificacion,
            ]);

            $_SESSION['registro_exitoso'] = [
                'email'  => $email,
                'token'  => $tokenVerificacion,
                'nombre' => $nombre_completo,
            ];

            header('Location: ' . base_url('registro_exitoso.php'));
            exit;

        } catch (PDOException $e) {
            error_log("Error al insertar usuario: " . $e->getMessage());
            $errors[] = 'No fue posible crear la cuenta. Intenta de nuevo.';
            $this->renderFormulario($errors, '');
        }
    }

    // =========================================================
    // LOGIN
    // =========================================================
    public function login(): void {
        $errors = [];

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rol      = trim($_POST['rol'] ?? 'cliente');

        if ($email === '') $errors[] = 'El correo es obligatorio.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El correo no es válido.';
        if ($password === '') $errors[] = 'La contraseña es obligatoria.';

        if (empty($errors)) {
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                if (!$user) {
                    $errors[] = 'Credenciales incorrectas.';
                } elseif (!password_verify($password, $user['password'])) {
                    $errors[] = 'Credenciales incorrectas.';
                } elseif ($user['rol'] !== $rol) {
                    $errors[] = 'No tienes permiso para acceder como ' . $rol . '.';
                } elseif ((int)$user['verificado'] === 0) {
                    $errors[] = 'Debes verificar tu correo antes de iniciar sesión.';
                } else {
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['email']      = $user['email'];
                    $_SESSION['rol']        = $user['rol'];
                    $_SESSION['nombre']     = $user['nombre_completo'];

                    // =========================================
                    // REDIRECCIÓN SEGÚN ROL
                    // =========================================
                    if ($user['rol'] === 'administrador') {
                        header('Location: ' . base_url('dashboard/index.php'));
                    } elseif ($user['rol'] === 'vendedor') {
                        header('Location: ' . base_url('dashboard/index.php'));
                    } else {
                        // Cliente
                        header('Location: ' . base_url('cliente/index.php'));
                    }
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Error en login: " . $e->getMessage());
                $errors[] = 'Error al procesar el login. Intenta de nuevo.';
            }
        }

        $this->renderLogin($errors, $rol);
    }

    // =========================================================
    // RENDERIZADO
    // =========================================================
    private function renderFormulario(array $errors, string $success): void {
        $pageTitle = 'Crear Cuenta';
        require_once __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/auth/registro.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    private function renderLogin(array $errors, string $rol): void {
        $titulos = [
            'cliente'        => 'Acceso Cliente',
            'vendedor'       => 'Acceso Vendedor',
            'administrador'  => 'Acceso Administrador',
        ];
        $pageTitle = $titulos[$rol] ?? 'Acceso';

        require_once __DIR__ . '/../views/layouts/header.php';
        ?>
        <!-- HERO con imagen de moto -->
        <header class="hero-x" style="min-height: 40vh; padding: 3rem 1rem;">
          <div class="container position-relative" style="z-index: 2;">
            <div class="row justify-content-center text-center">
              <div class="col-12 col-lg-8">
                <span class="x-badge mb-3">XMOTOS · Acceso</span>
                <h1 class="display-5 fw-bold mb-3"><?= $titulos[$rol] ?></h1>
                <p class="mb-0 fs-5">Ingresa tus credenciales para continuar.</p>
              </div>
            </div>
          </div>
        </header>

        <section class="x-section">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-12 col-lg-6 col-xl-5">
                <?php foreach ($errors as $error): ?>
                  <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i> <?= e($error) ?>
                  </div>
                <?php endforeach; ?>

                <div class="x-card p-4 p-md-5">
                  <form method="POST" action="<?= base_url('login.php?rol=' . $rol) ?>">
                    <input type="hidden" name="rol" value="<?= e($rol) ?>">

                    <div class="mb-3">
                      <label for="email" class="form-label">Correo electrónico</label>
                      <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                      <label for="password" class="form-label">Contraseña</label>
                      <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="d-grid">
                      <button type="submit" class="btn btn-x-primary btn-lg">Iniciar sesión</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>
        <?php
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    // =========================================================
    // VERIFICACIÓN DE CORREO
    // =========================================================
    public function verificar(): void {
        $token = $_GET['token'] ?? '';
        if ($token === '') {
            die('Token no proporcionado.');
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE token_verificacion = :token AND verificado = 0 LIMIT 1");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch();

            // ← VALIDAR PRIMERO
            if (!$user) {
                die('Token inválido o ya verificado.');
            }

            // ← ACTUALIZAR DESPUÉS
            $stmt = $pdo->prepare("UPDATE usuarios SET verificado = 1, token_verificacion = NULL WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);

            echo '<div style="font-family: Inter, sans-serif; text-align: center; padding: 4rem; background: #1A2430; color: #F5F7FA;">';
            echo '<h1 style="color: #B8FF24;">✅ Cuenta verificada</h1>';
            echo '<p>Tu cuenta ha sido verificada correctamente.</p>';
            echo '<a href="' . base_url('login.php?rol=' . $user['rol']) . '" style="color: #B8FF24;">Iniciar sesión</a>';
            echo '</div>';

        } catch (PDOException $e) {
            error_log("Error al verificar: " . $e->getMessage());
            die('Error al verificar la cuenta.');
        }
    }
}