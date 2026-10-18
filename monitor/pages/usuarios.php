<?php
$pageTitle    = 'Usuarios del monitor';
$pageSubtitle = 'Gestión de accesos al panel';
$currentPage  = 'usuarios';

require_once __DIR__ . '/../includes/header.php';

// Solo administradores pueden acceder
if (($_SESSION['monitor_rol'] ?? '') !== 'admin') {
    echo '<div class="dashboard-table p-5 text-center">';
    echo '<i class="bi bi-shield-lock fs-1 text-danger d-block mb-3"></i>';
    echo '<h3>Acceso denegado</h3>';
    echo '<p class="text-x-muted">Solo los administradores del monitor pueden gestionar usuarios.</p>';
    echo '</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Mensajes flash
$mensaje = $_SESSION['flash_ok'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'crear') {
            $nombre   = trim($_POST['nombre'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rol      = $_POST['rol'] ?? 'observador';

            if ($nombre === '' || $email === '' || strlen($password) < 6) {
                throw new Exception('Todos los campos son obligatorios. La contraseña debe tener al menos 6 caracteres.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El email no es válido.');
            }

            crearUsuarioMonitor($nombre, $email, $password, $rol);
            $_SESSION['flash_ok'] = 'Usuario creado correctamente.';
        } elseif ($accion === 'actualizar') {
            $id     = (int)($_POST['id'] ?? 0);
            $rol    = $_POST['rol'] ?? 'observador';
            $estado = $_POST['estado'] ?? 'activo';

            if ($id === (int)$_SESSION['monitor_usuario_id']) {
                throw new Exception('No puedes modificar tu propio usuario desde aquí.');
            }

            actualizarUsuarioMonitor($id, $rol, $estado);
            $_SESSION['flash_ok'] = 'Usuario actualizado.';
        } elseif ($accion === 'desbloquear') {
            $id = (int)($_POST['id'] ?? 0);
            desbloquearUsuarioMonitor($id);
            $_SESSION['flash_ok'] = 'Usuario desbloqueado.';
        }

        header('Location: ' . MONITOR_URL . '?route=usuarios');
        exit;

    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        header('Location: ' . MONITOR_URL . '?route=usuarios');
        exit;
    }
}

$usuarios = listarUsuariosMonitor();
?>

<?php if ($mensaje): ?>
  <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?= e($mensaje) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<!-- ============================================================
     FORMULARIO DE CREAR
     ============================================================ -->
<div class="dashboard-table mb-4">
  <div class="table-header">
    <h2><i class="bi bi-person-plus me-2"></i>Nuevo usuario del monitor</h2>
  </div>
  <form method="POST" class="p-3">
    <input type="hidden" name="accion" value="crear">

    <div class="row g-3">
      <div class="col-12 col-md-3">
        <label class="form-label small text-x-muted">Nombre completo</label>
        <input type="text" name="nombre" class="form-control" required maxlength="120">
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label small text-x-muted">Correo</label>
        <input type="email" name="email" class="form-control" required maxlength="120">
      </div>
      <div class="col-12 col-md-2">
        <label class="form-label small text-x-muted">Contraseña</label>
        <input type="password" name="password" class="form-control" required minlength="6">
      </div>
      <div class="col-12 col-md-2">
        <label class="form-label small text-x-muted">Rol</label>
        <select name="rol" class="form-select">
          <option value="observador">Observador</option>
          <option value="admin">Administrador</option>
        </select>
      </div>
      <div class="col-12 col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-x-primary w-100">
          <i class="bi bi-plus-circle me-1"></i> Crear
        </button>
      </div>
    </div>
  </form>
</div>

<!-- ============================================================
     LISTA
     ============================================================ -->
<div class="dashboard-table">
  <div class="table-header">
    <h2><i class="bi bi-people me-2"></i>Usuarios existentes</h2>
    <span class="count"><strong><?= count($usuarios) ?></strong> usuario(s)</span>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Rol</th>
        <th>Estado</th>
        <th>Último login</th>
        <th class="text-end">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($usuarios as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><strong><?= e($u['nombre_completo']) ?></strong></td>
          <td class="small text-x-muted"><?= e($u['email']) ?></td>
          <td>
            <form method="POST" class="d-inline">
              <input type="hidden" name="accion" value="actualizar">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <select name="rol" class="form-select form-select-sm d-inline-block"
                      style="width:auto;" onchange="this.form.submit()"
                      <?= (int)$u['id'] === (int)$_SESSION['monitor_usuario_id'] ? 'disabled' : '' ?>>
                <option value="observador" <?= $u['rol']==='observador' ? 'selected' : '' ?>>Observador</option>
                <option value="admin"      <?= $u['rol']==='admin'      ? 'selected' : '' ?>>Administrador</option>
              </select>
              <input type="hidden" name="estado" value="<?= e($u['estado']) ?>">
            </form>
          </td>
          <td>
            <span class="badge <?= $u['estado']==='activo' ? 'badge-aprobado' : 'badge-cancelado' ?>">
              <?= e($u['estado']) ?>
            </span>
          </td>
          <td class="small text-x-muted">
            <?= $u['ultimo_login'] ? formatearFecha($u['ultimo_login']) : 'Nunca' ?>
          </td>
          <td class="text-end">
            <?php if ($u['bloqueado_hasta'] && strtotime($u['bloqueado_hasta']) > time()): ?>
              <form method="POST" class="d-inline">
                <input type="hidden" name="accion" value="desbloquear">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="btn btn-x-outline btn-sm" title="Desbloquear">
                  <i class="bi bi-unlock"></i>
                </button>
              </form>
            <?php endif; ?>
            <form method="POST" class="d-inline">
              <input type="hidden" name="accion" value="actualizar">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <input type="hidden" name="rol" value="<?= e($u['rol']) ?>">
              <input type="hidden" name="estado" value="<?= $u['estado']==='activo' ? 'inactivo' : 'activo' ?>">
              <button type="submit" class="btn btn-x-outline btn-sm"
                      title="<?= $u['estado']==='activo' ? 'Desactivar' : 'Activar' ?>"
                      <?= (int)$u['id'] === (int)$_SESSION['monitor_usuario_id'] ? 'disabled' : '' ?>>
                <i class="bi bi-<?= $u['estado']==='activo' ? 'pause' : 'play' ?>"></i>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>