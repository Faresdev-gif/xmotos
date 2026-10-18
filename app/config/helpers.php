<?php
/**
 * XMOTOS - Funciones auxiliares
 */

declare(strict_types=1);

function base_url(string $path = ''): string {
    $base = '/xmotos/public';
    return $base . '/' . ltrim($path, '/');
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}