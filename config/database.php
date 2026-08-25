<?php
/**
 * config/database.php
 * -----------------------------------------------------------------------------
 * Módulo de Configuración y Conexión a la Base de Datos MySQL (PDO)
 * -----------------------------------------------------------------------------
 * Descripción:
 * Provee la conexión centralizada a la base de datos MySQL implementando el
 * patrón de diseño Singleton. Esto garantiza que exista una única instancia
 * de conexión PDO compartida durante todo el ciclo de vida de la petición HTTP,
 * optimizando el uso de recursos del servidor.
 *
 * Contenido:
 * - Constantes de configuración de la base de datos (Host, Puerto, Nombre, Usuario, Password, Charset).
 * - Clase Database con método estático getConnection() que retorna la instancia PDO.
 * - Manejo robusto de excepciones (PDOException) con respuesta de error estructurada en formato JSON.
 * -----------------------------------------------------------------------------
 */

// Parámetros de conexión a la base de datos MySQL
define('DB_HOST',    '127.0.0.1');  // Dirección IP local (recomendada para evitar sobrecarga de socket en PHP CLI)
define('DB_PORT',    '3306');       // Puerto por defecto de MySQL Server
define('DB_NAME',    'api_sena');   // Nombre de la base de datos del proyecto
define('DB_USER',    'root');       // Usuario con privilegios en la base de datos
define('DB_PASS',    '');           // Contraseña del usuario MySQL
define('DB_CHARSET', 'utf8mb4');    // Codificación para soporte completo de caracteres y emojis

/**
 * Clase Database
 * Implementa el patrón Singleton para la administración de la conexión PDO.
 */
class Database
{
    /**
     * Instancia única de la conexión PDO.
     * @var PDO|null
     */
    private static ?PDO $connection = null;

    /**
     * Constructor privado para evitar la instanciación directa.
     */
    private function __construct() {}

    /**
     * Obtiene la conexión activa a la base de datos MySQL.
     *
     * Si la conexión no existe, la crea con las configuraciones de seguridad
     * y manejo de errores adecuadas (excepciones activadas, emulación de prepares desactivada).
     *
     * @return PDO Instancia activa de conexión a MySQL.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Data Source Name (DSN) para MySQL
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );

            try {
                // Instanciación del objeto PDO con directivas de seguridad y rendimiento
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones ante fallos SQL
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Retorna arreglos asociativos limpios
                    PDO::ATTR_EMULATE_PREPARES   => false,                  // Uso de consultas preparadas nativas (evita inyecciones SQL)
                ]);
            } catch (PDOException $e) {
                // En caso de fallo crítico en la base de datos, retornar error 500 en formato JSON
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'No se pudo conectar a la base de datos.',
                    'detail'  => $e->getMessage(),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        return self::$connection;
    }
}

