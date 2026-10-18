<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

// Filtros
$buscar = trim($_GET['buscar'] ?? '');
$filtroEstado = $_GET['estado'] ?? '';

$sql = "SELECT 
            c.*,
            p.nombre AS proveedor_nombre,
            p.nit AS proveedor_nit,
            u.nombre_completo AS usuario_registro
        FROM compras c
        INNER JOIN proveedores p ON c.proveedor_id = p.id
        INNER JOIN usuarios u ON c.usuario_id = u.id
        WHERE 1=1";
$params = [];

if ($buscar !== '') {
    $sql .= " AND (c.numero_factura LIKE :buscar OR p.nombre LIKE :buscar OR p.nit LIKE :buscar)";
    $params[':buscar'] = '%' . $buscar . '%';
}

if ($filtroEstado !== '' && in_array($filtroEstado, ['pendiente', 'pagada', 'cancelada'], true)) {
    $sql .= " AND c.estado = :estado";
    $params[':estado'] = $filtroEstado;
}

$sql .= " ORDER BY c.fecha_registro DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$compras = $stmt->fetchAll();

$pageTitle = 'Compras';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h2 class="h4 mb-1">Gestión de compras</h2>
    <p class="text-x-muted mb-0">Administra las compras a proveedores</p>
  </div>
  <a href="<?= base_url('dashboard/compra_form.php') ?>" class="btn btn-x-primary">
    <i class="bi bi-plus-circle me-2"></i> Nueva compra
  </a>
</div>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i>
    <span><?= e($_GET['msg']) ?></span>
  </div>
<?php endif; ?>

<!-- FILTROS -->
<div class="x-card p-3 mb-4">
  <form method="GET" class="row g-3 align-items-end">
    <div class="col-12 col-md-4">
      <label class="form-label">Estado</label>
      <select name="estado" class="form-select">
        <option value="">Todos</option>
        <option value="pendiente" <?= $filtroEstado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
        <option value="pagada" <?= $filtroEstado === 'pagada' ? 'selected' : '' ?>>Pagada</option>
        <option value="cancelada" <?= $filtroEstado === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
      </select>
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label">Buscar</label>
      <input type="text" name="buscar" class="form-control" 
             placeholder="Buscar por factura, proveedor o NIT..." 
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
    <h2><i class="bi bi-bag-plus me-2" style="color: var(--x-primary);"></i> Listado de compras</h2>
    <span class="count"><?= count($compras) ?> compras</span>
  </div>

  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th># Factura</th>
          <th>Proveedor</th>
          <th>NIT</th>
          <th>Subtotal</th>
          <th>IVA</th>
          <th>Total</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th style="text-align: right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($compras)): ?>
          <tr>
            <td colspan="9" class="text-center py-5">
              <i class="bi bi-inbox fs-1 text-x-muted d-block mb-2"></i>
              <span class="text-x-muted">No hay compras registradas.</span>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($compras as $c): ?>
            <tr>
              <td><strong><?= e($c['numero_factura']) ?></strong></td>
              <td><?= e($c['proveedor_nombre']) ?></td>
              <td><?= e($c['proveedor_nit']) ?></td>
              <td>$<?= number_format((float)$c['subtotal'], 0, ',', '.') ?></td>
              <td>$<?= number_format((float)$c['iva'], 0, ',', '.') ?></td>
              <td><strong style="color: var(--x-primary-dark);">$<?= number_format((float)$c['total'], 0, ',', '.') ?></strong></td>
              <td>
                <span class="status-badge <?= $c['estado'] === 'pagada' ? 'activo' : ($c['estado'] === 'cancelada' ? 'inactivo' : '') ?>">
                  <?= ucfirst(e($c['estado'])) ?>
                </span>
              </td>
              <td><?= date('d/m/Y', strtotime($c['fecha_registro'])) ?></td>
              <td>
                <div class="action-buttons">
                  <a href="<?= base_url('dashboard/ver_compra.php?id=' . $c['id']) ?>" 
                     class="btn-action edit" title="Ver detalle">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="<?= base_url('dashboard/compra_form.php?id=' . $c['id']) ?>" 
                     class="btn-action toggle" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="<?= base_url('dashboard/compra_guardar.php?accion=cancelar&id=' . $c['id']) ?>" 
                     class="btn-action delete" 
                     title="Cancelar"
                     onclick="return confirm('¿Cancelar esta compra? El stock será revertido.')">
                    <i class="bi bi-x-lg"></i>
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