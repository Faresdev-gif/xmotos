<?php
/**
 * XMOTOS - Verificación de correo
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/config/helpers.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

(new AuthController())->verificar();