<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['cliente']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

$stmt = $pdo->prepare("
    SELECT p.*, c.nombre AS categoria_nombre
    FROM favoritos f
    INNER JOIN productos p ON f.producto_id = p.id
    INNER JOIN categorias c ON p.categoria_id = c.id
    WHERE f.usuario_id = :uid
    ORDER BY f.fecha_registro DESC
");
$stmt->execute([':uid' => $_SESSION['usuario_id']]);
$favoritos = $stmt->fetchAll();

$pageTitle = 'Mis favoritos';
require_once __DIR__ . '/../../app/views/layouts/cliente_header.php';
?>

<h2 class="h4 mb-4">Mis favoritos</h2>

<?php if (empty($favoritos)): ?>
  <div class="x-card p-5 text-center">
    <i class="bi bi-heart fs-1 text-x-muted"></i>
    <p class="text-x-muted mt-3 mb-3">Aún no tienes motos favoritas.</p>
    <a href="<?= base_url('productos.php') ?>" class="btn btn-x-primary">Ver catálogo</a>
  </div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($favoritos as $prod): ?>
      <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
        <div class="x-card h-100 overflow-hidden product-card">
          <div class="position-relative">
            <img src="<?= base_url('img/motos/' . $prod['imagen']) ?>" 
                 alt="<?= e($prod['marca']) ?>"
                 class="w-100" style="height: 200px; object-fit: cover;">
            <a href="<?= base_url('favorito_eliminar.php?id=' . $prod['id']) ?>" 
               class="btn-favorito position-absolute top-0 end-0 m-2 activo"
               title="Quitar de favoritos">
              <i class="bi bi-heart-fill"></i>
            </a>
          </div>
          <div class="p-3">
            <h3 class="h6 mb-1"><?= e($prod['marca']) ?></h3>
            <p class="text-x-muted small mb-3"><?= e($prod['modelo']) ?></p>
            <div class="d-flex justify-content-between mb-3">
              <span class="text-x-muted small">Precio</span>
              <strong class="text-x-primary">$<?= number_format((float)$prod['precio'], 0, ',', '.') ?></strong>
            </div>
            <a href="<?= base_url('cliente/pagos.php?producto_id=' . $prod['id']) ?>" 
               class="btn btn-x-primary w-100">
              <i class="bi bi-bag-check me-1"></i> Comprar
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../app/views/layouts/cliente_footer.php'; ?>