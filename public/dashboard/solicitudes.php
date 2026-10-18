<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$solicitudes = $pdo->query("
    SELECT s.*, u.nombre_completo 
    FROM solicitudes s
    INNER JOIN usuarios u ON s.usuario_id = u.id
    ORDER BY s.fecha_registro DESC
")->fetchAll();

$pageTitle = 'Solicitudes';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<h2 class="h5 mb-4">Solicitudes de financiamiento</h2>

<div class="dashboard-table">
  <div class="table-responsive">
    <table class="table mb-0">
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
        <?php if (empty($solicitudes)): ?>
          <tr><td colspan="6" class="text-center text-x-muted py-4">No hay solicitudes.</td></tr>
        <?php else: ?>
          <?php foreach ($solicitudes as $s): ?>
            <tr>
              <td>#<?= $s['id'] ?></td>
              <td><?= e($s['nombre_completo']) ?></td>
              <td><?= e($s['tipo_solicitud']) ?></td>
              <td><span class="x-badge"><?= e($s['estado']) ?></span></td>
              <td><?= date('d/m/Y H:i', strtotime($s['fecha_registro'])) ?></td>
              <td>
                <a href="<?= base_url('dashboard/ver_solicitud.php?id=' . $s['id']) ?>" class="btn btn-sm btn-x-outline">
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

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>