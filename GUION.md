# Guion de Presentación Técnica — Jovanny Medina Cifuentes

Cordial saludo a todos. Mi nombre es Jovanny Medina Cifuentes y a continuación les presento la validación de nuestra API REST desarrollada en PHP con arquitectura orientada a recursos y persistencia en MySQL. Vamos a revisar cuatro operaciones clave documentadas en nuestra colección de Postman.

Inicio con el Punto 1: Registro de Usuarios, mediante el método POST al endpoint /auth/register.php. Aquí envío un payload en formato JSON con nombre, correo y contraseña. El backend valida la estructura, procesa el cifrado de la clave mediante un algoritmo seguro de hashing y persiste el registro en la base de datos, retornando un código de estado 201 Created junto con la confirmación de la entidad creada.

Pasando al Punto 4: Lectura de recursos, ejecuto una petición GET hacia /tareas.php. Como dicta el estándar REST para métodos de consulta, no envío cuerpo en la solicitud. El servidor ejecuta la sentencia SQL a través de PDO y responde con un código 200 OK, devolviendo una colección en JSON con el total y detalle de todas las tareas almacenadas.

En el Punto 6, aplico el método PUT sobre /tareas.php?id=1. Aquí especifico el recurso a mutar mediante el parámetro de consulta id y adjunto en el body el nuevo estado y título. El servidor valida la existencia previa del registro, actualiza los campos correspondientes y devuelve un código 200 OK con los datos modificados.

Finalmente, en el Punto 7, implemento la eliminación con el método DELETE hacia /tareas.php?id=1. El endpoint valida que el ID exista, efectúa el borrado seguro en la base de datos y confirma la operación con un estado 200 OK. Con esto demuestro el ciclo de vida completo de la API, cumpliendo con las buenas prácticas REST y el manejo adecuado de los códigos de respuesta HTTP.
