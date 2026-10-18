<?php
declare(strict_types=1);

function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', $line, 2) + [1 => ''];
        $_ENV[trim($key)] = trim($value);
    }
}

loadEnv(__DIR__ . '/../../.env');

class Database {
    /** @var array<string, PDO> Cacheo de conexiones por nombre de BD */
    private static array $connections = [];

    /** @var resource|null Conexión Oracle */
    private static $oracleConnection = null;

    /**
     * Devuelve una conexión PDO. Si no se especifica $dbName, usa la del .env.
     */
    public static function getConnection(?string $dbName = null): PDO {
        $dbName = $dbName ?? ($_ENV['DB_NAME'] ?? 'xmotos');

        if (!isset(self::$connections[$dbName])) {
            
            $host    = $_ENV['DB_HOST'] ?? die('Error: DB_HOST no definido en .env');
            $port    = $_ENV['DB_PORT'] ?? die('Error: DB_PORT no definido en .env');
            $user    = $_ENV['DB_USER'] ?? die('Error: DB_USER no definido en .env');
            $pass    = $_ENV['DB_PASS'] ?? ''; // La contraseña puede estar vacía en local
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connections[$dbName] = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log("Error de conexión [$dbName]: " . $e->getMessage());
                die("No fue posible conectar con la base de datos.");
            }
        }
        return self::$connections[$dbName];
    }

    /**
     * Atajo: conexión al maestro (xmotos).
     */
    public static function getMaestro(): PDO {
        return self::getConnection($_ENV['DB_NAME'] ?? 'xmotos');
    }

    /**
     * Atajo: conexión a la réplica (xmotos_replica).
     */
    public static function getReplica(): PDO {
        return self::getConnection('xmotos_replica');
    }

    /**
     * Conexión Oracle (nodo distribuido).
     */
    public static function getOracleConnection() {
        if (self::$oracleConnection === null) {
            $host    = $_ENV['ORACLE_HOST'] ?? '127.0.0.1';
            $port    = $_ENV['ORACLE_PORT'] ?? '1521';
            $service = $_ENV['ORACLE_SERVICE'] ?? 'freepdb1.FALSE';
            $user    = $_ENV['ORACLE_USER'] ?? 'system';
            $pass    = $_ENV['ORACLE_PASS'] ?? 'Oracle2026*';

            $tns = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SERVICE_NAME=$service)))";

            if (function_exists('oci_connect')) {
                self::$oracleConnection = @oci_connect($user, $pass, $tns, 'AL32UTF8');
                if (!self::$oracleConnection) {
                    $m = oci_error();
                    error_log("Error conexión Oracle: " . ($m['message'] ?? 'Desconocido'));
                    die("Error al conectar con el nodo distribuido Oracle: " . ($m['message'] ?? ''));
                }
            } else {
                die("La extensión PHP OCI8 no está habilitada en XAMPP. Requiere activación en php.ini.");
            }
        }
        return self::$oracleConnection;
    }
}