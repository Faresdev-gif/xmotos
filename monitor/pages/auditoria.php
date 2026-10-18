<?php
$pageTitle    = 'Auditoría';
$pageSubtitle = 'Historial de operaciones replicadas';
$currentPage  = 'auditoria';

require_once __DIR__ . '/../includes/header.php';

// ============================================================
// Leer filtros desde la URL
// ============================================================
$filtros = [
    'tabla'     => trim($_GET['tabla']     ?? ''),
    'operacion' => trim($_GET['operacion'] ?? ''),
    'desde'     => trim($_GET['desde']     ?? ''),
    'hasta'     => trim($_GET['hasta']     ?? ''),
    'buscar'    => trim($_GET['buscar']    ?? ''),
];

$pagina = max(1, (int)($_GET['pagina'] ?? 1));

// ============================================================
// Consultar datos
// ============================================================
$resultado    = buscarOperaciones($filtros, $pagina, 25);
$operaciones  = $resultado['datos'];
$total        = $resultado['total'];
$paginas      = $resultado['paginas'];
$tablasLog    = obtenerTablasDelLog();

// Helper para construir URL preservando filtros
function urlConFiltros(array $cambios = []): string
{
    $base = $_GET;
    foreach ($cambios as $k => $v) {
        if ($v === '' || $v === null) {
            unset($base[$k]);
        } else {
            $base[$k] = $v;
        }
    }
    return MONITOR_URL . '?' . http_build_query($base);
}
?>

<!-- ============================================================
     FILTROS
     ============================================================ -->
<div class="dashboard-table mb-4">
  <div class="table-header">
    <h2><i class="bi bi-funnel me-2"></i>Filtros</h2>
    <span class="count">
      <strong><?= number_format($total) ?></strong> resultado(s)
    </span>
  </div>

  <form method="GET" action="<?= MONITOR_URL ?>" class="p-3">
    <input type="hidden" name="route" value="auditoria">

    <div class="row g-3">
      <div class="col-12 col-md-3">
        <label class="form-label small text-x-muted">Tabla</label>
        <select name="tabla" class="form-select">
          <option value="">— Todas —</option>
          <?php foreach ($tablasLog as $t): ?>
            <option value="<?= e($t) ?>" <?= $filtros['tabla'] === $t ? 'selected' : '' ?>>
              <?= e($t) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-2">
        <label class="form-label small text-x-muted">Operación</label>
        <select name="operacion" class="form-select">
          <option value="">— Todas —</option>
          <?php foreach (['INSERT','UPDATE','DELETE','SEED'] as $op): ?>
            <option value="<?= $op ?>" <?= $filtros['operacion'] === $op ? 'selected' : '' ?>>
              <?= $op ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-2">
        <label class="form-label small text-x-muted">Desde</label>
        <input type="date" name="desde" class="form-control" value="<?= e($filtros['desde']) ?>">
      </div>

      <div class="col-12 col-md-2">
        <label class="form-label small text-x-muted">Hasta</label>
        <input type="date" name="hasta" class="form-control" value="<?= e($filtros['hasta']) ?>">
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label small text-x-muted">Buscar en datos</label>
        <input type="text" name="buscar" class="form-control"
               placeholder="Ej: juan@xmotos.com"
               value="<?= e($filtros['buscar']) ?>">
      </div>

      <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-x-primary">
          <i class="bi bi-search me-1"></i> Filtrar
        </button>
        <a href="<?= MONITOR_URL ?>?route=auditoria" class="btn btn-x-outline">
          <i class="bi bi-x-circle me-1"></i> Limpiar
        </a>
      </div>
    </div>
  </form>
</div>

<!-- ============================================================
     RESULTADOS
     ============================================================ -->
<div class="dashboard-table">
  <div class="table-header">
    <h2><i class="bi bi-list-ul me-2"></i>Resultados</h2>
    <span class="count">Página <?= $pagina ?> de <?= $paginas ?></span>
  </div>

  <?php if (empty($operaciones)): ?>
    <div class="p-5 text-center text-x-muted">
      <i class="bi bi-inbox fs-1 d-block mb-2"></i>
      No se encontraron operaciones con los filtros aplicados.
    </div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th style="width:70px;">ID</th>
          <th>Tabla</th>
          <th>Operación</th>
          <th>Registro</th>
          <th>Datos</th>
          <th>Usuario BD</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($operaciones as $op): ?>
          <tr>
            <td><?= (int)$op['id'] ?></td>
            <td><strong><?= e($op['tabla_origen']) ?></strong></td>
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
              <code class="small text-truncate d-inline-block" style="max-width: 280px;"
                    title="<?= e($op['datos']) ?>">
                <?= e($op['datos']) ?>
              </code>
            </td>
            <td class="small text-x-muted"><?= e($op['usuario_bd'] ?? '—') ?></td>
            <td class="small text-x-muted"><?= formatearFecha($op['fecha_replicacion']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- ============================================================
         PAGINACIÓN
         ============================================================ -->
    <?php if ($paginas > 1): ?>
      <div class="p-3 d-flex justify-content-center align-items-center gap-2">
        <?php if ($pagina > 1): ?>
          <a href="<?= urlConFiltros(['pagina' => $pagina - 1]) ?>" class="btn btn-x-outline btn-sm">
            <i class="bi bi-chevron-left"></i> Anterior
          </a>
        <?php endif; ?>

        <span class="text-x-muted small px-3">
          Página <strong><?= $pagina ?></strong> de <strong><?= $paginas ?></strong>
        </span>

        <?php if ($pagina < $paginas): ?>
          <a href="<?= urlConFiltros(['pagina' => $pagina + 1]) ?>" class="btn btn-x-outline btn-sm">
            Siguiente <i class="bi bi-chevron-right"></i>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>?>