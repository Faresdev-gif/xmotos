<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT c.*, p.nombre AS proveedor_nombre, p.nit, p.telefono, p.email
    FROM compras c
    INNER JOIN proveedores p ON c.proveedor_id = p.id
    WHERE c.id = :id
");
$stmt->execute([':id' => $id]);
$compra = $stmt->fetch();

if (!$compra) {
    header('Location: ' . base_url('dashboard/compras.php'));
    exit;
}

$stmt = $pdo->prepare("
    SELECT cd.*, pr.marca, pr.modelo, pr.imagen
    FROM compras_detalle cd
    INNER JOIN productos pr ON cd.producto_id = pr.id
    WHERE cd.compra_id = :id
");
$stmt->execute([':id' => $id]);
$detalles = $stmt->fetchAll();

$pageTitle = 'Detalle de compra';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="mb-4">
  <a href="<?= base_url('dashboard/compras.php') ?>" class="text-x-muted">
    <i class="bi bi-arrow-left me-2"></i> Volver al listado
  </a>
</div>

<div class="x-card p-4 mb-4">
  <h2 class="h5 mb-3">Compra <?= e($compra['numero_factura']) ?></h2>

  <div class="row g-3">
    <div class="col-md-6">
      <p class="text-x-muted mb-1"><strong>Proveedor:</strong></p>
      <p><?= e($compra['proveedor_nombre']) ?> — <?= e($compra['nit']) ?></p>
      <p class="text-x-muted mb-0"><?= e($compra['telefono']) ?> · <?= e($compra['email']) ?></p>
    </div>
    <div class="col-md-6">
      <p class="text-x-muted mb-1"><strong>Fecha:</strong></p>
      <p><?= date('d/m/Y H:i', strtotime($compra['fecha_registro'])) ?></p>
      <p class="text-x-muted mb-1"><strong>Estado:</strong></p>
      <span class="status-badge <?= $compra['estado'] === 'pagada' ? 'activo' : 'inactivo' ?>">
        <?= ucfirst(e($compra['estado'])) ?>
      </span>
    </div>
  </div>

  <?php if ($compra['observaciones']): ?>
    <hr>
    <p class="text-x-muted mb-1"><strong>Observaciones:</strong></p>
    <p><?= e($compra['observaciones']) ?></p>
  <?php endif; ?>
</div>

<div class="dashboard-table">
  <div class="table-header">
    <h2>Productos comprados</h2>
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
            <td>$<?= number_format((float)$d['precio_compra_unitario'], 0, ',', '.') ?></td>
            <td><strong>$<?= number_format((float)$d['subtotal'], 0, ',', '.') ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
          <td><strong>$<?= number_format((float)$compra['subtotal'], 0, ',', '.') ?></strong></td>
        </tr>
        <tr>
          <td colspan="4" class="text-end"><strong>IVA (19%):</strong></td>
          <td><strong>$<?= number_format((float)$compra['iva'], 0, ',', '.') ?></strong></td>
        </tr>
        <tr>
          <td colspan="4" class="text-end"><strong>Total:</strong></td>
          <td><strong style="color: var(--x-primary-dark);">$<?= number_format((float)$compra['total'], 0, ',', '.') ?></strong></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>