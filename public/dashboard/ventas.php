<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$buscar = trim($_GET['buscar'] ?? '');
$filtroEstado = $_GET['estado'] ?? '';

$sql = "SELECT 
            v.*,
            u.nombre_completo AS cliente_nombre,
            u.email AS cliente_email
        FROM ventas v
        INNER JOIN usuarios u ON v.usuario_id = u.id
        WHERE 1=1";
$params = [];

if ($buscar !== '') {
    $sql .= " AND (v.numero_factura LIKE :buscar OR u.nombre_completo LIKE :buscar)";
    $params[':buscar'] = '%' . $buscar . '%';
}

if ($filtroEstado !== '' && in_array($filtroEstado, ['pendiente', 'pagada', 'cancelada'], true)) {
    $sql .= " AND v.estado = :estado";
    $params[':estado'] = $filtroEstado;
}

$sql .= " ORDER BY v.fecha_registro DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll();

$pageTitle = 'Ventas';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h2 class="h4 mb-1">Gestión de ventas</h2>
    <p class="text-x-muted mb-0">Administra las ventas realizadas</p>
  </div>
</div>

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
             placeholder="Buscar por factura o cliente..." 
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
    <h2><i class="bi bi-cart-check me-2" style="color: var(--x-primary);"></i> Listado de ventas</h2>
    <span class="count"><?= count($ventas) ?> ventas</span>
  </div>

  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th># Factura</th>
          <th>Cliente</th>
          <th>Subtotal</th>
          <th>IVA</th>
          <th>Total</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th style="text-align: right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($ventas)): ?>
          <tr>
            <td colspan="8" class="text-center py-5">
              <i class="bi bi-inbox fs-1 text-x-muted d-block mb-2"></i>
              <span class="text-x-muted">No hay ventas registradas.</span>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($ventas as $v): ?>
            <tr>
              <td><strong><?= e($v['numero_factura']) ?></strong></td>
              <td><?= e($v['cliente_nombre']) ?></td>
              <td>$<?= number_format((float)$v['subtotal'], 0, ',', '.') ?></td>
              <td>$<?= number_format((float)$v['iva'], 0, ',', '.') ?></td>
              <td><strong style="color: var(--x-primary-dark);">$<?= number_format((float)$v['total'], 0, ',', '.') ?></strong></td>
              <td>
                <span class="status-badge <?= $v['estado'] === 'pagada' ? 'activo' : 'inactivo' ?>">
                  <?= ucfirst(e($v['estado'])) ?>
                </span>
              </td>
              <td><?= date('d/m/Y H:i', strtotime($v['fecha_registro'])) ?></td>
              <td>
                <div class="action-buttons">
                  <a href="<?= base_url('dashboard/ver_venta.php?id=' . $v['id']) ?>" 
                     class="btn-action edit" title="Ver detalle">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="<?= base_url('dashboard/ver_venta.php?id=' . $v['id']) ?>" 
                     class="btn-action toggle" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="<?= base_url('dashboard/venta_cancelar.php?id=' . $v['id']) ?>" 
                     class="btn-action delete" 
                     title="Cancelar"
                     onclick="return confirm('¿Cancelar esta venta? El stock será revertido.')">
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