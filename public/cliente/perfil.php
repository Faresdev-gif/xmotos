<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['cliente']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

$pageTitle = 'Mi perfil';
require_once __DIR__ . '/../../app/views/layouts/cliente_header.php';
?>

<h2 class="h4 mb-4">Mi perfil</h2>

<div class="row g-4">
  <div class="col-12 col-lg-8">
    <div class="x-card p-4">
      <h3 class="h5 mb-3">Datos personales</h3>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre completo</label>
          <input type="text" class="form-control" value="<?= e($usuario['nombre_completo']) ?>" readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Correo electrónico</label>
          <input type="email" class="form-control" value="<?= e($usuario['email']) ?>" readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Teléfono</label>
          <input type="tel" class="form-control" value="<?= e($usuario['telefono']) ?>" readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Rol</label>
          <input type="text" class="form-control" value="<?= e(ucfirst($usuario['rol'])) ?>" readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Verificado</label>
          <input type="text" class="form-control" value="<?= $usuario['verificado'] ? 'Sí' : 'No' ?>" readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Fecha de registro</label>
          <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?>" readonly>
        </div>
      </div>

      <hr class="my-4">

      <h3 class="h5 mb-3">Cambiar contraseña</h3>

      <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
          <i class="bi bi-check-circle me-2"></i> <?= e($_GET['msg']) ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle me-2"></i> <?= e($_GET['error']) ?>
        </div>
      <?php endif; ?>

      <form action="<?= base_url('cliente/perfil_guardar.php') ?>" method="POST" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Contraseña actual</label>
          <input type="password" name="password_actual" class="form-control" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Nueva contraseña</label>
          <input type="password" name="password_nueva" class="form-control" minlength="8" required>
        </div>

        <div class="col-12 d-grid d-sm-flex justify-content-sm-end">
          <button type="submit" class="btn btn-x-primary">
            <i class="bi bi-save me-2"></i> Cambiar contraseña
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/cliente_footer.php'; ?>