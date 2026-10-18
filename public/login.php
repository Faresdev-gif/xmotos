<?php
/**
 * XMOTOS - Login unificado
 *
 * Recibe ?rol=cliente|vendedor|administrador para mostrar el título correcto.
 * Procesa POST contra AuthController->login()
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/config/helpers.php';

// Si es POST, procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    (new AuthController())->login();
    exit;
}

// Determinar el rol para el título
$rol = $_GET['rol'] ?? 'cliente';
$rolesValidos = ['cliente', 'vendedor', 'administrador'];
if (!in_array($rol, $rolesValidos, true)) {
    $rol = 'cliente';
}

$titulos = [
    'cliente'        => 'Acceso Cliente',
    'vendedor'       => 'Acceso Vendedor',
    'administrador'  => 'Acceso Administrador',
];

$pageTitle = $titulos[$rol];
require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<!-- HERO DEL LOGIN -->
<header class="hero-x" style="min-height: auto; padding: 3rem 1rem;">
  <div class="container position-relative" style="z-index: 2;">
    <div class="row justify-content-center text-center">
      <div class="col-12 col-lg-8">
        <span class="x-badge mb-3">XMOTOS · Acceso</span>
        <h1 class="display-5 fw-bold mb-3"><?= e($titulos[$rol]) ?></h1>
        <p class="mb-0 text-x-muted fs-5">
          Ingresa tus credenciales para continuar.
        </p>
      </div>
    </div>
  </div>
</header>

<!-- FORMULARIO DE LOGIN -->
<section class="x-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-6 col-xl-5">
        <div class="x-card p-4 p-md-5">
          <form method="POST" action="<?= base_url('login.php?rol=' . $rol) ?>" novalidate>

            <input type="hidden" name="rol" value="<?= e($rol) ?>">

            <!-- Correo -->
            <div class="mb-3">
              <label for="email" class="form-label">
                <i class="bi bi-envelope text-x-primary me-1"></i> Correo electrónico
              </label>
              <input type="email" class="form-control" id="email" name="email" 
                     placeholder="ejemplo@correo.com" required>
            </div>

            <!-- Contraseña -->
            <div class="mb-3">
              <label for="password" class="form-label">
                <i class="bi bi-lock text-x-primary me-1"></i> Contraseña
              </label>
              <input type="password" class="form-control" id="password" name="password" 
                     placeholder="Tu contraseña" required>
            </div>

            <!-- Botón -->
            <div class="d-grid mb-3">
              <button type="submit" class="btn btn-x-primary btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar sesión
              </button>
            </div>

            <!-- Enlace a registro -->
            <div class="text-center">
              <span class="text-x-muted">¿No tienes cuenta?</span>
              <a href="<?= base_url('registro.php') ?>" class="text-x-primary fw-bold">Regístrate aquí</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../app/views/layouts/footer.php'; ?>