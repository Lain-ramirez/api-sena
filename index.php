<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'status' => 'success',
    'message' => 'API SENA funcionando correctamente',
    'endpoints' => [
        'POST /auth/register.php' => 'Registrar usuario',
        'POST /auth/login.php' => 'Login de usuario',
        'GET /tareas.php' => 'Listar tareas',
        'POST /tareas.php' => 'Crear tarea',
        'PUT /tareas.php?id={id}' => 'Actualizar tarea',
        'DELETE /tareas.php?id={id}' => 'Eliminar tarea'
    ]
]);
