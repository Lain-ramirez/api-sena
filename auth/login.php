<?php
/**
 * auth/login.php
 * Endpoint: POST /auth/login
 * Autentica un usuario y retorna un token simple (para extender con JWT).
 *
 * Body JSON esperado:
 * {
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

// Validar campos
$errores = [];
if (empty($body['email']))    $errores[] = 'El campo "email" es requerido.';
if (empty($body['password'])) $errores[] = 'El campo "password" es requerido.';

if (!empty($errores)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'errors' => $errores]);
    exit;
}

$pdo = Database::getConnection();

// Buscar usuario por email
$stmt = $pdo->prepare('SELECT id, nombre, email, password FROM usuarios WHERE email = :email');
$stmt->execute([':email' => strtolower(trim($body['email']))]);
$usuario = $stmt->fetch();

// Verificar credenciales (evitar timing attacks con password_verify)
if (!$usuario || !password_verify($body['password'], $usuario['password'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Credenciales incorrectas.']);
    exit;
}

// Generar token simple (en produccion usa JWT con una libreria)
$token = bin2hex(random_bytes(32));

// Guardar token en sesion o BD segun tu arquitectura
// Por ahora lo retornamos directamente para que lo envies en cada peticion
// como header: Authorization: Bearer <token>

http_response_code(200);
echo json_encode([
    'status'  => 'success',
    'message' => 'Login exitoso.',
    'data'    => [
        'id'     => (int) $usuario['id'],
        'nombre' => $usuario['nombre'],
        'email'  => $usuario['email'],
        'token'  => $token,
    ],
]);
