<?php
/**
 * XMOTOS - Inventario (dashboard)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

// =====================================================
// FILTROS
// =====================================================
$buscar = trim($_GET['buscar'] ?? '');
$filtroStock = trim($_GET['stock'] ?? '');

$sql = "SELECT p.*, c.nombre AS categoria_nombre
        FROM productos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        WHERE 1=1";
$params = [];

// Filtro por búsqueda (usa dos placeholders distintos)
if ($buscar !== '') {
    $sql .= " AND (p.marca LIKE :buscar1 OR p.modelo LIKE :buscar2)";
    $params[':buscar1'] = '%' . $buscar . '%';
    $params[':buscar2'] = '%' . $buscar . '%';
}

// Filtro por stock
if ($filtroStock === 'bajo') {
    $sql .= " AND p.stock > 0 AND p.stock < 5";
} elseif ($filtroStock === 'agotado') {
    $sql .= " AND p.stock = 0";
}

$sql .= " ORDER BY p.stock ASC, p.marca";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// =====================================================
// ESTADÍSTICAS
// =====================================================
$totalProductos = count($productos);
$totalStock = array_sum(array_column($productos, 'stock'));
$valorInventario = array_sum(array_map(fn($p) => (float)$p['precio'] * (int)$p['stock'], $productos));

$pageTitle = 'Inventario';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="mb-4">
  <h2 class="h4 mb-1">Inventario</h2>
  <p class="text-x-muted mb-0">Consulta el stock y valor del inventario</p>
</div>

<!-- ESTADÍSTICAS -->
<div class="row g-4 mb-4">
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Productos en inventario</h6>
        <h3><?= $totalProductos ?></h3>
      </div>
      <div class="stat-icon-circle"><i class="bi bi-box-seam"></i></div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Unidades en stock</h6>
        <h3><?= $totalStock ?></h3>
      </div>
      <div class="stat-icon-circle"><i class="bi bi-stack"></i></div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <div class="stat-content">
        <h6>Valor total del inventario</h6>
        <h3>$<?= number_format($valorInventario, 0, ',', '.') ?></h3>
      </div>
      <div class="stat-icon-circle"><i class="bi bi-cash-stack"></i></div>
    </div>
  </div>
</div>

<!-- FILTROS -->
<div class="x-card p-3 mb-4">
  <form method="GET" class="row g-3 align-items-end">
    <div class="col-12 col-md-4">
      <label class="form-label">Stock</label>
      <select name="stock" class="form-select">
        <option value="">Todos</option>
        <option value="bajo" <?= $filtroStock === 'bajo' ? 'selected' : '' ?>>Stock bajo (&lt; 5)</option>
        <option value="agotado" <?= $filtroStock === 'agotado' ? 'selected' : '' ?>>Agotado (0)</option>
      </select>
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label">Buscar</label>
      <input type="text" name="buscar" class="form-control"
             placeholder="Buscar por marca o modelo..."
             value="<?= e($buscar) ?>">
    </div>
    <div class="col-12 col-md-2">
      <button type="submit" class="btn btn-x-primary w-100">
        <i class="bi bi-search me-1"></i> Filtrar
      </button>
    </div>
  </form>
</div>

<!-- TABLA -->
<div class="dashboard-table">
  <div class="table-header">
    <h2><i class="bi bi-clipboard-data me-2" style="color: var(--x-primary);"></i> Inventario de productos</h2>
    <span class="count"><?= count($productos) ?> productos</span>
  </div>

  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Producto</th>
          <th>Categoría</th>
          <th>Cilindraje</th>
          <th>Precio compra</th>
          <th>Precio venta</th>
          <th>Ganancia / und</th>
          <th>Stock</th>
          <th>Valor total</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($productos)): ?>
          <tr>
            <td colspan="10" class="text-center py-5">
              <i class="bi bi-inbox fs-1 text-x-muted d-block mb-2"></i>
              <span class="text-x-muted">No se encontraron productos.</span>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($productos as $p): ?>
            <tr>
              <td>#<?= $p['id'] ?></td>
              <td>
                <div class="product-info">
                  <span class="brand"><?= e($p['marca']) ?></span>
                  <span class="model"><?= e($p['modelo']) ?></span>
                </div>
              </td>
              <td><span class="category-badge"><?= e($p['categoria_nombre']) ?></span></td>
              <td><?= e($p['cilindraje']) ?></td>
              <td>$<?= number_format((float)$p['precio_compra'], 0, ',', '.') ?></td>
              <td>$<?= number_format((float)$p['precio'], 0, ',', '.') ?></td>
              <td>
                <strong style="color: #10B981;">
                  $<?= number_format((float)$p['precio'] - (float)$p['precio_compra'], 0, ',', '.') ?>
                </strong>
              </td>
              <td>
                <?php
                  $stockClass = '';
                  if ((int)$p['stock'] === 0) $stockClass = 'out';
                  elseif ((int)$p['stock'] < 5) $stockClass = 'low';
                ?>
                <span class="stock-badge <?= $stockClass ?>"><?= (int)$p['stock'] ?></span>
              </td>
              <td>
                <strong style="color: var(--x-primary-dark);">
                  $<?= number_format((float)$p['precio'] * (int)$p['stock'], 0, ',', '.') ?>
                </strong>
              </td>
              <td>
                <span class="status-badge <?= e($p['estado']) ?>">
                  <?= e(ucfirst($p['estado'])) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>