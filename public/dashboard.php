<?php
/**
 * XMOTOS - Redirección al dashboard nuevo
 *
 * Este archivo existía como placeholder. Ahora redirige a /dashboard/index.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/config/helpers.php';

header('Location: ' . base_url('dashboard/index.php'));
exit;