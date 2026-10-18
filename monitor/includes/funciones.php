<?php
declare(strict_types=1);

// ============================================================
// Funciones auxiliares del Monitor
// ============================================================

/**
 * Escapa texto para HTML (previene XSS)
 */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Registra un intento de acceso en la tabla de auditoría
 */
function registrarAcceso(
    ?int $usuarioId,
    string $emailIntentado,
    string $accion
): void {
    try {
        $pdo = Database::getReplica();
        $sql = "INSERT INTO monitor_auditoria_accesos
                  (usuario_id, email_intentado, accion, ip, user_agent)
                VALUES
                  (:usuario_id, :email, :accion, :ip, :user_agent)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':email'      => $emailIntentado,
            ':accion'     => $accion,
            ':ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    } catch (PDOException $e) {
        // La auditoría no debe romper el flujo principal
        error_log('Error en auditoría de accesos: ' . $e->getMessage());
    }
}

/**
 * Formatea una fecha para mostrarla en la UI
 */
function formatearFecha(?string $fecha): string
{
    if (!$fecha) return '—';
    return date('d/m/Y H:i:s', strtotime($fecha));
}

/**
 * Formatea un número como moneda colombiana
 */
function formatearMoneda($valor): string
{
    return '$' . number_format((float)$valor, 0, ',', '.');
}

/**
 * Redirige a una ruta del monitor
 */
function redirigir(string $ruta): void
{
    header('Location: ' . MONITOR_URL . ltrim($ruta, '/'));
    exit;
}