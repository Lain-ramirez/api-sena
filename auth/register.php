<?php
/**
 * auth/register.php
 * Endpoint: POST /auth/register
 * Registra un nuevo usuario en la tabla `usuarios`.
 *
 * Body JSON esperado:
 * {
 *   "nombre":   "Juan Perez",
 *   "email":    "juan@correo.com",
 *   "password": "miClave123"
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responder preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Solo aceptamos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metodo no permitido. Usa POST.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Leer y decodificar el body JSON
$body = json_decode(file_get_contents('php://input'), true);

// Validar campos requeridos
$errores = [];
if (empty($body['nombre']))   $errores[] = 'El campo "nombre" es requerido.';
if (empty($body['email']))    $errores[] = 'El campo "email" es requerido.';
if (empty($body['password'])) $errores[] = 'El campo "password" es requerido.';

if (!empty($body['email']) && !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El "email" no tiene un formato valido.';
}

if (!empty($errores)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'errors' => $errores]);
    exit;
}

$pdo = Database::getConnection();

// Verificar que el email no este ya registrado
$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
$stmt->execute([':email' => $body['email']]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'El email ya esta registrado.']);
    exit;
}

// Hash seguro de la contrasena
$hash = password_hash($body['password'], PASSWORD_BCRYPT);

// Insertar usuario
$insert = $pdo->prepare(
    'INSERT INTO usuarios (nombre, email, password, created_at)
     VALUES (:nombre, :email, :password, NOW())'
);
$insert->execute([
    ':nombre'   => htmlspecialchars(trim($body['nombre'])),
    ':email'    => strtolower(trim($body['email'])),
    ':password' => $hash,
]);

$nuevoId = $pdo->lastInsertId();

http_response_code(201);
echo json_encode([
    'status'  => 'success',
    'message' => 'Usuario registrado correctamente.',
    'data'    => [
        'id'     => (int) $nuevoId,
        'nombre' => htmlspecialchars(trim($body['nombre'])),
        'email'  => strtolower(trim($body['email'])),
    ],
]);
