<?php
$pageTitle    = 'Paridad';
$pageSubtitle = 'Comparación detallada maestro vs réplica';
$currentPage  = 'paridad';

require_once __DIR__ . '/../includes/header.php';

$paridad     = obtenerParidad();
$totalTablas = count($paridad);
$tablasOk    = count(array_filter($paridad, fn($p) => $p['ok']));
$todasOk     = $tablasOk === $totalTablas;
?>

<!-- ============================================================
     RESUMEN
     ============================================================ -->
<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Total tablas</h6>
        <h3><?= $totalTablas ?></h3>
        <span class="stat-badge">Replicadas</span>
      </div>
      <div class="stat-icon-circle">
        <i class="bi bi-diagram-3"></i>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Tablas sincronizadas</h6>
        <h3><?= $tablasOk ?> / <?= $totalTablas ?></h3>
        <span class="stat-badge <?= $todasOk ? '' : 'danger' ?>">
          <?= $todasOk ? 'Todo OK' : 'Divergencias detectadas' ?>
        </span>
      </div>
      <div class="stat-icon-circle">
        <i class="bi bi-check-circle"></i>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Estado general</h6>
        <h3>
          <?php if ($todasOk): ?>
            <span class="paridad-ok"><i class="bi bi-check-circle-fill"></i> OK</span>
          <?php else: ?>
            <span class="paridad-err"><i class="bi bi-exclamation-triangle-fill"></i> Alerta</span>
          <?php endif; ?>
        </h3>
        <span class="stat-badge">Última verificación: ahora</span>
      </div>
      <div class="stat-icon-circle">
        <i class="bi bi-arrow-left-right"></i>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     TABLA DE PARIDAD DETALLADA
     ============================================================ -->
<div class="dashboard-table">
  <div class="table-header">
    <h2><i class="bi bi-arrow-left-right me-2"></i>Comparación detallada</h2>
    <span class="count">
      <?php if ($todasOk): ?>
        <span class="paridad-ok"><i class="bi bi-check-circle-fill"></i> Todo sincronizado</span>
      <?php else: ?>
        <span class="paridad-err"><i class="bi bi-exclamation-triangle-fill"></i> Divergencia detectada</span>
      <?php endif; ?>
    </span>
  </div>

  <table>
    <thead>
      <tr>
        <th>Tabla</th>
        <th class="text-end">Maestro (xmotos)</th>
        <th class="text-end">Réplica (xmotos_replica)</th>
        <th class="text-end">Diferencia</th>
        <th class="text-center">Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($paridad as $fila): ?>
        <?php $dif = $fila['maestro'] - $fila['replica']; ?>
        <tr>
          <td><strong><?= e($fila['tabla']) ?></strong></td>
          <td class="text-end"><?= number_format($fila['maestro']) ?></td>
          <td class="text-end"><?= number_format($fila['replica']) ?></td>
          <td class="text-end">
            <?php if ($dif === 0): ?>
              <span class="text-x-muted">—</span>
            <?php elseif ($dif > 0): ?>
              <span class="paridad-err">+<?= abs($dif) ?></span>
            <?php else: ?>
              <span class="paridad-err"><?= $dif ?></span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?php if ($fila['ok']): ?>
              <span class="paridad-ok"><i class="bi bi-check-circle-fill"></i> OK</span>
            <?php else: ?>
              <span class="paridad-err"><i class="bi bi-exclamation-triangle-fill"></i> Alerta</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>