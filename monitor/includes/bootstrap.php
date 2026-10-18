<?php
declare(strict_types=1);

// ============================================================
// Bootstrap del Monitor XMotos
// Constantes globales y carga inicial de dependencias
// ============================================================

// Ruta raíz del proyecto (donde está xmotos/)
define('MONITOR_BASE_PATH', dirname(__DIR__, 2));

// URL base del monitor (accesible desde el navegador)
define('MONITOR_URL', '/xmotos/monitor/public/');

// URL base del proyecto (para reutilizar xmotos.css)
define('APP_URL', '/xmotos/public/');

// Cargar configuración y conexiones del proyecto principal
require_once MONITOR_BASE_PATH . '/app/config/database.php';

// Cargar funciones auxiliares del monitor
require_once __DIR__ . '/funciones.php';

// Cargar el repositorio de datos
require_once __DIR__ . '/repositorio.php';  