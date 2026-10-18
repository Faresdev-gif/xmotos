<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Valores por defecto
$pageTitle   = $pageTitle   ?? 'Monitor';
$currentPage = $currentPage ?? '';
$pageSubtitle = $pageSubtitle ?? '';

// Info del usuario logueado
$usuarioNombre = $_SESSION['monitor_usuario'] ?? 'Usuario';
$usuarioRol    = $_SESSION['monitor_rol']     ?? 'observador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($pageTitle) ?> — Monitor XMotos</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= APP_URL ?>css/xmotos.css">
  <link rel="stylesheet" href="<?= MONITOR_URL ?>assets/css/monitor.css">
</head>
<body>

<div class="dashboard-wrapper">

  <!-- ========== SIDEBAR ========== -->
  <aside class="dashboard-sidebar" id="monitorSidebar">
    <div class="dashboard-brand">
      <a href="<?= MONITOR_URL ?>?route=dashboard">
        <i class="bi bi-speedometer2"></i>
        <span style="color: #F8FAFC; font-size: 1.15rem; font-weight: 900;">MONITOR</span>
        <span style="color: var(--x-primary); font-size: 0.75rem; font-weight: 700;">
          XMOTOS
        </span>
      </a>
    </div>

    <nav class="dashboard-nav">
      <a href="<?= MONITOR_URL ?>?route=dashboard"
         class="dashboard-nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>

      <a href="<?= MONITOR_URL ?>?route=auditoria"
         class="dashboard-nav-link <?= $currentPage === 'auditoria' ? 'active' : '' ?>">
        <i class="bi bi-journal-text"></i> Auditoría
      </a>

      <a href="<?= MONITOR_URL ?>?route=paridad"
         class="dashboard-nav-link <?= $currentPage === 'paridad' ? 'active' : '' ?>">
        <i class="bi bi-arrow-left-right"></i> Paridad
      </a>

      <?php if ($usuarioRol === 'admin'): ?>
        <a href="<?= MONITOR_URL ?>?route=usuarios"
           class="dashboard-nav-link <?= $currentPage === 'usuarios' ? 'active' : '' ?>">
          <i class="bi bi-people"></i> Usuarios
        </a>
      <?php endif; ?>

      <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">

      <a href="<?= APP_URL ?>" class="dashboard-nav-link" target="_blank">
        <i class="bi bi-box-arrow-up-right"></i> Ir al sitio
      </a>

      <a href="<?= MONITOR_URL ?>?route=logout" class="dashboard-nav-link text-danger">
        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
      </a>
    </nav>
  </aside>

  <!-- ========== CONTENIDO PRINCIPAL ========== -->
  <main class="dashboard-main">

    <header class="dashboard-topbar">
      <button class="btn btn-x-outline d-lg-none" id="btnToggleSidebar">
        <i class="bi bi-list"></i>
      </button>

      <h1 class="dashboard-title mb-0"><?= e($pageTitle) ?></h1>

      <?php if ($pageSubtitle): ?>
        <small class="text-x-muted ms-2 d-none d-md-inline">— <?= e($pageSubtitle) ?></small>
      <?php endif; ?>

      <div class="dashboard-user ms-auto">
        <i class="bi bi-person-circle"></i>
        <div>
          <div class="fw-bold"><?= e($usuarioNombre) ?></div>
          <small class="text-x-muted"><?= e($usuarioRol) ?></small>
        </div>
      </div>
    </header>

    <div class="dashboard-content">