<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$usuario = [
    'id' => 0,
    'nombre_completo' => '',
    'email' => '',
    'telefono' => '',
    'rol' => 'cliente',
    'verificado' => 0,
    'estado' => 'activo',
];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch();
    if ($data) {
        $usuario = $data;
    } else {
        header('Location: ' . base_url('dashboard/usuarios.php?error=Usuario no encontrado'));
        exit;
    }
}

$pageTitle = $id > 0 ? 'Editar usuario' : 'Nuevo usuario';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="mb-4">
  <a href="<?= base_url('dashboard/usuarios.php') ?>" class="text-x-muted">
    <i class="bi bi-arrow-left me-2"></i> Volver al listado
  </a>
</div>

<div class="x-card p-4">
  <h2 class="h5 mb-4">
    <?= $id > 0 ? 'Editar usuario #' . $id : 'Nuevo usuario' ?>
  </h2>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger mb-4">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <?= e($_GET['error']) ?>
    </div>
  <?php endif; ?>

  <form action="<?= base_url('dashboard/usuario_guardar_form.php') ?>" method="POST">
    <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">

    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Nombre completo *</label>
        <input type="text" name="nombre_completo" class="form-control" 
               value="<?= e($usuario['nombre_completo']) ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Correo electrónico *</label>
        <input type="email" name="email" class="form-control" 
               value="<?= e($usuario['email']) ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Teléfono *</label>
        <input type="tel" name="telefono" class="form-control" 
               value="<?= e($usuario['telefono']) ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Rol *</label>
        <select name="rol" class="form-select" required>
          <option value="cliente" <?= $usuario['rol'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
          <option value="vendedor" <?= $usuario['rol'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
          <option value="administrador" <?= $usuario['rol'] === 'administrador' ? 'selected' : '' ?>>Administrador</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <option value="activo" <?= $usuario['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
          <option value="inactivo" <?= $usuario['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>

      <?php if ($id === 0): ?>
        <div class="col-md-6">
          <label class="form-label">Contraseña *</label>
          <input type="password" name="password" class="form-control" minlength="8" required>
          <div class="form-text text-x-muted">Mínimo 8 caracteres</div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Confirmar contraseña *</label>
          <input type="password" name="password_confirm" class="form-control" minlength="8" required>
        </div>
      <?php endif; ?>

      <div class="col-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="verificado" name="verificado" 
                 <?= $usuario['verificado'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="verificado">
            Usuario verificado (puede iniciar sesión)
          </label>
        </div>
      </div>

      <div class="col-12 d-flex gap-2 justify-content-end mt-3">
        <a href="<?= base_url('dashboard/usuarios.php') ?>" class="btn btn-x-outline">Cancelar</a>
        <button type="submit" class="btn btn-x-primary">
          <i class="bi bi-check-circle me-2"></i> Guardar
        </button>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>