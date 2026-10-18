<?php
/**
 * XMOTOS - Procesar solicitud de financiamiento (área del cliente)
 *
 * Recibe el POST del formulario del cliente y guarda la solicitud.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/database.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// =====================================================
// CAPTURAR DATOS
// =====================================================
$producto_id     = (int)($_POST['producto_id'] ?? 0);
$monto           = (float)($_POST['monto'] ?? 0);
$plazo           = (int)($_POST['plazo'] ?? 36);
$cuotaInicialPct = (float)($_POST['cuota_inicial_pct'] ?? 20);
$cuotaMensual    = (float)($_POST['cuota_mensual'] ?? 0);

$datos = [
    'usuario_id'       => $_SESSION['usuario_id'],
    'producto_id'      => $producto_id > 0 ? $producto_id : null,
    'tipo_solicitud'   => $_POST['tipo_solicitud'] ?? 'financiamiento',
    'monto_solicitado' => $monto,
    'plazo_meses'      => $plazo,
    'cuota_inicial_pct' => $cuotaInicialPct,
    'cuota_mensual_estimada' => $cuotaMensual,
    'nombre'           => trim($_POST['nombre'] ?? ''),
    'apellidos'        => trim($_POST['apellidos'] ?? ''),
    'tipo_doc'         => trim($_POST['tipo_doc'] ?? 'CC'),
    'cedula'           => trim($_POST['cedula'] ?? ''),
    'fecha_nac'        => trim($_POST['fecha_nac'] ?? ''),
    'correo'           => trim($_POST['correo'] ?? ''),
    'telefono'         => trim($_POST['telefono'] ?? ''),
    'departamento'     => trim($_POST['departamento'] ?? ''),
    'ciudad'           => trim($_POST['ciudad'] ?? ''),
    'direccion'        => trim($_POST['direccion'] ?? ''),
];

// =====================================================
// VALIDACIONES
// =====================================================
$errores = [];
foreach ($datos as $campo => $valor) {
    if (in_array($campo, ['usuario_id', 'producto_id', 'monto_solicitado', 'plazo_meses', 'cuota_inicial_pct', 'cuota_mensual_estimada'], true)) continue;
    if ($valor === '') $errores[] = "El campo $campo es obligatorio.";
}

if ($producto_id <= 0) $errores[] = 'Debes seleccionar una moto.';
if ($monto <= 0) $errores[] = 'El monto debe ser mayor a 0.';
if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha_nac'])) $errores[] = 'La fecha de nacimiento no es válida.';
if (!preg_match('/^\d{6,15}$/', $datos['cedula'])) $errores[] = 'El número de documento no es válido.';

if (!empty($errores)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
    exit;
}

// Términos
if (!isset($_POST['terminos']) || $_POST['terminos'] !== 'on') {
    echo json_encode(['success' => false, 'message' => 'Debes aceptar los términos y condiciones.']);
    exit;
}

// =====================================================
// SUBIR ARCHIVOS
// =====================================================
$uploadDir = __DIR__ . '/../../uploads/documentos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$archivosSubidos = [];
$archivos = [
    'doc_cedula'   => 'cedula',
    'doc_ingresos' => 'ingresos',
];

foreach ($archivos as $input => $tipo) {
    if (!isset($_FILES[$input]) || $_FILES[$input]['error'] !== UPLOAD_ERR_OK) {
        continue;
    }

    $file = $_FILES[$input];

    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => "El archivo $tipo supera los 5 MB."]);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
        echo json_encode(['success' => false, 'message' => "El archivo $tipo debe ser PDF, JPG o PNG."]);
        exit;
    }

    $nombreArchivo = $tipo . '_' . $_SESSION['usuario_id'] . '_' . time() . '.' . $ext;
    $rutaDestino = $uploadDir . $nombreArchivo;

    if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        $archivosSubidos[$tipo] = 'uploads/documentos/' . $nombreArchivo;
    }
}

if (!isset($archivosSubidos['cedula']) || !isset($archivosSubidos['ingresos'])) {
    echo json_encode(['success' => false, 'message' => 'Debes adjuntar la cédula y el certificado de ingresos.']);
    exit;
}

// =====================================================
// INSERTAR EN BD
// =====================================================
try {
    $pdo = Database::getConnection();
    $sql = "INSERT INTO solicitudes 
            (usuario_id, producto_id, tipo_solicitud, monto_solicitado, plazo_meses, 
             cuota_inicial_pct, cuota_mensual_estimada, nombre, apellidos, tipo_doc, 
             cedula, fecha_nac, correo, telefono, departamento, ciudad, direccion, 
             archivo_cedula, archivo_ingresos, estado, fecha_registro)
            VALUES 
            (:usuario_id, :producto_id, :tipo_solicitud, :monto_solicitado, :plazo_meses,
             :cuota_inicial_pct, :cuota_mensual_estimada, :nombre, :apellidos, :tipo_doc,
             :cedula, :fecha_nac, :correo, :telefono, :departamento, :ciudad, :direccion,
             :archivo_cedula, :archivo_ingresos, 'nuevo', NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuario_id'      => $datos['usuario_id'],
        ':producto_id'     => $datos['producto_id'],
        ':tipo_solicitud'  => $datos['tipo_solicitud'],
        ':monto_solicitado'=> $datos['monto_solicitado'],
        ':plazo_meses'     => $datos['plazo_meses'],
        ':cuota_inicial_pct' => $datos['cuota_inicial_pct'],
        ':cuota_mensual_estimada' => $datos['cuota_mensual_estimada'],
        ':nombre'          => $datos['nombre'],
        ':apellidos'       => $datos['apellidos'],
        ':tipo_doc'        => $datos['tipo_doc'],
        ':cedula'          => $datos['cedula'],
        ':fecha_nac'       => $datos['fecha_nac'],
        ':correo'          => $datos['correo'],
        ':telefono'        => $datos['telefono'],
        ':departamento'    => $datos['departamento'],
        ':ciudad'          => $datos['ciudad'],
        ':direccion'       => $datos['direccion'],
        ':archivo_cedula'  => $archivosSubidos['cedula'],
        ':archivo_ingresos'=> $archivosSubidos['ingresos'],
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Solicitud recibida. En un momento será evaluada.'
    ]);

} catch (PDOException $e) {
    error_log("Error al insertar solicitud: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al guardar la solicitud. Intenta de nuevo.']);
}