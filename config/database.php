<?php
/**
 * config/database.php
 * Configuracion de conexion a la base de datos MySQL via PDO.
 *
 * Credenciales predeterminadas de XAMPP:
 *   Host   : localhost
 *   Puerto : 3306
 *   Usuario: root
 *   Pass   : (vacio por defecto)
 *
 * Cambia DB_NAME por el nombre de tu base de datos creada en phpMyAdmin.
 */

define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'api_sena');   // <- nombre de la BD que crearas en phpMyAdmin
define('DB_USER',    'root');       // usuario por defecto en XAMPP
define('DB_PASS',    '');           // contrasena vacia por defecto en XAMPP
define('DB_CHARSET', 'utf8mb4');

class Database
{
    private static ?PDO $connection = null;

    /**
     * Retorna una instancia singleton de la conexion PDO.
     * Lanza excepcion si no puede conectar.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
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
                    'hint'    => 'Verifica que XAMPP este corriendo y que la BD "' . DB_NAME . '" exista en phpMyAdmin.',
                ]);
                exit;
            }
        }

        return self::$connection;
    }
}
