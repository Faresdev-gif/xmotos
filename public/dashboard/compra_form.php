<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$proveedores = $pdo->query("SELECT * FROM proveedores WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
$productos = $pdo->query("SELECT * FROM productos WHERE estado = 'activo' ORDER BY marca, modelo")->fetchAll();

$pageTitle = 'Nueva compra';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="mb-4">
  <a href="<?= base_url('dashboard/compras.php') ?>" class="text-x-muted">
    <i class="bi bi-arrow-left me-2"></i> Volver al listado
  </a>
</div>

<div class="x-card p-4">
  <h2 class="h5 mb-4">Registrar nueva compra</h2>

  <form action="<?= base_url('dashboard/compra_guardar.php') ?>" method="POST" id="formCompra">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Proveedor *</label>
        <select name="proveedor_id" class="form-select" required>
          <option value="">Selecciona un proveedor...</option>
          <?php foreach ($proveedores as $p): ?>
            <option value="<?= $p['id'] ?>">
              <?= e($p['nombre']) ?> — <?= e($p['nit']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Número de factura *</label>
        <input type="text" name="numero_factura" class="form-control" 
               placeholder="Ej: COMP-2026-0004" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Producto *</label>
        <select name="producto_id" class="form-select" required>
          <option value="">Selecciona un producto...</option>
          <?php foreach ($productos as $prod): ?>
            <option value="<?= $prod['id'] ?>" data-precio="<?= $prod['precio_compra'] ?>">
              <?= e($prod['marca'] . ' ' . $prod['modelo']) ?> — 
              $<?= number_format((float)$prod['precio_compra'], 0, ',', '.') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Cantidad *</label>
        <input type="number" name="cantidad" class="form-control" value="1" min="1" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Precio compra unitario *</label>
        <input type="number" name="precio_compra" class="form-control" 
               min="0" step="1" required>
      </div>

      <div class="col-12">
        <label class="form-label">Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"></textarea>
      </div>

      <div class="col-12 d-flex gap-2 justify-content-end mt-3">
        <a href="<?= base_url('dashboard/compras.php') ?>" class="btn btn-x-outline">Cancelar</a>
        <button type="submit" class="btn btn-x-primary">
          <i class="bi bi-check-circle me-2"></i> Registrar compra
        </button>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>