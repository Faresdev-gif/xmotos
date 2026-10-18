<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/config/helpers.php';

if (!isset($_SESSION['venta_exitosa'])) {
    header('Location: ' . base_url('cliente/pagos.php'));
    exit;
}

$datos = $_SESSION['venta_exitosa'];
$pageTitle = 'Pago exitoso';
require_once __DIR__ . '/../../app/views/layouts/cliente_header.php';
?>

<div class="x-card p-5 text-center">
  <i class="bi bi-check-circle-fill" style="font-size: 5rem; color: #10B981;"></i>
  <h2 class="h3 mt-4 mb-3">¡Pago procesado correctamente!</h2>
  <p class="text-x-muted mb-4">
    Tu compra ha sido registrada exitosamente.
  </p>

  <div class="x-card p-4 mb-4" style="background: var(--x-surface-2); max-width: 500px; margin: 0 auto;">
    <div class="d-flex justify-content-between mb-2">
      <span class="text-x-muted">Número de factura:</span>
      <strong><?= e($datos['numero_factura']) ?></strong>
    </div>
    <div class="d-flex justify-content-between mb-2">
      <span class="text-x-muted">Método de pago:</span>
      <strong><?= e(strtoupper($datos['metodo'])) ?></strong>
    </div>
    <div class="d-flex justify-content-between">
      <span class="text-x-muted">Total:</span>
      <strong class="text-x-primary">
        $<?= number_format((float)$datos['total'], 0, ',', '.') ?>
      </strong>
    </div>
  </div>

  <div class="d-flex gap-2 justify-content-center">
    <a href="<?= base_url('cliente/facturas.php') ?>" class="btn btn-x-primary">
      <i class="bi bi-receipt me-2"></i> Ver mis facturas
    </a>
    <a href="<?= base_url('productos.php') ?>" class="btn btn-x-outline">
      <i class="bi bi-box-seam me-2"></i> Seguir comprando
    </a>
  </div>
</div>

<?php
// Limpiar sesión
unset($_SESSION['venta_exitosa']);
require_once __DIR__ . '/../../app/views/layouts/cliente_footer.php';
?>