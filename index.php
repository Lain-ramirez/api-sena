<?php
/**
 * index.php
 * -----------------------------------------------------------------------------
 * API SENA - Punto de Entrada y Verificación de Estado (Health Check)
 * -----------------------------------------------------------------------------
 * Descripción:
 * Archivo raíz de la API que actúa como punto de entrada base y verificación de
 * estado del servicio. Retorna un mensaje de confirmación y el catálogo básico
 * de rutas y endpoints disponibles en formato JSON.
 *
 * Contenido:
 * - Configuración de cabeceras HTTP (CORS y Content-Type: application/json).
 * - Respuesta JSON con el estado del servicio y lista de endpoints disponibles.
 * -----------------------------------------------------------------------------
 */

// Cabeceras HTTP para permitir consumo de la API y definir formato JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder preflight CORS en caso de peticiones OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Estructura de respuesta con catálogo de servicios
echo json_encode([
    'status'    => 'success',
    'message'   => 'API SENA funcionando correctamente',
    'timestamp' => date('c'),
    'endpoints' => [
        'POST /auth/register.php'    => 'Registrar nuevo usuario con contraseña cifrada',
        'POST /auth/login.php'       => 'Autenticación de usuario y generación de token',
        'GET /tareas.php'            => 'Listar todas las tareas registradas',
        'GET /tareas.php?id={id}'    => 'Consultar una tarea específica por su ID',
        'POST /tareas.php'           => 'Crear una nueva tarea en el sistema',
        'PUT /tareas.php?id={id}'    => 'Actualizar información de una tarea existente',
        'DELETE /tareas.php?id={id}' => 'Eliminar una tarea del sistema'
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

