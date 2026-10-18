<?php
/**
 * XMOTOS - Cerrar sesión
 */

declare(strict_types=1);

session_start();
session_destroy();

header('Location: /xmotos/public/');
exit;