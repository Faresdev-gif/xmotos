<?php
declare(strict_types=1);

// ============================================================
// Repositorio del Monitor
// Funciones que consultan la BD y devuelven datos limpios
// Patrón: Repository
// ============================================================

/**
 * Devuelve el conteo de registros de cada tabla replicada
 * en el maestro Y en la réplica.
 *
 * @return array<int, array{tabla:string, maestro:int, replica:int, ok:bool}>
 */
function obtenerParidad(): array
{
    $pdoMaestro = Database::getMaestro();
    $pdoReplica = Database::getReplica();

    // Definición de pares: [tabla_maestra => tabla_replica]
    $pares = [
        'usuarios'          => 'usuarios_replica',
        'productos'         => 'productos_replica',
        'ventas'            => 'ventas_replica',
        'venta_detalle'     => 'venta_detalle_replica',
        'solicitudes'       => 'solicitudes_replica',
        'compras'           => 'compras_replica',
        'compras_detalle'   => 'compras_detalle_replica',
    ];

    $resultado = [];

    foreach ($pares as $tablaMaestra => $tablaReplica) {
        $totalMaestro = (int) $pdoMaestro
            ->query("SELECT COUNT(*) FROM `$tablaMaestra`")
            ->fetchColumn();

        $totalReplica = (int) $pdoReplica
            ->query("SELECT COUNT(*) FROM `$tablaReplica`")
            ->fetchColumn();

        $resultado[] = [
            'tabla'   => $tablaMaestra,
            'maestro' => $totalMaestro,
            'replica' => $totalReplica,
            'ok'      => $totalMaestro === $totalReplica,
        ];
    }

    return $resultado;
}

/**
 * Devuelve las últimas N operaciones del replica_log.
 *
 * @param int $limite Número máximo de operaciones a devolver
 * @return array<int, array>
 */
function obtenerUltimasOperaciones(int $limite = 20): array
{
    $pdo = Database::getReplica();

    $sql = "SELECT id, tabla_origen, operacion, registro_id,
                   datos, usuario_bd, fecha_replicacion
            FROM replica_log
            ORDER BY id DESC
            LIMIT :limite";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Devuelve métricas resumidas del log.
 *
 * @return array{total:int, insert:int, update:int, delete:int, seed:int}
 */
function obtenerMetricas(): array
{
    $pdo = Database::getReplica();

    $sql = "SELECT
                COUNT(*) AS total,
                SUM(operacion = 'INSERT') AS insert_count,
                SUM(operacion = 'UPDATE') AS update_count,
                SUM(operacion = 'DELETE') AS delete_count
            FROM replica_log";

    $row = $pdo->query($sql)->fetch();

    return [
        'total'  => (int) $row['total'],
        'insert' => (int) $row['insert_count'],
        'update' => (int) $row['update_count'],
        'delete' => (int) $row['delete_count'],
    ];
}

/**
 * Cuenta operaciones en las últimas 24 horas.
 */
function obtenerOperacionesUltimas24h(): int
{
    $pdo = Database::getReplica();
    $sql = "SELECT COUNT(*) FROM replica_log
            WHERE fecha_replicacion >= NOW() - INTERVAL 24 HOUR";
    return (int) $pdo->query($sql)->fetchColumn();
}

/**
 * Cuenta operaciones por hora en las últimas 24 horas.
 * Devuelve un array de 24 elementos, uno por hora.
 *
 * @return array<int, int>
 */
function obtenerOperacionesPorHora(): array
{
    $pdo = Database::getReplica();

    $sql = "SELECT HOUR(fecha_replicacion) AS hora, COUNT(*) AS total
            FROM replica_log
            WHERE fecha_replicacion >= NOW() - INTERVAL 24 HOUR
            GROUP BY HOUR(fecha_replicacion)";

    $datos = $pdo->query($sql)->fetchAll();

    // Inicializar todas las horas a 0
    $porHora = array_fill(0, 24, 0);

    foreach ($datos as $fila) {
        $porHora[(int)$fila['hora']] = (int)$fila['total'];
    }

    return $porHora;
}
/**
 * Devuelve operaciones del log con filtros y paginación.
 *
 * @param array $filtros ['tabla'=>'', 'operacion'=>'', 'desde'=>'', 'hasta'=>'', 'buscar'=>'']
 * @param int $pagina Número de página (1-based)
 * @param int $porPagina Registros por página
 * @return array{datos:array, total:int, paginas:int}
 */
function buscarOperaciones(array $filtros, int $pagina = 1, int $porPagina = 25): array
{
    $pdo = Database::getReplica();

    // Construir WHERE dinámico con placeholders
    $condiciones = [];
    $params = [];

    if (!empty($filtros['tabla'])) {
        $condiciones[] = 'tabla_origen = :tabla';
        $params[':tabla'] = $filtros['tabla'];
    }

    if (!empty($filtros['operacion'])) {
        $condiciones[] = 'operacion = :operacion';
        $params[':operacion'] = $filtros['operacion'];
    }

    if (!empty($filtros['desde'])) {
        $condiciones[] = 'fecha_replicacion >= :desde';
        $params[':desde'] = $filtros['desde'] . ' 00:00:00';
    }

    if (!empty($filtros['hasta'])) {
        $condiciones[] = 'fecha_replicacion <= :hasta';
        $params[':hasta'] = $filtros['hasta'] . ' 23:59:59';
    }

    if (!empty($filtros['buscar'])) {
        $condiciones[] = 'datos LIKE :buscar';
        $params[':buscar'] = '%' . $filtros['buscar'] . '%';
    }

    $where = '';
    if ($condiciones) {
        $where = 'WHERE ' . implode(' AND ', $condiciones);
    }

    // 1) Contar total de resultados (para paginación)
    $sqlCount = "SELECT COUNT(*) FROM replica_log $where";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $total = (int) $stmtCount->fetchColumn();

    // 2) Calcular offset
    $pagina = max(1, $pagina);
    $offset = ($pagina - 1) * $porPagina;
    $paginas = max(1, (int) ceil($total / $porPagina));

    // 3) Traer los datos paginados
    $sql = "SELECT id, tabla_origen, operacion, registro_id,
                   datos, usuario_bd, fecha_replicacion
            FROM replica_log
            $where
            ORDER BY id DESC
            LIMIT :limite OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'datos'   => $stmt->fetchAll(),
        'total'   => $total,
        'paginas' => $paginas,
    ];
}

/**
 * Devuelve la lista de tablas únicas que aparecen en el log.
 * Útil para el combo de filtro.
 */
function obtenerTablasDelLog(): array
{
    $pdo = Database::getReplica();
    $sql = "SELECT DISTINCT tabla_origen FROM replica_log
            WHERE tabla_origen <> ''
            ORDER BY tabla_origen";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
}
/**
 * Lista todos los usuarios del monitor.
 */
function listarUsuariosMonitor(): array
{
    $pdo = Database::getReplica();
    $sql = "SELECT id, nombre_completo, email, rol, estado,
                   ultimo_login, intentos_fallidos, bloqueado_hasta,
                   fecha_registro
            FROM monitor_usuarios
            ORDER BY id DESC";
    return $pdo->query($sql)->fetchAll();
}

/**
 * Crea un usuario del monitor.
 * Devuelve el id insertado o lanza excepción.
 */
function crearUsuarioMonitor(
    string $nombre,
    string $email,
    string $password,
    string $rol = 'observador'
): int {
    $pdo = Database::getReplica();
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        "INSERT INTO monitor_usuarios
            (nombre_completo, email, password_hash, rol, estado)
         VALUES
            (:nombre, :email, :hash, :rol, 'activo')"
    );
    $stmt->execute([
        ':nombre' => $nombre,
        ':email'  => $email,
        ':hash'   => $hash,
        ':rol'    => $rol,
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Actualiza rol y estado de un usuario del monitor.
 */
function actualizarUsuarioMonitor(int $id, string $rol, string $estado): void
{
    $pdo = Database::getReplica();
    $stmt = $pdo->prepare(
        "UPDATE monitor_usuarios
         SET rol = :rol, estado = :estado
         WHERE id = :id"
    );
    $stmt->execute([
        ':rol'    => $rol,
        ':estado' => $estado,
        ':id'     => $id,
    ]);
}

/**
 * Desbloquea un usuario (reinicia intentos fallidos).
 */
function desbloquearUsuarioMonitor(int $id): void
{
    $pdo = Database::getReplica();
    $stmt = $pdo->prepare(
        "UPDATE monitor_usuarios
         SET intentos_fallidos = 0, bloqueado_hasta = NULL
         WHERE id = :id"
    );
    $stmt->execute([':id' => $id]);
}