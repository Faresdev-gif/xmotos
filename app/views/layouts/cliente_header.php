<?php
/**
 * XMOTOS - Header del dashboard del cliente
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/database.php';

// Verificar que sea cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: ' . base_url('login.php?rol=cliente'));
    exit;
}

$pageTitle = $pageTitle ?? 'Mi cuenta';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Contar facturas del cliente
$pdoHeader = Database::getConnection();
$stmtHeader = $pdoHeader->prepare("SELECT COUNT(*) FROM ventas WHERE usuario_id = :uid");
$stmtHeader->execute([':uid' => $_SESSION['usuario_id']]);
$totalFacturas = (int)$stmtHeader->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle) ?> — XMOTOS</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/xmotos.css') ?>">
</head>
<body>

<div class="dashboard-wrapper">

  <aside class="dashboard-sidebar" id="dashboardSidebar">
    <div class="dashboard-brand">
      <a href="<?= base_url('cliente/index.php') ?>">
        <i class="bi bi-lightning-charge-fill"></i>
        <span style="color: #F8FAFC; font-size: 1.15rem; font-weight: 900;">XMOTOS</span>
        <span style="color: var(--x-primary); font-size: 0.75rem; font-weight: 700;">CLIENTE</span>
      </a>
    </div>

    <nav class="dashboard-nav">
      <a href="<?= base_url('cliente/index.php') ?>" 
         class="dashboard-nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Mi panel
      </a>

      <a href="<?= base_url('productos.php') ?>" class="dashboard-nav-link">
        <i class="bi bi-box-seam"></i> Catálogo
      </a>

      <a href="<?= base_url('cliente/favoritos.php') ?>" 
         class="dashboard-nav-link <?= $currentPage === 'favoritos' ? 'active' : '' ?>">
        <i class="bi bi-heart"></i> Mis favoritos
      </a>

      <a href="<?= base_url('cliente/pagos.php') ?>" 
         class="dashboard-nav-link <?= $currentPage === 'pagos' ? 'active' : '' ?>">
        <i class="bi bi-credit-card"></i> Comprar moto
      </a>

      <a href="<?= base_url('cliente/financiamiento.php') ?>" 
         class="dashboard-nav-link <?= $currentPage === 'financiamiento' ? 'active' : '' ?>">
        <i class="bi bi-cash-coin"></i> Financiamiento
      </a>
      <a href="<?= base_url('cliente/mis_solicitudes.php') ?>" class="dashboard-nav-link">
  <i class="bi bi-file-earmark-text"></i> Mis solicitudes
</a>

      <?php if ($totalFacturas > 0): ?>
        <a href="<?= base_url('cliente/facturas.php') ?>" 
           class="dashboard-nav-link <?= $currentPage === 'facturas' ? 'active' : '' ?>">
          <i class="bi bi-receipt"></i> Mis facturas
          <span class="badge bg-primary ms-auto"><?= $totalFacturas ?></span>
        </a>
      <?php endif; ?>

      <a href="<?= base_url('cliente/perfil.php') ?>" 
         class="dashboard-nav-link <?= $currentPage === 'perfil' ? 'active' : '' ?>">
        <i class="bi bi-person"></i> Mi perfil
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

  <main class="dashboard-main">

    <header class="dashboard-topbar">
      <button class="btn btn-x-outline d-lg-none" id="btnToggleSidebar">
        <i class="bi bi-list"></i>
      </button>

      <div class="dashboard-search d-none d-md-block">
        <i class="bi bi-search"></i>
        <input type="search" placeholder="Buscar productos...">
      </div>

      <div class="dashboard-user ms-auto">
        <i class="bi bi-person-circle"></i>
        <div>
          <div class="fw-bold"><?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></div>
          <small class="text-x-muted">Cliente</small>
        </div>
      </div>
    </header>

    <div class="dashboard-content">