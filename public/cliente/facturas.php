<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
require_once __DIR__ . '/../../app/config/database.php';

requireRole(['cliente']);

$pdo = Database::getConnection();

$stmt = $pdo->prepare("
    SELECT 
        v.*,
        GROUP_CONCAT(CONCAT(p.marca, ' ', p.modelo) SEPARATOR ', ') AS productos
    FROM ventas v
    LEFT JOIN venta_detalle vd ON vd.venta_id = v.id
    LEFT JOIN productos p ON vd.producto_id = p.id
    WHERE v.usuario_id = :uid
    GROUP BY v.id
    ORDER BY v.fecha_registro DESC
");
$stmt->execute([':uid' => $_SESSION['usuario_id']]);
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Mis facturas';
require_once __DIR__ . '/../../app/views/layouts/cliente_header.php';
?>

<h2 class="h4 mb-4">Mis facturas</h2>

<?php if (empty($facturas)): ?>
  <div class="x-card p-5 text-center">
    <i class="bi bi-receipt fs-1 text-x-muted"></i>
    <p class="text-x-muted mt-3 mb-3">Aún no tienes facturas.</p>
    <a href="<?php echo base_url('productos.php'); ?>" class="btn btn-x-primary">Ver catálogo</a>
  </div>
<?php else: ?>
  <div class="dashboard-table">
    <div class="table-header">
      <i class="bi bi-receipt me-2" style="color:var(--x-primary)"></i> Mis compras
      <span class="count"><?php echo count($facturas); ?></span>
    </div>

    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th># Factura</th>
            <th>Productos</th>
            <th>Subtotal</th>
            <th>IVA</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($facturas as $f): ?>
            <tr>
              <td><strong><?php echo e($f['numero_factura']); ?></strong></td>
              <td><?php echo e($f['productos'] ?? '—'); ?></td>
              <td><?php echo number_format((float)$f['subtotal'], 0, ',', '.'); ?></td>
              <td class="text-x-primary"><?php echo number_format((float)$f['iva'], 0, ',', '.'); ?></td>
              <td><?php echo number_format((float)$f['total'], 0, ',', '.'); ?></td>
              <td><span class="status-badge <?php echo $f['estado'] === 'pagada' ? 'activo' : 'inactivo'; ?>"><?php echo ucfirst($f['estado']); ?></span></td>
              <td><?php echo date('d/m/Y H:i', strtotime($f['fecha_registro'])); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../app/views/layouts/cliente_footer.php'; ?>
