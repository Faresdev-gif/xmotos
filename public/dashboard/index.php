<?php
/**
 * XMOTOS - Dashboard principal (Admin/Vendedor)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';

requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';

$pdo = Database::getConnection();

// Estadísticas
$totalProductos   = (int)$pdo->query("SELECT COUNT(*) FROM productos WHERE estado = 'activo'")->fetchColumn();
$totalUsuarios    = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalSolicitudes = (int)$pdo->query("SELECT COUNT(*) FROM solicitudes")->fetchColumn();
$solicitudesNuevas = (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'nuevo'")->fetchColumn();
$totalVentas      = (int)$pdo->query("SELECT COUNT(*) FROM ventas")->fetchColumn();
$montoVentas      = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM ventas WHERE estado = 'pagada'")->fetchColumn();

// Últimas solicitudes
$ultimasSolicitudes = $pdo->query("
    SELECT s.*, u.nombre_completo 
    FROM solicitudes s
    INNER JOIN usuarios u ON s.usuario_id = u.id
    ORDER BY s.fecha_registro DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<!-- ESTADÍSTICAS -->
<div class="row g-4 mb-4">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Productos activos</h6>
        <h3><?= $totalProductos ?></h3>
        <span class="stat-badge">Activos</span>
      </div>
      <div class="stat-icon-circle">
        <i class="bi bi-box-seam"></i>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Usuarios registrados</h6>
        <h3><?= $totalUsuarios ?></h3>
        <span class="stat-badge">Total</span>
      </div>
      <div class="stat-icon-circle">
        <i class="bi bi-people"></i>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Solicitudes totales</h6>
        <h3><?= $totalSolicitudes ?></h3>
        <span class="stat-badge"><?= $solicitudesNuevas ?> nuevas</span>
      </div>
      <div class="stat-icon-circle">
        <i class="bi bi-file-earmark-text"></i>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Ventas registradas</h6>
        <h3><?= $totalVentas ?></h3>
        <span class="stat-badge">Total</span>
      </div>
      <div class="stat-icon-circle">
        <i class="bi bi-cart-check"></i>
      </div>
    </div>
  </div>
</div>

<!-- SOLICITUDES NUEVAS -->
<div class="row g-4 mb-4">
  <div class="col-12">
    <div class="x-card p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">
          <i class="bi bi-bell text-x-primary me-2"></i>
          Solicitudes nuevas
        </h2>
        <a href="<?= base_url('dashboard/solicitudes.php') ?>" class="btn btn-x-outline btn-sm">
          Ver todas
        </a>
      </div>
      <p class="text-x-muted">
        Tienes <strong class="text-x-primary"><?= $solicitudesNuevas ?></strong> solicitudes pendientes por revisar.
      </p>
    </div>
  </div>
</div>

<!-- ÚLTIMAS SOLICITUDES -->
<div class="row g-4">
  <div class="col-12">
    <div class="dashboard-table">
      <div class="p-3 border-bottom" style="border-color: var(--x-border) !important;">
        <h2 class="h6 mb-0">Últimas 5 solicitudes</h2>
      </div>
      <div class="table-responsive">
        <table class="table table-dark mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Tipo</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($ultimasSolicitudes)): ?>
              <tr>
                <td colspan="6" class="text-center text-x-muted py-4">
                  No hay solicitudes registradas.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($ultimasSolicitudes as $sol): ?>
                <tr>
                  <td>#<?= $sol['id'] ?></td>
                  <td><?= e($sol['nombre_completo']) ?></td>
                  <td><?= ucfirst(e($sol['tipo_solicitud'])) ?></td>
                  <td>
                    <span class="x-badge"><?= e($sol['estado']) ?></span>
                  </td>
                  <td><?= date('d/m/Y H:i', strtotime($sol['fecha_registro'])) ?></td>
                  <td>
                    <a href="<?= base_url('dashboard/ver_solicitud.php?id=' . $sol['id']) ?>" 
                       class="btn btn-sm btn-x-outline">
                      <i class="bi bi-eye"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>