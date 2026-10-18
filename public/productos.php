<?php
/**
 * XMOTOS - Catálogo de productos (público)
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/helpers.php';
require_once __DIR__ . '/../app/config/database.php';

$pdo = Database::getConnection();

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY id")->fetchAll();

$categoriaSlug = $_GET['categoria'] ?? '';
$productos = [];

if ($categoriaSlug) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre AS categoria_nombre, c.slug AS categoria_slug
        FROM productos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        WHERE c.slug = :slug AND p.estado = 'activo'
        ORDER BY p.marca, p.modelo
    ");
    $stmt->execute([':slug' => $categoriaSlug]);
    $productos = $stmt->fetchAll();
} else {
    $productos = $pdo->query("
        SELECT p.*, c.nombre AS categoria_nombre, c.slug AS categoria_slug
        FROM productos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        WHERE p.estado = 'activo'
        ORDER BY c.id, p.marca, p.modelo
    ")->fetchAll();
}

$pageTitle = 'Catálogo';
require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<!-- HERO -->
<header class="hero-x" style="min-height: auto; padding: 3rem 1rem;">
  <div class="container position-relative" style="z-index: 2;">
    <div class="row justify-content-center text-center">
      <div class="col-12 col-lg-8">
        <span class="x-badge mb-3">XMOTOS · Catálogo</span>
        <h1 class="display-5 fw-bold mb-3">Encuentra tu moto ideal</h1>
        <p class="mb-0 fs-5">
          Explora por categoría, marca o modelo.
        </p>
      </div>
    </div>
  </div>
</header>

<!-- FILTRO DE CATEGORÍAS -->
<section class="x-section pb-0">
  <div class="container">
    <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
      <a href="<?= base_url('productos.php') ?>" 
         class="btn <?= $categoriaSlug === '' ? 'btn-x-primary' : 'btn-x-outline' ?>">
        <i class="bi bi-grid me-1"></i> Todas
      </a>
      <?php foreach ($categorias as $cat): ?>
        <a href="<?= base_url('productos.php?categoria=' . $cat['slug']) ?>" 
           class="btn <?= $categoriaSlug === $cat['slug'] ? 'btn-x-primary' : 'btn-x-outline' ?>">
          <i class="bi <?= e($cat['icono']) ?> me-1"></i> <?= e($cat['nombre']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- GRID DE PRODUCTOS -->
<section class="x-section pt-4">
  <div class="container">
    <?php if (empty($productos)): ?>
      <div class="x-card p-5 text-center">
        <i class="bi bi-search fs-1 text-x-muted"></i>
        <p class="text-x-muted mt-3 mb-0">No hay productos en esta categoría.</p>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($productos as $prod): ?>
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="x-card h-100 overflow-hidden product-card">
              <div class="position-relative">
                <img src="<?= base_url('img/motos/' . $prod['imagen']) ?>" 
                     alt="<?= e($prod['marca'] . ' ' . $prod['modelo']) ?>"
                     class="w-100" style="height: 200px; object-fit: cover;">

                <button class="btn-favorito position-absolute top-0 end-0 m-2" 
                        data-producto="<?= $prod['id'] ?>" 
                        title="Añadir a favoritos">
                  <i class="bi bi-heart-fill"></i>
                </button>
              </div>

              <div class="p-3">
                <h3 class="h6 mb-1"><?= e($prod['marca']) ?></h3>
                <p class="text-x-muted small mb-3"><?= e($prod['modelo']) ?></p>

                <div class="d-flex justify-content-between mb-2 small">
                  <span class="text-x-muted">Cilindraje</span>
                  <strong><?= e($prod['cilindraje']) ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-3">
                  <span class="text-x-muted small">Precio</span>
                  <strong class="text-x-primary">
                    $<?= number_format((float)$prod['precio'], 0, ',', '.') ?>
                  </strong>
                </div>

                <?php if (isset($_SESSION['usuario_id'])): ?>
                  <a href="<?= base_url('cliente/pagos.php?producto_id=' . $prod['id']) ?>" 
                     class="btn btn-x-primary w-100">
                    <i class="bi bi-bag-check me-1"></i> Comprar
                  </a>
                <?php else: ?>
                  <a href="<?= base_url('login.php?rol=cliente&redirect=' . urlencode('cliente/pagos.php?producto_id=' . $prod['id'])) ?>" 
                     class="btn btn-x-primary w-100">
                    <i class="bi bi-bag-check me-1"></i> Comprar
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../app/views/layouts/footer.php'; ?>