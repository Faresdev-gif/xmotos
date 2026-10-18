<?php
/**
 * XMOTOS - Registro de usuarios
 *
 * Este archivo:
 * 1. Si recibe POST, procesa el registro con AuthController.
 * 2. Si no, muestra el formulario de registro.
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/config/helpers.php';

// =========================================================
// SI ES POST → PROCESAR REGISTRO
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    (new AuthController())->registrar();
    exit;
}

// =========================================================
// SI NO ES POST → MOSTRAR FORMULARIO
// =========================================================
$pageTitle = 'Crear Cuenta';
require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<!-- HERO DEL REGISTRO -->
<header class="hero-compact">
  <div class="container text-center">
    <span class="x-badge mb-2">Únete a XMOTOS</span>
    <h1 class="mb-2">Crea tu cuenta</h1>
    <p class="mb-0">Regístrate para cotizar, financiar y hacer seguimiento a tus solicitudes.</p>
  </div>
</header>



<!-- FORMULARIO DE REGISTRO (vista reutilizable) -->
<?php require __DIR__ . '/../app/views/auth/registro.php'; ?>

<?php require_once __DIR__ . '/../app/views/layouts/footer.php'; ?>