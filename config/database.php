<?php
/**
 * config/database.php
 * Configuracion de conexion a la base de datos MySQL via PDO.
 *
 * Entorno: Google Cloud Workstation con MySQL 8 instalado localmente.
 * Puerto : 3306 | Usuario: root | Password: (vacio)
 */

define('DB_HOST',    '127.0.0.1');  // usar IP en vez de socket para PHP CLI server
define('DB_PORT',    '3306');
define('DB_NAME',    'api_sena');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );

            try {
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'No se pudo conectar a la base de datos.',
                    'detail'  => $e->getMessage(),
                ]);
                exit;
            }
        }

        return self::$connection;
    }
}
