<?php
/**
 * XMOTOS - Header del dashboard (admin y vendedor)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'Dashboard';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$esVendedor = ($_SESSION['rol'] ?? '') === 'vendedor';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle) ?> — XMOTOS <?= $esVendedor ? 'Vendedor' : 'Admin' ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= base_url('css/xmotos.css') ?>">
</head>
<body>

<div class="dashboard-wrapper">

  <!-- SIDEBAR -->
  <aside class="dashboard-sidebar" id="dashboardSidebar">
    <div class="dashboard-brand">
      <a href="<?= base_url('dashboard/index.php') ?>">
        <i class="bi bi-lightning-charge-fill"></i>
        <span style="color: #F8FAFC; font-size: 1.15rem; font-weight: 900;">XMOTOS</span>
        <span style="color: var(--x-primary); font-size: 0.75rem; font-weight: 700;">
          <?= $esVendedor ? 'VENDEDOR' : 'ADMIN' ?>
        </span>
      </a>
    </div>

    <nav class="dashboard-nav">
      <a href="<?= base_url('dashboard/index.php') ?>" 
         class="dashboard-nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>

      <?php if (!$esVendedor): ?>
        <a href="<?= base_url('dashboard/productos.php') ?>" 
           class="dashboard-nav-link <?= in_array($currentPage, ['productos', 'producto_form'], true) ? 'active' : '' ?>">
          <i class="bi bi-box-seam"></i> Productos
        </a>
      <?php endif; ?>

      <a href="<?= base_url('dashboard/inventario.php') ?>" 
         class="dashboard-nav-link <?= $currentPage === 'inventario' ? 'active' : '' ?>">
        <i class="bi bi-clipboard-data"></i> Inventario
      </a>

      <?php if (!$esVendedor): ?>
        <a href="<?= base_url('dashboard/compras.php') ?>" 
           class="dashboard-nav-link <?= in_array($currentPage, ['compras', 'compra_form'], true) ? 'active' : '' ?>">
          <i class="bi bi-bag-plus"></i> Compras
        </a>
      <?php endif; ?>

      <?php if (!$esVendedor): ?>
        <a href="<?= base_url('dashboard/usuarios.php') ?>" 
           class="dashboard-nav-link <?= in_array($currentPage, ['usuarios', 'usuario_form'], true) ? 'active' : '' ?>">
          <i class="bi bi-people"></i> Usuarios
        </a>
      <?php endif; ?>

      <a href="<?= base_url('dashboard/ventas.php') ?>" 
         class="dashboard-nav-link <?= $currentPage === 'ventas' ? 'active' : '' ?>">
        <i class="bi bi-cart-check"></i> Ventas
      </a>

      <a href="<?= base_url('dashboard/devoluciones.php') ?>" 
         class="dashboard-nav-link <?= in_array($currentPage, ['devoluciones', 'devolucion_form'], true) ? 'active' : '' ?>">
        <i class="bi bi-arrow-return-left"></i> Devoluciones
      </a>

      <a href="<?= base_url('dashboard/solicitudes.php') ?>" 
         class="dashboard-nav-link <?= in_array($currentPage, ['solicitudes', 'ver_solicitud'], true) ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-text"></i> Solicitudes
      </a>

      <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">

      <a href="<?= base_url() ?>" class="dashboard-nav-link">
        <i class="bi bi-house"></i> Ir al sitio
      </a>

      <a href="<?= base_url('logout.php') ?>" class="dashboard-nav-link text-danger">
        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
      </a>
    </nav>
  </aside>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="dashboard-main">

    <header class="dashboard-topbar">
      <button class="btn btn-x-outline d-lg-none" id="btnToggleSidebar">
        <i class="bi bi-list"></i>
      </button>

      <div class="dashboard-search d-none d-md-block">
        <i class="bi bi-search"></i>
        <input type="search" placeholder="Buscar...">
      </div>

      <div class="dashboard-user ms-auto">
        <i class="bi bi-person-circle"></i>
        <div>
          <div class="fw-bold"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></div>
          <small class="text-x-muted"><?= htmlspecialchars($_SESSION['rol'] ?? '') ?></small>
        </div>
      </div>
    </header>

    <div class="dashboard-content">