<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['cliente']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$stmt = $pdo->prepare("
    SELECT v.*, 
           (SELECT GROUP_CONCAT(CONCAT(p.marca, ' ', p.modelo) SEPARATOR ', ')
            FROM venta_detalle vd 
            INNER JOIN productos p ON vd.producto_id = p.id 
            WHERE vd.venta_id = v.id) AS productos
    FROM ventas v
    WHERE v.usuario_id = :uid
    ORDER BY v.fecha_registro DESC
");
$stmt->execute([':uid' => $_SESSION['usuario_id']]);
$facturas = $stmt->fetchAll();

$pageTitle = 'Mis facturas';
require_once __DIR__ . '/../../app/views/layouts/cliente_header.php';
?>

<h2 class="h4 mb-4">Mis facturas</h2>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success mb-4">
    <i class="bi bi-check-circle me-2"></i> <?= e($_GET['msg']) ?>
  </div>
<?php endif; ?>

<?php if (empty($facturas)): ?>
  <div class="x-card p-5 text-center">
    <i class="bi bi-receipt fs-1 text-x-muted"></i>
    <p class="text-x-muted mt-3 mb-3">Aún no tienes facturas.</p>
    <a href="<?= base_url('productos.php') ?>" class="btn btn-x-primary">
      Ver catálogo
    </a>
  </div>
<?php else: ?>
  <div class="dashboard-table">
    <div class="table-header">
      <h2><i class="bi bi-receipt me-2" style="color: var(--x-primary);"></i> Mis compras</h2>
      <span class="count"><?= count($facturas) ?> facturas</span>
    </div>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th># Factura</th>
            <th>Productos</th>
            <th>Subtotal</th>
            <th>IVA</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($facturas as $f): ?>
            <tr>
              <td><strong><?= e($f['numero_factura']) ?></strong></td>
              <td><?= e($f['productos'] ?? '—') ?></td>
              <td>$<?= number_format((float)$f['subtotal'], 0, ',', '.') ?></td>
              <td>$<?= number_format((float)$f['iva'], 0, ',', '.') ?></td>
              <td><strong class="text-x-primary">$<?= number_format((float)$f['total'], 0, ',', '.') ?></strong></td>
              <td>
                <span class="status-badge <?= $f['estado'] === 'pagada' ? 'activo' : 'inactivo' ?>">
                  <?= ucfirst(e($f['estado'])) ?>
                </span>
              </td>
              <td><?= date('d/m/Y H:i', strtotime($f['fecha_registro'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../app/views/layouts/cliente_footer.php'; ?>