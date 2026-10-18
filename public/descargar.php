<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/helpers.php';

if (!isset($_SESSION['usuario_id'])) {
    die('No autorizado.');
}

$file = $_GET['file'] ?? '';
if ($file === '') {
    die('Archivo no especificado.');
}

// Seguridad: solo permitir archivos dentro de uploads/
$file = str_replace(['..', '\\'], '', $file);
if (strpos($file, 'uploads/') !== 0) {
    die('Acceso denegado.');
}

$ruta = __DIR__ . '/../' . $file;

if (!file_exists($ruta)) {
    die('Archivo no encontrado.');
}

$ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
$tipos = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];

$mime = $tipos[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($ruta) . '"');
header('Content-Length: ' . filesize($ruta));

readfile($ruta);
exit;