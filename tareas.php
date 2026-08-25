<?php
/**
 * tareas.php
 * -----------------------------------------------------------------------------
 * Módulo de Gestión de Tareas - Servicio Web RESTful (CRUD)
 * -----------------------------------------------------------------------------
 * Descripción:
 * Provee la interfaz RESTful para la gestión completa de tareas (Creación,
 * Lectura individual y masiva, Actualización y Eliminación). Procesa diferentes
 * verbos HTTP sobre el mismo recurso (`/tareas.php`), gestionando parámetros
 * por query string (`?id=N`) o en el cuerpo JSON.
 *
 * Métodos HTTP y Acciones soportadas:
 *   - GET    /tareas.php          -> Obtener listado de todas las tareas ordenadas cronológicamente.
 *   - GET    /tareas.php?id=N     -> Obtener detalle de una tarea específica por su ID.
 *   - POST   /tareas.php          -> Crear una nueva tarea con validación de título y estado.
 *   - PUT    /tareas.php?id=N     -> Actualizar la información de una tarea existente.
 *   - DELETE /tareas.php?id=N     -> Eliminar definitivamente una tarea del sistema.
 *   - OPTIONS                     -> Manejo de preflight CORS.
 *
 * Contenido:
 * - Configuración de encabezados HTTP (CORS, verbos admitidos y Content-Type JSON).
 * - Funciones de utilidad (helpers) para extracción y validación de payloads JSON.
 * - Enrutamiento y control de lógica por método HTTP (GET, POST, PUT, DELETE).
 * - Manejo exhaustivo de códigos de estado HTTP (200, 201, 400, 404, 405, 422).
 * - Consultas preparadas con PDO para prevenir vulnerabilidades de Inyección SQL.
 * -----------------------------------------------------------------------------
 */

// Cabeceras HTTP para permitir consumo CORS y definir formato JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder a peticiones preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Incluir módulo de conexión a la base de datos
require_once __DIR__ . '/config/database.php';

$pdo    = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Extraer ID del recurso desde la URL si está presente (?id=X)
$id = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];
}

// ─── Funciones de Utilidad (Helpers) ─────────────────────────────────────────

/**
 * Obtiene y decodifica el cuerpo de la petición en formato JSON.
 *
 * @return array Arreglo asociativo con los datos recibidos en el payload JSON.
 */
function getBody(): array
{
    $raw = file_get_contents('php://input');
    if (trim($raw) === '') {
        return [];
    }
    $body = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400); // 400 Bad Request
        echo json_encode([
            'status'  => 'error',
            'message' => 'El cuerpo de la petición contiene un JSON inválido o mal formado.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    return $body ?? [];
}

/**
 * Valida la existencia y no vacuidad de una lista de campos obligatorios.
 *
 * @param array $data Arreglo asociativo con los datos enviados.
 * @param array $campos Lista de nombres de campos requeridos.
 * @return array Lista de mensajes de error encontrados.
 */
function validarCampos(array $data, array $campos): array
{
    $errores = [];
    foreach ($campos as $campo) {
        if (empty($data[$campo])) {
            $errores[] = "El campo \"$campo\" es obligatorio y no puede estar vacío.";
        }
    }
    return $errores;
}

// Catálogo de estados válidos permitidos para las tareas
$estadosValidos = ['pendiente', 'en_progreso', 'completada'];


// ─── GET ─────────────────────────────────────────────────────────────────────

if ($method === 'GET') {
    if ($id !== null) {
        // Obtener una tarea por ID
        $stmt = $pdo->prepare('SELECT * FROM tareas WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $tarea = $stmt->fetch();

        if (!$tarea) {
            http_response_code(404);
            echo json_encode([
                'status'  => 'error',
                'message' => "Tarea con id=$id no encontrada."
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data'   => $tarea
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        // Listar todas
        $stmt = $pdo->query('SELECT * FROM tareas ORDER BY created_at DESC');
        $tareas = $stmt->fetchAll();

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'total'  => count($tareas),
            'data'   => $tareas,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ─── POST ─────────────────────────────────────────────────────────────────────

if ($method === 'POST') {
    $body    = getBody();
    $errores = validarCampos($body, ['titulo']);

    if (!empty($body['estado']) && !in_array($body['estado'], $estadosValidos)) {
        $errores[] = 'El campo "estado" debe ser: pendiente, en_progreso o completada.';
    }

    if (!empty($errores)) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'errors' => $errores
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO tareas (titulo, descripcion, estado)
         VALUES (:titulo, :descripcion, :estado)'
    );
    $stmt->execute([
        ':titulo'      => htmlspecialchars(trim($body['titulo'])),
        ':descripcion' => htmlspecialchars(trim($body['descripcion'] ?? '')),
        ':estado'      => $body['estado'] ?? 'pendiente',
    ]);

    $nuevaId = (int) $pdo->lastInsertId();
    $nueva   = $pdo->prepare('SELECT * FROM tareas WHERE id = :id');
    $nueva->execute([':id' => $nuevaId]);

    http_response_code(201);
    echo json_encode([
        'status'  => 'success',
        'message' => 'Tarea creada correctamente.',
        'data'    => $nueva->fetch(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── PUT ──────────────────────────────────────────────────────────────────────

if ($method === 'PUT') {
    $body = getBody();
    if (!$id && isset($body['id']) && is_numeric($body['id'])) {
        $id = (int) $body['id'];
    }

    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Falta el parámetro "id" en la URL (ej: ?id=1) o en el body JSON.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Verificar que existe
    $check = $pdo->prepare('SELECT id FROM tareas WHERE id = :id');
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode([
            'status'  => 'error',
            'message' => "Tarea con id=$id no encontrada."
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $errores = validarCampos($body, ['titulo']);

    if (!empty($body['estado']) && !in_array($body['estado'], $estadosValidos)) {
        $errores[] = 'El campo "estado" debe ser: pendiente, en_progreso o completada.';
    }

    if (!empty($errores)) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'errors' => $errores
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare(
        'UPDATE tareas
         SET titulo = :titulo, descripcion = :descripcion, estado = :estado
         WHERE id = :id'
    );
    $stmt->execute([
        ':titulo'      => htmlspecialchars(trim($body['titulo'])),
        ':descripcion' => htmlspecialchars(trim($body['descripcion'] ?? '')),
        ':estado'      => $body['estado'] ?? 'pendiente',
        ':id'          => $id,
    ]);

    $actualizada = $pdo->prepare('SELECT * FROM tareas WHERE id = :id');
    $actualizada->execute([':id' => $id]);

    http_response_code(200);
    echo json_encode([
        'status'  => 'success',
        'message' => 'Tarea actualizada correctamente.',
        'data'    => $actualizada->fetch(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── DELETE ───────────────────────────────────────────────────────────────────

if ($method === 'DELETE') {
    $body = getBody();
    if (!$id && isset($body['id']) && is_numeric($body['id'])) {
        $id = (int) $body['id'];
    }

    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Falta el parámetro "id" en la URL (ej: ?id=1) o en el body JSON.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $check = $pdo->prepare('SELECT id, titulo FROM tareas WHERE id = :id');
    $check->execute([':id' => $id]);
    $tarea = $check->fetch();

    if (!$tarea) {
        http_response_code(404);
        echo json_encode([
            'status'  => 'error',
            'message' => "Tarea con id=$id no encontrada."
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM tareas WHERE id = :id');
    $stmt->execute([':id' => $id]);

    http_response_code(200);
    echo json_encode([
        'status'  => 'success',
        'message' => "Tarea \"" . $tarea['titulo'] . "\" eliminada correctamente.",
        'data'    => ['id_eliminado' => $id],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── Método no soportado ──────────────────────────────────────────────────────

http_response_code(405);
echo json_encode([
    'status'  => 'error',
    'message' => 'Método HTTP no soportado.'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

