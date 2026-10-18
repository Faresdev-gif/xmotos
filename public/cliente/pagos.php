<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['cliente']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$productoId = (int)($_GET['producto_id'] ?? 0);
$producto = null;
if ($productoId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id AND estado = 'activo'");
    $stmt->execute([':id' => $productoId]);
    $producto = $stmt->fetch();
}

$productos = $pdo->query("SELECT id, marca, modelo, precio FROM productos WHERE estado = 'activo' ORDER BY marca")->fetchAll();

$pageTitle = 'Comprar moto';
require_once __DIR__ . '/../../app/views/layouts/cliente_header.php';
?>

<h2 class="h4 mb-4">Comprar moto</h2>

<div class="row g-4">
  <div class="col-12 col-lg-7">
    <div class="x-card p-4">
      <form action="<?= base_url('pago_procesar.php') ?>" method="POST" id="formPago">
        <div class="mb-3">
          <label class="form-label">Producto *</label>
          <select name="producto_id" id="producto_id" class="form-select" required>
            <option value="">Selecciona una moto...</option>
            <?php foreach ($productos as $p): ?>
              <option value="<?= $p['id'] ?>" data-precio="<?= $p['precio'] ?>"
                      <?= $productoId === (int)$p['id'] ? 'selected' : '' ?>>
                <?= e($p['marca'] . ' ' . $p['modelo']) ?> — $<?= number_format((float)$p['precio'], 0, ',', '.') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Precio</label>
          <input type="text" id="precioMostrar" class="form-control" readonly 
                 value="<?= $producto ? '$ ' . number_format((float)$producto['precio'], 0, ',', '.') : '' ?>">
          <input type="hidden" name="monto" id="precioHidden" value="<?= $producto['precio'] ?? '' ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Método de pago *</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="metodo" id="pse" value="pse" checked>
              <label class="form-check-label" for="pse">
                <i class="bi bi-bank me-1"></i> PSE
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="metodo" id="tarjeta" value="tarjeta">
              <label class="form-check-label" for="tarjeta">
                <i class="bi bi-credit-card me-1"></i> Tarjeta
              </label>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Observaciones</label>
          <textarea name="observaciones" class="form-control" rows="2"></textarea>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-x-primary btn-lg">
            <i class="bi bi-lock me-2"></i> Proceder al pago
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="x-card p-4">
      <h3 class="h5 mb-3">Resumen</h3>
      <div class="d-flex justify-content-between mb-2">
        <span>Subtotal</span>
        <strong id="resSubtotal">$ 0</strong>
      </div>
      <div class="d-flex justify-content-between mb-2">
        <span>IVA (19%)</span>
        <strong id="resIva">$ 0</strong>
      </div>
      <hr>
      <div class="d-flex justify-content-between">
        <span><strong>Total</strong></span>
        <strong id="resTotal" class="text-x-primary">$ 0</strong>
      </div>
    </div>
  </div>
</div>

<script>
  function actualizar() {
    const precio = parseFloat(document.getElementById('precioHidden').value) || 0;
    const iva = precio * 0.19;
    const total = precio + iva;
    document.getElementById('resSubtotal').textContent = '$ ' + precio.toLocaleString('es-CO');
    document.getElementById('resIva').textContent = '$ ' + iva.toLocaleString('es-CO');
    document.getElementById('resTotal').textContent = '$ ' + total.toLocaleString('es-CO');
  }
  document.getElementById('producto_id').addEventListener('change', function () {
    const precio = this.options[this.selectedIndex].dataset.precio;
    document.getElementById('precioHidden').value = precio || 0;
    document.getElementById('precioMostrar').value = precio ? '$ ' + parseInt(precio).toLocaleString('es-CO') : '';
    actualizar();
  });
  actualizar();
</script>

<?php require_once __DIR__ . '/../../app/views/layouts/cliente_footer.php'; ?>