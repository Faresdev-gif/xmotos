<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT v.*, u.nombre_completo AS cliente, u.email, u.telefono
    FROM ventas v
    INNER JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.id = :id
");
$stmt->execute([':id' => $id]);
$venta = $stmt->fetch();

if (!$venta) {
    header('Location: ' . base_url('dashboard/ventas.php'));
    exit;
}

$stmt = $pdo->prepare("
    SELECT vd.*, p.marca, p.modelo, p.imagen
    FROM venta_detalle vd
    INNER JOIN productos p ON vd.producto_id = p.id
    WHERE vd.venta_id = :id
");
$stmt->execute([':id' => $id]);
$detalles = $stmt->fetchAll();

$pageTitle = 'Detalle de venta';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="mb-4">
  <a href="<?= base_url('dashboard/ventas.php') ?>" class="text-x-muted">
    <i class="bi bi-arrow-left me-2"></i> Volver al listado
  </a>
</div>

<div class="x-card p-4 mb-4">
  <h2 class="h5 mb-3">Venta <?= e($venta['numero_factura']) ?></h2>

  <div class="row g-3">
    <div class="col-md-6">
      <p class="text-x-muted mb-1"><strong>Cliente:</strong></p>
      <p><?= e($venta['cliente']) ?></p>
      <p class="text-x-muted mb-0"><?= e($venta['email']) ?> · <?= e($venta['telefono']) ?></p>
    </div>
    <div class="col-md-6">
      <p class="text-x-muted mb-1"><strong>Fecha:</strong></p>
      <p><?= date('d/m/Y H:i', strtotime($venta['fecha_registro'])) ?></p>
      <p class="text-x-muted mb-1"><strong>Estado:</strong></p>
      <span class="status-badge <?= $venta['estado'] === 'pagada' ? 'activo' : 'inactivo' ?>">
        <?= ucfirst(e($venta['estado'])) ?>
      </span>
    </div>
  </div>
</div>

<div class="dashboard-table">
  <div class="table-header">
    <h2>Productos vendidos</h2>
    <span class="count"><?= count($detalles) ?> productos</span>
  </div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>Imagen</th>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Precio unitario</th>
          <th>Subtotal</th>
          <th>Ganancia</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($detalles as $d): ?>
          <tr>
            <td>
              <img src="<?= base_url('img/motos/' . $d['imagen']) ?>" 
                   alt="<?= e($d['marca']) ?>" class="product-thumb">
            </td>
            <td>
              <div class="product-info">
                <span class="brand"><?= e($d['marca']) ?></span>
                <span class="model"><?= e($d['modelo']) ?></span>
              </div>
            </td>
            <td><?= (int)$d['cantidad'] ?></td>
            <td>$<?= number_format((float)$d['precio_unitario'], 0, ',', '.') ?></td>
            <td><strong>$<?= number_format((float)$d['subtotal'], 0, ',', '.') ?></strong></td>
            <td>
              <strong style="color: #10B981;">
                $<?= number_format(
                    ((float)$d['precio_unitario'] - (float)$d['precio_compra_unitario']) * (int)$d['cantidad'],
                    0, ',', '.'
                ) ?>
              </strong>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
          <td colspan="2"><strong>$<?= number_format((float)$venta['subtotal'], 0, ',', '.') ?></strong></td>
        </tr>
        <tr>
          <td colspan="4" class="text-end"><strong>IVA (19%):</strong></td>
          <td colspan="2"><strong>$<?= number_format((float)$venta['iva'], 0, ',', '.') ?></strong></td>
        </tr>
        <tr>
          <td colspan="4" class="text-end"><strong>Total:</strong></td>
          <td colspan="2"><strong style="color: var(--x-primary-dark);">$<?= number_format((float)$venta['total'], 0, ',', '.') ?></strong></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>