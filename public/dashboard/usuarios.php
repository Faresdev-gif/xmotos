<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$buscar = trim($_GET['buscar'] ?? '');
$filtroRol = $_GET['rol'] ?? '';
$filtroEstado = $_GET['estado'] ?? '';

$sql = "SELECT * FROM usuarios WHERE 1=1";
$params = [];

if ($buscar !== '') {
    $sql .= " AND (nombre_completo LIKE :buscar OR email LIKE :buscar OR telefono LIKE :buscar)";
    $params[':buscar'] = '%' . $buscar . '%';
}

if ($filtroRol !== '' && in_array($filtroRol, ['cliente', 'vendedor', 'administrador'], true)) {
    $sql .= " AND rol = :rol";
    $params[':rol'] = $filtroRol;
}

if ($filtroEstado !== '' && in_array($filtroEstado, ['activo', 'inactivo'], true)) {
    $sql .= " AND estado = :estado";
    $params[':estado'] = $filtroEstado;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

$pageTitle = 'Usuarios';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h2 class="h4 mb-1">Gestión de usuarios</h2>
    <p class="text-x-muted mb-0">Administra los usuarios registrados en XMOTOS</p>
  </div>
  <a href="<?= base_url('dashboard/usuario_form.php') ?>" class="btn btn-x-primary">
    <i class="bi bi-person-plus me-2"></i> Nuevo usuario
  </a>
</div>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i>
    <span><?= e($_GET['msg']) ?></span>
  </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span><?= e($_GET['error']) ?></span>
  </div>
<?php endif; ?>

<!-- FILTROS -->
<div class="x-card p-3 mb-4">
  <form method="GET" class="row g-3 align-items-end">
    <div class="col-12 col-md-3">
      <label class="form-label">Tipo de usuario</label>
      <select name="rol" class="form-select">
        <option value="">Todos</option>
        <option value="cliente" <?= $filtroRol === 'cliente' ? 'selected' : '' ?>>Cliente</option>
        <option value="vendedor" <?= $filtroRol === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
        <option value="administrador" <?= $filtroRol === 'administrador' ? 'selected' : '' ?>>Administrador</option>
      </select>
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Estado</label>
      <select name="estado" class="form-select">
        <option value="">Todos</option>
        <option value="activo" <?= $filtroEstado === 'activo' ? 'selected' : '' ?>>Activo</option>
        <option value="inactivo" <?= $filtroEstado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
      </select>
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label">Buscar</label>
      <input type="text" name="buscar" class="form-control" 
             placeholder="Buscar por nombre, email o teléfono..." 
             value="<?= e($buscar) ?>">
    </div>
    <div class="col-12 col-md-2">
      <button type="submit" class="btn btn-x-primary w-100">
        <i class="bi bi-search me-1"></i> Filtrar
      </button>
    </div>
  </form>
</div>

<!-- TABLA -->
<div class="dashboard-table">
  <div class="table-header">
    <h2><i class="bi bi-people me-2" style="color: var(--x-primary);"></i> Listado de usuarios</h2>
    <span class="count"><?= count($usuarios) ?> usuarios</span>
  </div>

  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Teléfono</th>
          <th>Rol</th>
          <th>Verificado</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th style="text-align: right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($usuarios)): ?>
          <tr>
            <td colspan="9" class="text-center py-5">
              <i class="bi bi-inbox fs-1 text-x-muted d-block mb-2"></i>
              <span class="text-x-muted">No se encontraron usuarios.</span>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td>#<?= $u['id'] ?></td>
              <td><?= e($u['nombre_completo']) ?></td>
              <td><?= e($u['email']) ?></td>
              <td><?= e($u['telefono']) ?></td>
              <td>
                <span class="category-badge"><?= e(strtoupper($u['rol'])) ?></span>
              </td>
              <td>
                <?php if ($u['verificado']): ?>
                  <span class="status-badge activo"><i class="bi bi-check-circle-fill"></i> Sí</span>
                <?php else: ?>
                  <span class="status-badge inactivo"><i class="bi bi-x-circle-fill"></i> No</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($u['estado'] === 'activo'): ?>
                  <span class="status-badge activo">Activo</span>
                <?php else: ?>
                  <span class="status-badge inactivo">Inactivo</span>
                <?php endif; ?>
              </td>
              <td><?= date('d/m/Y', strtotime($u['fecha_registro'])) ?></td>
              <td>
                <div class="action-buttons">
                  <!-- Editar -->
                  <a href="<?= base_url('dashboard/usuario_form.php?id=' . $u['id']) ?>" 
                     class="btn-action edit" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>

                  <!-- Verificar / Desverificar -->
                  <a href="<?= base_url('dashboard/usuario_guardar.php?accion=toggle&id=' . $u['id']) ?>" 
                     class="btn-action toggle" 
                     title="<?= $u['verificado'] ? 'Marcar como NO verificado' : 'Marcar como verificado' ?>"
                     onclick="return confirm('¿Cambiar el estado de verificación?')">
                    <i class="bi bi-<?= $u['verificado'] ? 'person-x' : 'person-check' ?>"></i>
                  </a>

                  <!-- Inactivar / Activar (antes era basura) -->
                  <a href="<?= base_url('dashboard/usuario_guardar.php?accion=toggle_estado&id=' . $u['id']) ?>" 
                     class="btn-action <?= $u['estado'] === 'activo' ? 'delete' : 'edit' ?>" 
                     title="<?= $u['estado'] === 'activo' ? 'Inactivar usuario' : 'Activar usuario' ?>"
                     onclick="return confirm('¿<?= $u['estado'] === 'activo' ? 'Inactivar' : 'Activar' ?> este usuario?')">
                    <i class="bi bi-<?= $u['estado'] === 'activo' ? 'toggle-on' : 'toggle-off' ?>"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>