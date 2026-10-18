<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT s.*, 
           u.nombre_completo AS usuario_nombre, u.email AS usuario_email,
           p.marca, p.modelo, p.imagen, p.precio AS producto_precio,
           ap.nombre_completo AS aprobador_nombre
    FROM solicitudes s
    INNER JOIN usuarios u ON s.usuario_id = u.id
    LEFT JOIN productos p ON s.producto_id = p.id
    LEFT JOIN usuarios ap ON s.aprobado_por = ap.id
    WHERE s.id = :id
");
$stmt->execute([':id' => $id]);
$sol = $stmt->fetch();

if (!$sol) {
    header('Location: ' . base_url('dashboard/solicitudes.php'));
    exit;
}

$pageTitle = 'Solicitud #' . $id;
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="mb-4">
  <a href="<?= base_url('dashboard/solicitudes.php') ?>" class="text-x-muted">
    <i class="bi bi-arrow-left me-2"></i> Volver al listado
  </a>
</div>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i>
    <span><?= e($_GET['msg']) ?></span>
  </div>
<?php endif; ?>

<div class="row g-4">
  <!-- COLUMNA IZQUIERDA: DATOS DEL CLIENTE -->
  <div class="col-12 col-lg-7">
    <div class="x-card p-4 mb-4">
      <h2 class="h5 mb-3">
        <i class="bi bi-person-fill text-x-primary me-2"></i>
        Datos del cliente
      </h2>
      <div class="row g-3">
        <div class="col-md-6">
          <p class="text-x-muted mb-1 small">Nombre completo</p>
          <p class="mb-0"><strong><?= e($sol['nombre'] . ' ' . $sol['apellidos']) ?></strong></p>
        </div>
        <div class="col-md-6">
          <p class="text-x-muted mb-1 small">Documento</p>
          <p class="mb-0"><?= e($sol['tipo_doc']) ?> <?= e($sol['cedula']) ?></p>
        </div>
        <div class="col-md-6">
          <p class="text-x-muted mb-1 small">Correo</p>
          <p class="mb-0"><?= e($sol['correo']) ?></p>
        </div>
        <div class="col-md-6">
          <p class="text-x-muted mb-1 small">Teléfono</p>
          <p class="mb-0"><?= e($sol['telefono']) ?></p>
        </div>
        <div class="col-md-6">
          <p class="text-x-muted mb-1 small">Fecha nacimiento</p>
          <p class="mb-0"><?= date('d/m/Y', strtotime($sol['fecha_nac'])) ?></p>
        </div>
        <div class="col-md-6">
          <p class="text-x-muted mb-1 small">Ubicación</p>
          <p class="mb-0"><?= e($sol['ciudad']) ?>, <?= e($sol['departamento']) ?></p>
        </div>
        <div class="col-12">
          <p class="text-x-muted mb-1 small">Dirección</p>
          <p class="mb-0"><?= e($sol['direccion']) ?></p>
        </div>
      </div>
    </div>

    <!-- ARCHIVOS ADJUNTOS -->
    <div class="x-card p-4 mb-4">
      <h2 class="h5 mb-3">
        <i class="bi bi-paperclip text-x-primary me-2"></i>
        Documentos adjuntos
      </h2>
      <div class="row g-3">
        <?php if (!empty($sol['archivo_cedula'])): ?>
          <div class="col-md-6">
            <p class="text-x-muted mb-2 small">Cédula</p>
            <a href="<?= base_url('descargar.php?file=' . urlencode($sol['archivo_cedula'])) ?>" 
               target="_blank" class="btn btn-x-outline w-100">
              <i class="bi bi-file-earmark-pdf me-2"></i> Ver cédula
            </a>
          </div>
        <?php endif; ?>
        <?php if (!empty($sol['archivo_ingresos'])): ?>
          <div class="col-md-6">
            <p class="text-x-muted mb-2 small">Certificado de ingresos</p>
            <a href="<?= base_url('descargar.php?file=' . urlencode($sol['archivo_ingresos'])) ?>" 
               target="_blank" class="btn btn-x-outline w-100">
              <i class="bi bi-file-earmark-pdf me-2"></i> Ver certificado
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- COLUMNA DERECHA: PRODUCTO Y ESTADO -->
  <div class="col-12 col-lg-5">
    <div class="x-card p-4 mb-4">
      <h2 class="h5 mb-3">
        <i class="bi bi-bicycle text-x-primary me-2"></i>
        Moto de interés
      </h2>
      <?php if (!empty($sol['marca'])): ?>
        <img src="<?= base_url('img/motos/' . $sol['imagen']) ?>" 
             alt="<?= e($sol['marca']) ?>" 
             class="w-100 rounded mb-3" style="height: 180px; object-fit: cover;">
        <p class="mb-1"><strong><?= e($sol['marca'] . ' ' . $sol['modelo']) ?></strong></p>
        <p class="text-x-muted mb-0">
          Precio: <strong class="text-x-primary">$<?= number_format((float)$sol['producto_precio'], 0, ',', '.') ?></strong>
        </p>
      <?php else: ?>
        <p class="text-x-muted">Sin producto asociado.</p>
      <?php endif; ?>
    </div>

    <div class="x-card p-4 mb-4">
      <h2 class="h5 mb-3">
        <i class="bi bi-cash-coin text-x-primary me-2"></i>
        Información financiera
      </h2>
      <div class="d-flex justify-content-between mb-2">
        <span class="text-x-muted">Monto solicitado</span>
        <strong>$<?= number_format((float)$sol['monto_solicitado'], 0, ',', '.') ?></strong>
      </div>
      <div class="d-flex justify-content-between mb-2">
        <span class="text-x-muted">Cuota inicial</span>
        <strong><?= number_format((float)$sol['cuota_inicial_pct'], 2) ?>%</strong>
      </div>
      <div class="d-flex justify-content-between mb-2">
        <span class="text-x-muted">Plazo</span>
        <strong><?= (int)$sol['plazo_meses'] ?> meses</strong>
      </div>
      <div class="d-flex justify-content-between mb-3 pb-3" style="border-bottom: 1px solid var(--x-border);">
        <span class="text-x-muted">Cuota mensual estimada</span>
        <strong class="text-x-primary">$<?= number_format((float)$sol['cuota_mensual_estimada'], 0, ',', '.') ?></strong>
      </div>
      <p class="text-x-muted mb-1 small">Tipo de solicitud</p>
      <p class="mb-0"><strong><?= ucfirst(e($sol['tipo_solicitud'])) ?></strong></p>
    </div>

    <div class="x-card p-4">
      <h2 class="h5 mb-3">
        <i class="bi bi-info-circle text-x-primary me-2"></i>
        Estado
      </h2>
      <span class="status-badge <?= $sol['estado'] === 'aprobado' ? 'activo' : ($sol['estado'] === 'cancelado' ? 'inactivo' : '') ?> mb-3">
        <?= ucfirst(e($sol['estado'])) ?>
      </span>
      <p class="text-x-muted mb-1 small">Fecha de solicitud</p>
      <p class="mb-0"><?= date('d/m/Y H:i', strtotime($sol['fecha_registro'])) ?></p>

      <?php if ($sol['aprobado_por']): ?>
        <hr>
        <p class="text-x-muted mb-1 small">Aprobado por</p>
        <p class="mb-0"><?= e($sol['aprobador_nombre']) ?></p>
        <p class="text-x-muted mb-1 small mt-2">Fecha de aprobación</p>
        <p class="mb-0"><?= date('d/m/Y H:i', strtotime($sol['fecha_aprobacion'])) ?></p>
      <?php endif; ?>

      <?php if ($sol['observaciones']): ?>
        <hr>
        <p class="text-x-muted mb-1 small">Observaciones</p>
        <p class="mb-0"><?= e($sol['observaciones']) ?></p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ACCIONES DE ESTADO -->
<div class="x-card p-4 mt-4">
  <h2 class="h5 mb-3">
    <i class="bi bi-gear text-x-primary me-2"></i>
    Cambiar estado
  </h2>
  <div class="d-flex flex-wrap gap-2">
    <?php if ($sol['estado'] !== 'aprobado'): ?>
      <a href="<?= base_url('dashboard/solicitud_guardar.php?accion=aprobar&id=' . $sol['id']) ?>" 
         class="btn btn-x-primary"
         onclick="return confirm('¿Aprobar esta solicitud?')">
        <i class="bi bi-check-circle me-2"></i> Aprobar
      </a>
    <?php endif; ?>

    <?php if ($sol['estado'] !== 'en_negociacion'): ?>
      <a href="<?= base_url('dashboard/solicitud_guardar.php?accion=negociacion&id=' . $sol['id']) ?>" 
         class="btn btn-x-outline">
        <i class="bi bi-chat-dots me-2"></i> En negociación
      </a>
    <?php endif; ?>

   
    <?php if ($sol['estado'] !== 'cancelado'): ?>
      <a href="<?= base_url('dashboard/solicitud_guardar.php?accion=cancelar&id=' . $sol['id']) ?>" 
         class="btn btn-x-outline"
         style="border-color: var(--x-danger); color: var(--x-danger);"
         onclick="return confirm('¿Cancelar esta solicitud?')">
        <i class="bi bi-x-circle me-2"></i> Cancelar
      </a>
    <?php endif; ?>
  </div>

  <!-- Si está aprobado, mostrar botón para convertir en venta -->
  <?php if ($sol['estado'] === 'aprobado' && !empty($sol['producto_id'])): ?>
    <hr class="my-4">
    <h3 class="h6 mb-3">Convertir en venta</h3>
    <a href="<?= base_url('dashboard/venta_desde_solicitud.php?id=' . $sol['id']) ?>" 
       class="btn btn-x-primary"
       onclick="return confirm('¿Registrar la venta de esta moto al cliente? Se descontará del stock.')">
      <i class="bi bi-cart-check me-2"></i> Registrar venta
    </a>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>