<?php
/**
 * tareas.php
 * Endpoint principal para el CRUD de tareas.
 *
 * Rutas soportadas (misma URL, distinto metodo HTTP):
 *   GET    /tareas.php          -> Listar todas las tareas
 *   GET    /tareas.php?id=N     -> Obtener una tarea por ID
 *   POST   /tareas.php          -> Crear una tarea nueva
 *   PUT    /tareas.php?id=N     -> Actualizar una tarea existente
 *   DELETE /tareas.php?id=N     -> Eliminar una tarea
 *
 * Tabla esperada en MySQL (crea esto en phpMyAdmin):
 * -------------------------------------------------------
 * CREATE TABLE tareas (
 *   id          INT AUTO_INCREMENT PRIMARY KEY,
 *   titulo      VARCHAR(150)  NOT NULL,
 *   descripcion TEXT,
 *   estado      ENUM('pendiente','en_progreso','completada') NOT NULL DEFAULT 'pendiente',
 *   created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 *   updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 * );
 * -------------------------------------------------------
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responder preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/config/database.php';

$pdo    = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Extraer ID de la URL (?id=X)
$id = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];
}


// ─── Helpers ────────────────────────────────────────────────────────────────

/**
 * Devuelve el body JSON parseado o termina con error 400.
 */
function getBody(): array
{
    $raw = file_get_contents('php://input');
    if (trim($raw) === '') {
        return [];
    }
    $body = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Body JSON invalido.']);
        exit;
    }
    return $body ?? [];
}

/**
 * Valida que los campos requeridos existan y no esten vacios.
 */
function validarCampos(array $data, array $campos): array
{
    $errores = [];
    foreach ($campos as $campo) {
        if (empty($data[$campo])) {
            $errores[] = "El campo \"$campo\" es requerido.";
        }
    }
    return $errores;
}

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
            echo json_encode(['status' => 'error', 'message' => "Tarea con id=$id no encontrada."]);
            exit;
        }

        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $tarea]);
    } else {
        // Listar todas
        $stmt = $pdo->query('SELECT * FROM tareas ORDER BY created_at DESC');
        $tareas = $stmt->fetchAll();

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'total'  => count($tareas),
            'data'   => $tareas,
        ]);
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
        echo json_encode(['status' => 'error', 'errors' => $errores]);
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

    $nuevaId = $pdo->lastInsertId();
    $nueva   = $pdo->prepare('SELECT * FROM tareas WHERE id = :id');
    $nueva->execute([':id' => $nuevaId]);

    http_response_code(201);
    echo json_encode([
        'status'  => 'success',
        'message' => 'Tarea creada correctamente.',
        'data'    => $nueva->fetch(),
    ]);
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
        echo json_encode(['status' => 'error', 'message' => 'Falta el parametro "id" en la URL (ej: ?id=1) o en el body JSON.']);
        exit;
    }

    // Verificar que existe
    $check = $pdo->prepare('SELECT id FROM tareas WHERE id = :id');
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => "Tarea con id=$id no encontrada."]);
        exit;
    }

    $body    = getBody();
    $errores = validarCampos($body, ['titulo']);

    if (!empty($body['estado']) && !in_array($body['estado'], $estadosValidos)) {
        $errores[] = 'El campo "estado" debe ser: pendiente, en_progreso o completada.';
    }

    if (!empty($errores)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'errors' => $errores]);
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
    ]);
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
        echo json_encode(['status' => 'error', 'message' => 'Falta el parametro "id" en la URL (ej: ?id=1) o en el body JSON.']);
        exit;
    }

    $check = $pdo->prepare('SELECT id, titulo FROM tareas WHERE id = :id');
    $check->execute([':id' => $id]);
    $tarea = $check->fetch();

    if (!$tarea) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => "Tarea con id=$id no encontrada."]);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM tareas WHERE id = :id');
    $stmt->execute([':id' => $id]);

    http_response_code(200);
    echo json_encode([
        'status'  => 'success',
        'message' => "Tarea \"" . $tarea['titulo'] . "\" eliminada correctamente.",
        'data'    => ['id_eliminado' => $id],
    ]);
    exit;
}

// ─── Metodo no soportado ──────────────────────────────────────────────────────

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Metodo HTTP no soportado.']);
