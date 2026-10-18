<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';

$pdo = Database::getConnection();
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$producto = [
    'id' => 0,
    'categoria_id' => 1,
    'marca' => '',
    'modelo' => '',
    'cilindraje' => '',
    'precio' => '',
    'imagen' => '',
    'stock' => 1,
    'estado' => 'activo',
];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch();
    if ($data) {
        $producto = $data;
    }
}

$pageTitle = $id > 0 ? 'Editar producto' : 'Nuevo producto';
require_once __DIR__ . '/../../app/views/layouts/dashboard_header.php';
?>

<div class="mb-4">
  <a href="<?= base_url('dashboard/productos.php') ?>" class="text-x-muted">
    <i class="bi bi-arrow-left me-2"></i> Volver al listado
  </a>
</div>

<div class="x-card p-4">
  <h2 class="h5 mb-4">
    <?= $id > 0 ? 'Editar producto #' . $id : 'Nuevo producto' ?>
  </h2>

  <form action="<?= base_url('dashboard/producto_guardar.php') ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= (int)$producto['id'] ?>">

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Categoría *</label>
        <select name="categoria_id" class="form-select" required>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>" 
                    <?= $cat['id'] == $producto['categoria_id'] ? 'selected' : '' ?>>
              <?= e($cat['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Marca *</label>
        <input type="text" name="marca" class="form-control" 
               value="<?= e($producto['marca']) ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Modelo *</label>
        <input type="text" name="modelo" class="form-control" 
               value="<?= e($producto['modelo']) ?>" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Cilindraje</label>
        <input type="text" name="cilindraje" class="form-control" 
               value="<?= e($producto['cilindraje']) ?>" placeholder="124 cc">
      </div>

      <div class="col-md-3">
        <label class="form-label">Precio (COP) *</label>
        <input type="number" name="precio" class="form-control" 
               value="<?= e((string)$producto['precio']) ?>" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Stock</label>
        <input type="number" name="stock" class="form-control" 
               value="<?= (int)$producto['stock'] ?>" min="0">
      </div>

      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <option value="activo" <?= $producto['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
          <option value="inactivo" <?= $producto['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Imagen</label>
        <input type="file" name="imagen" class="form-control" accept="image/*">
        <?php if (!empty($producto['imagen'])): ?>
          <div class="mt-2">
            <small class="text-x-muted">Imagen actual:</small><br>
            <img src="<?= base_url('img/motos/' . $producto['imagen']) ?>" 
                 style="max-width: 200px; border-radius: 8px;">
            <input type="hidden" name="imagen_actual" value="<?= e($producto['imagen']) ?>">
          </div>
        <?php endif; ?>
      </div>

      <div class="col-12 d-flex gap-2 justify-content-end mt-3">
        <a href="<?= base_url('dashboard/productos.php') ?>" class="btn btn-x-outline">Cancelar</a>
        <button type="submit" class="btn btn-x-primary">
          <i class="bi bi-check-circle me-2"></i> Guardar
        </button>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/dashboard_footer.php'; ?>