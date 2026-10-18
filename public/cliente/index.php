<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['cliente']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$uid = (int)$_SESSION['usuario_id'];

$totalFavoritos = (int)$pdo->query("SELECT COUNT(*) FROM favoritos WHERE usuario_id = $uid")->fetchColumn();
$totalVentas = (int)$pdo->query("SELECT COUNT(*) FROM ventas WHERE usuario_id = $uid")->fetchColumn();
$totalSolicitudes = (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE usuario_id = $uid")->fetchColumn();

$pageTitle = 'Mi panel';
require_once __DIR__ . '/../../app/views/layouts/cliente_header.php';
?>

<h2 class="h4 mb-4">Bienvenido, <?= e($_SESSION['nombre']) ?></h2>

<div class="row g-4 mb-4">
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Mis favoritos</h6>
        <h3><?= $totalFavoritos ?></h3>
      </div>
      <div class="stat-icon-circle"><i class="bi bi-heart"></i></div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Mis compras</h6>
        <h3><?= $totalVentas ?></h3>
      </div>
      <div class="stat-icon-circle"><i class="bi bi-cart-check"></i></div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Mis solicitudes</h6>
        <h3><?= $totalSolicitudes ?></h3>
      </div>
      <div class="stat-icon-circle"><i class="bi bi-file-earmark-text"></i></div>
    </div>
  </div>
</div>

<div class="x-card p-4">
  <h3 class="h5 mb-3">¿Qué quieres hacer?</h3>
  <div class="row g-3">
    <div class="col-12 col-md-6">
      <a href="<?= base_url('productos.php') ?>" class="btn btn-x-primary w-100 p-3">
        <i class="bi bi-box-seam me-2"></i> Ver catálogo
      </a>
    </div>
    <div class="col-12 col-md-6">
      <a href="<?= base_url('cliente/favoritos.php') ?>" class="btn btn-x-outline w-100 p-3">
        <i class="bi bi-heart me-2"></i> Mis favoritos
      </a>
    </div>
    <div class="col-12 col-md-6">
      <a href="<?= base_url('cliente/financiamiento.php') ?>" class="btn btn-x-outline w-100 p-3">
        <i class="bi bi-cash-coin me-2"></i> Solicitar financiamiento
      </a>
    </div>
    <div class="col-12 col-md-6">
      <a href="<?= base_url('cliente/facturas.php') ?>" class="btn btn-x-outline w-100 p-3">
        <i class="bi bi-receipt me-2"></i> Mis facturas
      </a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/cliente_footer.php'; ?>