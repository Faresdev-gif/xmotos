<?php
$pageTitle    = 'Dashboard';
$pageSubtitle = 'Estado general del sistema';
$currentPage  = 'dashboard';

require_once __DIR__ . '/../includes/header.php';

// ============================================================
// Obtener datos
// ============================================================
$paridad         = obtenerParidad();
$operaciones     = obtenerUltimasOperaciones(20);
$metricas        = obtenerMetricas();
$opsUltimas24h   = obtenerOperacionesUltimas24h();
$opsPorHora      = obtenerOperacionesPorHora();

// Totales de paridad (solo para el KPI card)
$totalTablas     = count($paridad);
$tablasOk        = count(array_filter($paridad, fn($p) => $p['ok']));
$todasOk         = $tablasOk === $totalTablas;
?>

<!-- ============================================================
     KPI CARDS
     ============================================================ -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <a href="<?= MONITOR_URL ?>?route=paridad" class="text-decoration-none">
      <div class="stat-card">
        <div class="stat-content">
          <h6>Tablas replicadas</h6>
          <h3><?= $totalTablas ?></h3>
          <span class="stat-badge <?= $todasOk ? '' : 'danger' ?>">
            <?= $todasOk ? 'Sincronizadas' : "$tablasOk / $totalTablas OK" ?>
          </span>
        </div>
        <div class="stat-icon-circle">
          <i class="bi bi-diagram-3"></i>
        </div>
      </div>
    </a>
  </div>

  <div class="col-6 col-lg-3">
    <a href="<?= MONITOR_URL ?>?route=auditoria" class="text-decoration-none">
      <div class="stat-card">
        <div class="stat-content">
          <h6>Total operaciones</h6>
          <h3><?= number_format($metricas['total']) ?></h3>
          <span class="stat-badge">Registradas</span>
        </div>
        <div class="stat-icon-circle">
          <i class="bi bi-journal-text"></i>
        </div>
      </div>
    </a>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Últimas 24 horas</h6>
        <h3><?= number_format($opsUltimas24h) ?></h3>
        <span class="stat-badge">Actividad reciente</span>
      </div>
      <div class="stat-icon-circle">
        <i class="bi bi-clock-history"></i>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Distribución</h6>
        <h3><?= $metricas['insert'] ?> / <?= $metricas['update'] ?> / <?= $metricas['delete'] ?></h3>
        <span class="stat-badge">I / U / D</span>
      </div>
      <div class="stat-icon-circle">
        <i class="bi bi-bar-chart"></i>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     GRÁFICO DE OPERACIONES POR HORA
     ============================================================ -->
<div class="dashboard-table mb-4">
  <div class="table-header">
    <h2><i class="bi bi-bar-chart-line me-2"></i>Operaciones últimas 24 horas</h2>
    <span class="count">Por hora</span>
  </div>
  <div style="padding: 1.5rem;">
    <canvas id="graficoOperaciones"
            style="max-height: 300px;"
            data-ops='<?= json_encode(array_values($opsPorHora)) ?>'></canvas>
  </div>
</div>

<!-- ============================================================
     ÚLTIMAS OPERACIONES (resumen)
     ============================================================ -->
<div class="dashboard-table">
  <div class="table-header">
    <h2><i class="bi bi-clock-history me-2"></i>Últimas operaciones replicadas</h2>
    <a href="<?= MONITOR_URL ?>?route=auditoria" class="btn btn-x-outline btn-sm">
      Ver todas <i class="bi bi-arrow-right ms-1"></i>
    </a>
  </div>

  <?php if (empty($operaciones)): ?>
    <div class="p-4 text-center text-x-muted">
      <i class="bi bi-inbox fs-1 d-block mb-2"></i>
      No hay operaciones registradas todavía.
    </div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Tabla</th>
          <th>Operación</th>
          <th>Registro</th>
          <th>Datos</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($operaciones, 0, 10) as $op): ?>
          <tr>
            <td><?= (int)$op['id'] ?></td>
            <td><?= e($op['tabla_origen']) ?></td>
            <td>
              <?php
                $badgeClass = match($op['operacion']) {
                    'INSERT' => 'badge-insert',
                    'UPDATE' => 'badge-update',
                    'DELETE' => 'badge-delete',
                    default  => 'badge-seed',
                };
              ?>
              <span class="<?= $badgeClass ?>"><?= e($op['operacion']) ?></span>
            </td>
            <td>#<?= (int)$op['registro_id'] ?></td>
            <td>
              <code class="small text-truncate d-inline-block" style="max-width: 300px;"
                    title="<?= e($op['datos']) ?>">
                <?= e($op['datos']) ?>
              </code>
            </td>
            <td class="small text-x-muted"><?= formatearFecha($op['fecha_replicacion']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- ============================================================
     AUTO-REFRESH
     ============================================================ -->
<div class="text-end mt-3">
  <small class="text-x-muted">
    <i class="bi bi-arrow-clockwise"></i>
    Actualización automática: <strong id="countdown">10</strong>s
    <button type="button" class="btn btn-x-outline btn-sm ms-2" onclick="refrescarAhora()">
      Refrescar ahora
    </button>
  </small>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>