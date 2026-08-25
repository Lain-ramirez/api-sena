<?php
/**
 * auth/login.php
 * -----------------------------------------------------------------------------
 * Módulo de Autenticación - Servicio Web de Inicio de Sesión (Login)
 * -----------------------------------------------------------------------------
 * Descripción:
 * Autentica las credenciales enviadas por un usuario (email y contraseña).
 * Consulta la base de datos MySQL mediante consultas preparadas PDO y valida
 * la contraseña contra el hash almacenado utilizando `password_verify` para
 * prevenir ataques de temporización (timing attacks). Ante una autenticación
 * exitosa, emite un token de sesión criptográficamente seguro y datos del usuario.
 *
 * Contenido:
 * - Configuración de encabezados HTTP (CORS, Métodos admitidos, Content-Type JSON).
 * - Restricción estricta al método POST.
 * - Decodificación y validación de campos del Payload JSON.
 * - Búsqueda de usuario y verificación segura de credenciales (HTTP 401 Unauthorized).
 * - Generación de token pseudoaleatorio seguro (random_bytes).
 * - Retorno de respuesta estructurada en JSON con código HTTP 200 OK.
 * -----------------------------------------------------------------------------
 */

// Cabeceras HTTP para permitir la interoperabilidad de la API y formato JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder a peticiones preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Restringir el endpoint exclusivamente a peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Método no permitido. Utilice POST para iniciar sesión.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Incluir configuración centralizada de la base de datos
require_once __DIR__ . '/../config/database.php';

// Leer y decodificar el cuerpo de la petición (JSON Raw)
$body = json_decode(file_get_contents('php://input'), true);

// Validación de presencia de credenciales requeridas
$errores = [];
if (empty($body['email']))    $errores[] = 'El campo "email" es requerido.';
if (empty($body['password'])) $errores[] = 'El campo "password" es requerido.';

// Si faltan campos obligatorios, responder con código HTTP 422
if (!empty($errores)) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'errors' => $errores
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = Database::getConnection();

// Buscar usuario por correo electrónico utilizando sentencias preparadas
$stmt = $pdo->prepare('SELECT id, nombre, email, password FROM usuarios WHERE email = :email LIMIT 1');
$stmt->execute([':email' => strtolower(trim($body['email']))]);
$usuario = $stmt->fetch();

// Verificación de credenciales (evita enumeración de usuarios y timing attacks)
if (!$usuario || !password_verify($body['password'], $usuario['password'])) {
    http_response_code(401); // 401 Unauthorized: Credenciales inválidas
    echo json_encode([
        'status'  => 'error',
        'message' => 'Credenciales de acceso incorrectas.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Generación de un token criptográfico seguro de 64 caracteres hexadecimales (32 bytes)
$token = bin2hex(random_bytes(32));

// Respuesta exitosa con código HTTP 200 OK
http_response_code(200);
echo json_encode([
    'status'  => 'success',
    'message' => 'Autenticación exitosa.',
    'data'    => [
        'id'     => (int) $usuario['id'],
        'nombre' => $usuario['nombre'],
        'email'  => $usuario['email'],
        'token'  => $token,
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

