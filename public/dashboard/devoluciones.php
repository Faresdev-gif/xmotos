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
            d.*,
            vd.cantidad AS cantidad_devuelta,
            vd.precio_unitario,
            p.marca,
            p.modelo,
            p.imagen,
            v.id AS venta_id,
            v.numero_factura,
            v.fecha_registro AS venta_fecha,
            u.nombre_completo AS cliente_nombre,
            u.email AS cliente_email
        FROM devoluciones d
        INNER JOIN venta_detalle vd ON d.venta_detalle_id = vd.id
        INNER JOIN ventas v ON vd.venta_id = v.id
        INNER JOIN productos p ON vd.producto_id = p.id
        INNER JOIN usuarios u ON v.usuario_id = u.id
        WHERE 1=1";
$params = [];

if ($buscar !== '') {
    $sql .= " AND (u.nombre_completo LIKE :buscar OR p.marca LIKE :buscar OR p.modelo LIKE :buscar OR d.motivo LIKE :buscar)";
    $params[':buscar'] = '%' . $buscar . '%';
}

if ($filtroEstado !== '' && in_array($filtroEstado, ['pendiente', 'aprobada', 'rechazada'], true)) {
    $sql .= " AND d.estado = :estado";
    $params[':estado'] = $filtroEstado;
}

$sql .= " ORDER BY d.fecha_registro DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$devoluciones = $stmt->fetchAll();

$pageTitle = 'Devoluciones';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h2 class="h4 mb-1">Gestión de devoluciones</h2>
    <p class="text-x-muted mb-0">Administra las solicitudes de devolución</p>
  </div>
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
    <div class="col-12 col-md-4">
      <label class="form-label">Estado</label>
      <select name="estado" class="form-select">
        <option value="">Todos</option>
        <option value="pendiente" <?= $filtroEstado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
        <option value="aprobada" <?= $filtroEstado === 'aprobada' ? 'selected' : '' ?>>Aprobada</option>
        <option value="rechazada" <?= $filtroEstado === 'rechazada' ? 'selected' : '' ?>>Rechazada</option>
      </select>
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label">Buscar</label>
      <input type="text" name="buscar" class="form-control"
             placeholder="Buscar por cliente, producto o motivo..."
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
    <h2><i class="bi bi-arrow-return-left me-2" style="color: var(--x-primary);"></i> Listado de devoluciones</h2>
    <span class="count"><?= count($devoluciones) ?> devoluciones</span>
  </div>

  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Factura venta</th>
          <th>Cliente</th>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Monto</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th style="text-align: right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($devoluciones)): ?>
          <tr>
            <td colspan="9" class="text-center py-5">
              <i class="bi bi-inbox fs-1 text-x-muted d-block mb-2"></i>
              <span class="text-x-muted">No hay devoluciones registradas.</span>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($devoluciones as $d): ?>
            <tr>
              <td>#<?= $d['id'] ?></td>
              <td><strong><?= e($d['numero_factura']) ?></strong></td>
              <td><?= e($d['cliente_nombre']) ?></td>
              <td>
                <div class="product-info">
                  <span class="brand"><?= e($d['marca']) ?></span>
                  <span class="model"><?= e($d['modelo']) ?></span>
                </div>
              </td>
              <td><?= (int)$d['cantidad_devuelta'] ?></td>
              <td><strong>$<?= number_format((float)$d['monto'], 0, ',', '.') ?></strong></td>
              <td>
                <?php
                  $class = 'category-badge';
                  if ($d['estado'] === 'aprobada') $class = 'status-badge activo';
                  elseif ($d['estado'] === 'rechazada') $class = 'status-badge inactivo';
                ?>
                <span class="<?= $class ?>"><?= ucfirst(e($d['estado'])) ?></span>
              </td>
              <td><?= date('d/m/Y', strtotime($d['fecha_registro'])) ?></td>
              <td>
                <div class="action-buttons">
                  <?php if ($d['estado'] === 'pendiente'): ?>
                    <a href="<?= base_url('dashboard/devolucion_guardar.php?accion=aprobar&id=' . $d['id']) ?>"
                       class="btn-action"
                       style="background: #10B981; color: #FFF; border-color: #10B981;"
                       title="Aprobar">
                      <i class="bi bi-check-lg"></i>
                    </a>
                    <a href="<?= base_url('dashboard/devolucion_guardar.php?accion=rechazar&id=' . $d['id']) ?>"
                       class="btn-action delete"
                       title="Rechazar"
                       onclick="return confirm('¿Rechazar esta devolución?')">
                      <i class="bi bi-x-lg"></i>
                    </a>
                  <?php else: ?>
                    <span class="text-x-muted small">
                      <?= $d['fecha_resolucion'] ? date('d/m/Y', strtotime($d['fecha_resolucion'])) : '—' ?>
                    </span>
                  <?php endif; ?>
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