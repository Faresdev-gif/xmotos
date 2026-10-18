<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
require_once __DIR__ . '/../../app/config/database.php';

requireRole(['admin', 'vendedor', 'cliente']); // ajusta roles según tu lógica

$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['venta_id'])) {
    header('Location: ' . base_url('dashboard/ventas.php?error=invalid_request'));
    exit;
}

$ventaId = (int) $_POST['venta_id'];

try {
    $pdo->beginTransaction();
    error_log("=== Iniciando cancelación de venta #{$ventaId} ===");

    // Marca la venta como cancelada (si aplica en tu modelo)
    $upd = $pdo->prepare("UPDATE ventas SET estado = 'cancelada' WHERE id = :id");
    if (!$upd->execute([':id' => $ventaId])) {
        throw new Exception('Fallo al actualizar estado de la venta.');
    }
    error_log("Venta {$ventaId} marcada como cancelada.");

    // Recuperar detalles de venta para crear devoluciones (si aplica)
    $stmtDetalles = $pdo->prepare("SELECT * FROM venta_detalle WHERE venta_id = :vid");
    $stmtDetalles->execute([':vid' => $ventaId]);
    $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

    if (empty($detalles)) {
        error_log("Venta {$ventaId} no tiene detalles registrados.");
    }

    $ins = $pdo->prepare("
        INSERT INTO devoluciones
        (venta_detalle_id, motivo, cantidad, monto, estado, metodo_reembolso, observaciones, usuario_solicita)
        VALUES (:vdi, :motivo, :cantidad, :monto, 'aprobada', :metodo, :obs, :uid)
    ");

    foreach ($detalles as $d) {
        $params = [
            ':vdi' => $d['id'],
            ':motivo' => 'Cancelación de venta desde el dashboard',
            ':cantidad' => $d['cantidad'] ?? 1,
            ':monto' => $d['subtotal'] ?? 0,
            ':metodo' => 'efectivo',
            ':obs' => 'Generada automáticamente al cancelar la venta #' . $ventaId,
            ':uid' => $_SESSION['usuario_id'] ?? null
        ];

        if (!$ins->execute($params)) {
            $errInfo = $ins->errorInfo();
            throw new Exception('Fallo al insertar devolución: ' . json_encode($errInfo));
        }

        $lastId = $pdo->lastInsertId();
        error_log("Devolución insertada id={$lastId} para venta_detalle_id={$d['id']} (venta {$ventaId}).");
    }

    $pdo->commit();
    error_log("=== Cancelación completada para venta #{$ventaId} ===");

    header('Location: ' . base_url('dashboard/ventas.php?msg=Venta cancelada y devolución registrada'));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error al cancelar venta #{$ventaId}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
    header('Location: ' . base_url('dashboard/ventas.php?error=' . urlencode('Error al cancelar venta: ' . $e->getMessage())));
    exit;
}
