<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

// Filtros
$buscar = trim($_GET['buscar'] ?? '');
$filtroCategoria = (int)($_GET['categoria'] ?? 0);
$filtroEstado = $_GET['estado'] ?? '';

$sql = "SELECT p.*, c.nombre AS categoria_nombre
        FROM productos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        WHERE 1=1";
$params = [];

if ($buscar !== '') {
    $sql .= " AND (p.marca LIKE :buscar OR p.modelo LIKE :buscar)";
    $params[':buscar'] = '%' . $buscar . '%';
}

if ($filtroCategoria > 0) {
    $sql .= " AND p.categoria_id = :categoria";
    $params[':categoria'] = $filtroCategoria;
}

if ($filtroEstado !== '' && in_array($filtroEstado, ['activo', 'inactivo'])) {
    $sql .= " AND p.estado = :estado";
    $params[':estado'] = $filtroEstado;
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();

$pageTitle = 'Productos';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<!-- ENCABEZADO -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h2 class="h4 mb-1">Gestión de productos</h2>
    <p class="text-x-muted mb-0">Administra el catálogo de motos de XMOTOS</p>
  </div>
  <a href="<?= base_url('dashboard/producto_form.php') ?>" class="btn btn-x-primary">
    <i class="bi bi-plus-circle me-2"></i> Nuevo producto
  </a>
</div>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i>
    <span><?= e($_GET['msg']) ?></span>
  </div>
<?php endif; ?>

<!-- FILTROS -->
<div class="x-card p-3 mb-4">
  <form method="GET" class="row g-3 align-items-end">
    <div class="col-12 col-md-3">
      <label class="form-label">Categoría</label>
      <select name="categoria" class="form-select">
        <option value="">Todas</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $filtroCategoria === (int)$cat['id'] ? 'selected' : '' ?>>
            <?= e($cat['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Estado</label>
      <select name="estado" class="form-select">
        <option value="">Todos</option>
        <option value="activo" <?= $filtroEstado === 'activo' ? 'selected' : '' ?>>Activo</option>
        <option value="inactivo" <?= $filtroEstado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
      </select>
    </div>
    <div class="col-12 col-md-4">
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
    <h2><i class="bi bi-box-seam me-2" style="color: var(--x-primary);"></i> Listado de productos</h2>
    <span class="count"><?= count($productos) ?> productos</span>
  </div>

  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
  <tr>
    <th>ID</th>
    <th>Imagen</th>
    <th>Producto</th>
    <th>Categoría</th>
    <th>Cilindraje</th>
    <th>Precio unitario</th>
    <th>Stock</th>
    <th>Valor total</th>
    <th>Estado</th>
    <th style="text-align: right;">Acciones</th>
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
          <img src="<?= base_url('img/motos/' . $p['imagen']) ?>" 
               alt="<?= e($p['marca']) ?>" class="product-thumb">
        </td>
        <td>
          <div class="product-info">
            <span class="brand"><?= e($p['marca']) ?></span>
            <span class="model"><?= e($p['modelo']) ?></span>
          </div>
        </td>
        <td><span class="category-badge"><?= e($p['categoria_nombre']) ?></span></td>
        <td><?= e($p['cilindraje']) ?></td>
        <td><strong style="color: var(--x-primary-dark);">$<?= number_format((float)$p['precio'], 0, ',', '.') ?></strong></td>
        <td>
          <?php
            $stockClass = '';
            if ((int)$p['stock'] === 0) $stockClass = 'out';
            elseif ((int)$p['stock'] < 5) $stockClass = 'low';
          ?>
          <span class="stock-badge <?= $stockClass ?>"><?= (int)$p['stock'] ?></span>
        </td>
        <td>
          <strong>$<?= number_format((float)$p['precio'] * (int)$p['stock'], 0, ',', '.') ?></strong>
        </td>
        <td>
          <span class="status-badge <?= e($p['estado']) ?>">
            <i class="bi bi-<?= $p['estado'] === 'activo' ? 'check-circle-fill' : 'x-circle-fill' ?>"></i>
            <?= e(ucfirst($p['estado'])) ?>
          </span>
        </td>
        <td>
          <div class="action-buttons">
            <a href="<?= base_url('dashboard/producto_form.php?id=' . $p['id']) ?>" 
               class="btn-action edit" title="Editar">
              <i class="bi bi-pencil"></i>
            </a>
            <a href="<?= base_url('dashboard/producto_guardar.php?accion=toggle&id=' . $p['id']) ?>" 
               class="btn-action toggle" 
               title="<?= $p['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>">
              <i class="bi bi-<?= $p['estado'] === 'activo' ? 'eye-slash' : 'eye' ?>"></i>
            </a>
            <a href="<?= base_url('dashboard/producto_guardar.php?accion=eliminar&id=' . $p['id']) ?>" 
               class="btn-action delete" 
               title="Eliminar"
               onclick="return confirm('¿Eliminar este producto?')">
              <i class="bi bi-trash"></i>
            </a>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
</tbody>
        <?php if (empty($productos)): ?>
          <tr>
            <td colspan="9" class="text-center py-5">
              <i class="bi bi-inbox fs-1 text-x-muted d-block mb-2"></i>
              <span class="text-x-muted">No se encontraron productos.</span>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($productos as $p): ?>
            <tr>
              <td>#<?= $p['id'] ?></td>
              <td>
                <img src="<?= base_url('img/motos/' . $p['imagen']) ?>" 
                     alt="<?= e($p['marca']) ?>" class="product-thumb">
              </td>
              <td>
                <div class="product-info">
                  <span class="brand"><?= e($p['marca']) ?></span>
                  <span class="model"><?= e($p['modelo']) ?></span>
                </div>
              </td>
              <td><span class="category-badge"><?= e($p['categoria_nombre']) ?></span></td>
              <td><?= e($p['cilindraje']) ?></td>
              <td><strong style="color: var(--x-primary-dark);">$<?= number_format((float)$p['precio'], 0, ',', '.') ?></strong></td>
              <td>
                <?php
                  $stockClass = '';
                  if ((int)$p['stock'] === 0) $stockClass = 'out';
                  elseif ((int)$p['stock'] < 5) $stockClass = 'low';
                ?>
                <span class="stock-badge <?= $stockClass ?>"><?= (int)$p['stock'] ?></span>
              </td>
              <td>
                <span class="status-badge <?= e($p['estado']) ?>">
                  <i class="bi bi-<?= $p['estado'] === 'activo' ? 'check-circle-fill' : 'x-circle-fill' ?>"></i>
                  <?= e(ucfirst($p['estado'])) ?>
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <a href="<?= base_url('dashboard/producto_form.php?id=' . $p['id']) ?>" 
                     class="btn-action edit" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="<?= base_url('dashboard/producto_guardar.php?accion=toggle&id=' . $p['id']) ?>" 
                     class="btn-action toggle" 
                     title="<?= $p['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>">
                    <i class="bi bi-<?= $p['estado'] === 'activo' ? 'eye-slash' : 'eye' ?>"></i>
                  </a>
                  <a href="<?= base_url('dashboard/producto_guardar.php?accion=eliminar&id=' . $p['id']) ?>" 
                     class="btn-action delete" 
                     title="Eliminar"
                     onclick="return confirm('¿Eliminar este producto?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>