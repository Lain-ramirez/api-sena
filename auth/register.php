<?php
/**
 * auth/register.php
 * -----------------------------------------------------------------------------
 * Módulo de Autenticación - Servicio Web de Registro de Usuarios
 * -----------------------------------------------------------------------------
 * Descripción:
 * Procesa el registro de nuevos usuarios en el sistema. Valida que los datos
 * obligatorios estén presentes (nombre, email, password), verifica la estructura
 * correcta del correo electrónico y comprueba la unicidad del email en la BD.
 * Realiza el hash unidireccional y seguro del password mediante BCRYPT antes
 * de almacenar la información en MySQL utilizando sentencias preparadas PDO.
 *
 * Contenido:
 * - Configuración de encabezados HTTP (CORS, Métodos permitidos, Content-Type JSON).
 * - Control de método HTTP (Permite únicamente POST).
 * - Extracción y decodificación del Payload JSON.
 * - Validación exhaustiva de entradas y formato de email.
 * - Verificación de no duplicidad de correo (código HTTP 409 Conflict).
 * - Cifrado seguro de contraseña con password_hash (BCRYPT).
 * - Inserción en la base de datos y retorno del registro creado (código HTTP 201 Created).
 * -----------------------------------------------------------------------------
 */

// Cabeceras HTTP para permitir comunicación con clientes REST y definir formato JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder a peticiones preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Restringir el acceso exclusivamente al verbo HTTP POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Método no permitido. Utilice POST para registrar usuarios.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Incluir configuración y conexión a la base de datos
require_once __DIR__ . '/../config/database.php';

// Leer y decodificar el cuerpo de la petición (JSON Raw)
$body = json_decode(file_get_contents('php://input'), true);

// Validación de presencia de campos obligatorios
$errores = [];
if (empty($body['nombre']))   $errores[] = 'El campo "nombre" es requerido.';
if (empty($body['email']))    $errores[] = 'El campo "email" es requerido.';
if (empty($body['password'])) $errores[] = 'El campo "password" es requerido.';

// Validación de formato sintáctico del correo electrónico
if (!empty($body['email']) && !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El campo "email" no cuenta con un formato de correo electrónico válido.';
}

// Si existen errores de validación, retornar código HTTP 422 (Unprocessable Entity)
if (!empty($errores)) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'errors' => $errores
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = Database::getConnection();

// Verificar si el correo electrónico ya se encuentra registrado en el sistema
$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
$stmt->execute([':email' => strtolower(trim($body['email']))]);

if ($stmt->fetch()) {
    http_response_code(409); // 409 Conflict: Recurso duplicado
    echo json_encode([
        'status'  => 'error',
        'message' => 'El correo electrónico ya se encuentra registrado en el sistema.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Encriptación segura de la contraseña mediante el algoritmo BCRYPT
$hash = password_hash($body['password'], PASSWORD_BCRYPT);

// Inserción del nuevo usuario utilizando consultas preparadas para mitigar inyecciones SQL
$insert = $pdo->prepare(
    'INSERT INTO usuarios (nombre, email, password, created_at)
     VALUES (:nombre, :email, :password, NOW())'
);
$insert->execute([
    ':nombre'   => htmlspecialchars(trim($body['nombre'])),
    ':email'    => strtolower(trim($body['email'])),
    ':password' => $hash,
]);

$nuevoId = (int) $pdo->lastInsertId();

// Respuesta exitosa de creación con código HTTP 201 Created (excluyendo el hash de contraseña)
http_response_code(201);
echo json_encode([
    'status'  => 'success',
    'message' => 'Usuario registrado satisfactoriamente.',
    'data'    => [
        'id'     => $nuevoId,
        'nombre' => htmlspecialchars(trim($body['nombre'])),
        'email'  => strtolower(trim($body['email'])),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

