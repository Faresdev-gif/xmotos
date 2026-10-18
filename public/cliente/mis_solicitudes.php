<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['cliente']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$stmt = $pdo->prepare("
    SELECT s.*, p.marca, p.modelo, p.imagen
    FROM solicitudes s
    LEFT JOIN productos p ON s.producto_id = p.id
    WHERE s.usuario_id = :uid
    ORDER BY s.fecha_registro DESC
");
$stmt->execute([':uid' => $_SESSION['usuario_id']]);
$solicitudes = $stmt->fetchAll();

$pageTitle = 'Mis solicitudes';
require_once __DIR__ . '/../../app/views/layouts/cliente_header.php';
?>

<h2 class="h4 mb-4">Mis solicitudes</h2>

<?php if (empty($solicitudes)): ?>
  <div class="x-card p-5 text-center">
    <i class="bi bi-inbox fs-1 text-x-muted"></i>
    <p class="text-x-muted mt-3 mb-0">No tienes solicitudes registradas.</p>
    <a href="<?= base_url('cliente/financiamiento.php') ?>" class="btn btn-x-primary mt-3">
      Solicitar financiamiento
    </a>
  </div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($solicitudes as $s): ?>
      <div class="col-12">
        <div class="x-card p-4">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
              <h3 class="h5 mb-1">
                <?= e($s['marca'] ?? 'Sin moto') ?> <?= e($s['modelo'] ?? '') ?>
              </h3>
              <p class="text-x-muted small mb-0">
                Solicitud #<?= $s['id'] ?> · 
                <?= date('d/m/Y H:i', strtotime($s['fecha_registro'])) ?>
              </p>
            </div>
            <div>
              <span class="status-badge <?= $s['estado'] === 'aprobado' ? 'activo' : 'inactivo' ?>">
                <?= ucfirst(e($s['estado'])) ?>
              </span>
            </div>
          </div>
          <hr>
          <div class="row g-3">
            <div class="col-md-4">
              <p class="text-x-muted mb-1 small">Monto solicitado</p>
              <strong>$<?= number_format((float)$s['monto_solicitado'], 0, ',', '.') ?></strong>
            </div>
            <div class="col-md-4">
              <p class="text-x-muted mb-1 small">Cuota mensual</p>
              <strong class="text-x-primary">$<?= number_format((float)$s['cuota_mensual_estimada'], 0, ',', '.') ?></strong>
            </div>
            <div class="col-md-4">
              <p class="text-x-muted mb-1 small">Plazo</p>
              <strong><?= (int)$s['plazo_meses'] ?> meses</strong>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../app/views/layouts/cliente_footer.php'; ?>