<?php
/**
 * XMOTOS - Control de acceso por rol
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica que el usuario esté logueado y tenga uno de los roles permitidos.
 * Si no cumple, redirige al login.
 */
function requireRole(array $rolesPermitidos = ['administrador', 'vendedor']): void {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . base_url('login.php'));
        exit;
    }

    if (!in_array($_SESSION['rol'], $rolesPermitidos, true)) {
        header('Location: ' . base_url());
        exit;
    }
}

/**
 * Devuelve true si el usuario actual tiene el rol indicado.
 */
function hasRole(string $rol): bool {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === $rol;
}

/**
 * Devuelve true si el usuario está logueado.
 */
function isLoggedIn(): bool {
    return isset($_SESSION['usuario_id']);
}