<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'XMOTOS';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
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
  <nav class="navbar navbar-expand-lg x-navbar sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="<?= base_url() ?>">
        <i class="bi bi-lightning-charge-fill me-1"></i> XMOTOS
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMain">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="<?= base_url() ?>">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('productos.php') ?>">Productos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url() ?>#nosotros">Nosotros</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url() ?>#contacto">Contáctenos</a>
          </li>

          <?php if (isset($_SESSION['usuario_id'])): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i> <?= e($_SESSION['nombre']) ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <?php if ($_SESSION['rol'] === 'cliente'): ?>
                  <li><a class="dropdown-item" href="<?= base_url('cliente/index.php') ?>"><i class="bi bi-speedometer2 me-2"></i> Mi panel</a></li>
                <?php else: ?>
                  <li><a class="dropdown-item" href="<?= base_url('dashboard/index.php') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= base_url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i> Iniciar sesión
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= base_url('registro.php') ?>"><i class="bi bi-person-plus me-2"></i> Registrarse</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><h6 class="dropdown-header">Acceder como</h6></li>
                <li><a class="dropdown-item" href="<?= base_url('login.php?rol=cliente') ?>"><i class="bi bi-person me-2"></i> Cliente</a></li>
                <li><a class="dropdown-item" href="<?= base_url('login.php?rol=vendedor') ?>"><i class="bi bi-briefcase me-2"></i> Vendedor</a></li>
                <li><a class="dropdown-item" href="<?= base_url('login.php?rol=administrador') ?>"><i class="bi bi-shield-lock me-2"></i> Administrador</a></li>
              </ul>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>