# DOCUMENTACIÓN TÉCNICA Y SUSTENTACIÓN DE LA API REST — SENA

---

## 1. Descripción General de la API y su Relación con los Requerimientos Funcionales

La **API REST de Gestión de Tareas y Usuarios** es un servicio web backend desarrollado en **PHP puro (Vanilla PHP 8.3)** y respaldado por una base de datos relacional **MySQL 8**. Esta solución está orientada a desacoplar la lógica del servidor de cualquier cliente consumidor (interfaces web, aplicaciones móviles o herramientas de pruebas como Postman).

### Relación con los Requerimientos Funcionales del Sistema:

1. **RF-01: Registro de Usuarios:** Permitir la creación de nuevos usuarios mediante el suministro de nombre, correo electrónico y contraseña. Se asegura la unicidad del correo en la base de datos y la confidencialidad mediante encriptación unidireccional con BCRYPT.
2. **RF-02: Autenticación de Usuarios (Login):** Validar credenciales de acceso (email y contraseña contra el hash de la BD) y expedir un token criptográfico de sesión para autorizar transacciones.
3. **RF-03: Consulta de Tareas (Listado y Detalle):** Permitir la recuperación de todas las tareas almacenadas ordenadas cronológicamente, así como la consulta detallada de una tarea específica a través de su identificador único (`id`).
4. **RF-04: Creación de Nuevas Tareas:** Registrar tareas proporcionando título, descripción opcional y estado (`pendiente`, `en_progreso`, `completada`), validando la obligatoriedad del título y la consistencia del estado.
5. **RF-05: Actualización de Tareas:** Modificar los datos y el ciclo de vida de una tarea existente identificada por su `id`, garantizando la persistencia de cambios y la actualización de marcas temporales.
6. **RF-06: Eliminación de Tareas:** Suprimir registros de tareas del sistema mediante su identificador único previa verificación de existencia.
7. **RF-07: Respuestas Estándar y Códigos HTTP:** Comunicar los resultados de las operaciones a través de payloads JSON estructurados y códigos de estado HTTP estándar (200, 201, 400, 401, 404, 405, 409, 422, 500).

---

## 2. Explicación de los Servicios Web y Endpoints Implementados

La API sigue los principios de la arquitectura RESTful, utilizando URLs basadas en recursos y verbos HTTP semánticos.

### 2.1. Matriz de Endpoints y Servicios

| Módulo | Método | Endpoint | Parámetros / Body | Código Éxito | Descripción |
| :--- | :---: | :--- | :--- | :---: | :--- |
| **General** | `GET` | `/index.php` | Ninguno | `200 OK` | Verificación de disponibilidad del servicio (*Health Check*) y catálogo de rutas. |
| **Auth** | `POST` | `/auth/register.php` | JSON: `nombre`, `email`, `password` | `201 Created` | Registra un usuario y almacena el hash BCRYPT. |
| **Auth** | `POST` | `/auth/login.php` | JSON: `email`, `password` | `200 OK` | Valida credenciales y genera token de sesión. |
| **Tareas** | `GET` | `/tareas.php` | Ninguno | `200 OK` | Retorna el listado completo de tareas y total de registros. |
| **Tareas** | `GET` | `/tareas.php?id={id}` | Query: `id` (entero) | `200 OK` | Obtiene los datos detallados de una tarea específica. |
| **Tareas** | `POST` | `/tareas.php` | JSON: `titulo`, `descripcion`, `estado` | `201 Created` | Inserta una nueva tarea en la base de datos. |
| **Tareas** | `PUT` | `/tareas.php?id={id}` | Query/JSON: `id`, `titulo`, `descripcion`, `estado` | `200 OK` | Actualiza los datos de la tarea indicada. |
| **Tareas** | `DELETE` | `/tareas.php?id={id}` | Query/JSON: `id` | `200 OK` | Elimina la tarea especificada de la base de datos. |

---

## 3. Demostración y Pruebas Funcionales con Postman

Las pruebas funcionales se realizaron enviando peticiones HTTP con formato de carga `raw JSON` y encabezado `Content-Type: application/json`.

```
Base URL: http://localhost:8080
```

### 3.1. Prueba 01: Verificación de Estado (Health Check)
* **Método:** `GET`
* **URL:** `http://localhost:8080/index.php`
* **Headers:** `Accept: application/json`

### 3.2. Prueba 02: Registro de Usuario
* **Método:** `POST`
* **URL:** `http://localhost:8080/auth/register.php`
* **Headers:** `Content-Type: application/json`
* **Body (Raw JSON):**
```json
{
  "nombre": "Carlos Mendoza",
  "email": "carlos.mendoza@correo.com",
  "password": "PasswordSeguro2026!"
}
```

### 3.3. Prueba 03: Autenticación (Login)
* **Método:** `POST`
* **URL:** `http://localhost:8080/auth/login.php`
* **Headers:** `Content-Type: application/json`
* **Body (Raw JSON):**
```json
{
  "email": "carlos.mendoza@correo.com",
  "password": "PasswordSeguro2026!"
}
```

### 3.4. Prueba 04: Crear Tarea
* **Método:** `POST`
* **URL:** `http://localhost:8080/tareas.php`
* **Headers:** `Content-Type: application/json`
* **Body (Raw JSON):**
```json
{
  "titulo": "Diseñar arquitectura de software",
  "descripcion": "Modelar diagramas de componentes y base de datos relacional para el SENA",
  "estado": "pendiente"
}
```

### 3.5. Prueba 05: Consultar Todas las Tareas
* **Método:** `GET`
* **URL:** `http://localhost:8080/tareas.php`

### 3.6. Prueba 06: Consultar Tarea por ID
* **Método:** `GET`
* **URL:** `http://localhost:8080/tareas.php?id=1`

### 3.7. Prueba 07: Actualizar Tarea
* **Método:** `PUT`
* **URL:** `http://localhost:8080/tareas.php?id=1`
* **Headers:** `Content-Type: application/json`
* **Body (Raw JSON):**
```json
{
  "titulo": "Diseñar arquitectura de software (Aprobado)",
  "descripcion": "Diagramas de componentes y BD completados y validados",
  "estado": "en_progreso"
}
```

### 3.8. Prueba 08: Eliminar Tarea
* **Método:** `DELETE`
* **URL:** `http://localhost:8080/tareas.php?id=1`

---

## 4. Evidencia de Resultados Obtenidos para las Operaciones

### 4.1. Respuestas de Éxito

#### Respuesta `GET /index.php` — `HTTP 200 OK`
```json
{
  "status": "success",
  "message": "API SENA funcionando correctamente",
  "timestamp": "2026-08-25T09:09:20-05:00",
  "endpoints": {
    "POST /auth/register.php": "Registrar nuevo usuario con contraseña cifrada",
    "POST /auth/login.php": "Autenticación de usuario y generación de token",
    "GET /tareas.php": "Listar todas las tareas registradas",
    "GET /tareas.php?id={id}": "Consultar una tarea específica por su ID",
    "POST /tareas.php": "Crear una nueva tarea en el sistema",
    "PUT /tareas.php?id={id}": "Actualizar información de una tarea existente",
    "DELETE /tareas.php?id={id}": "Eliminar una tarea del sistema"
  }
}
```

#### Respuesta `POST /auth/register.php` — `HTTP 201 Created`
```json
{
  "status": "success",
  "message": "Usuario registrado satisfactoriamente.",
  "data": {
    "id": 1,
    "nombre": "Carlos Mendoza",
    "email": "carlos.mendoza@correo.com"
  }
}
```

#### Respuesta `POST /auth/login.php` — `HTTP 200 OK`
```json
{
  "status": "success",
  "message": "Autenticación exitosa.",
  "data": {
    "id": 1,
    "nombre": "Carlos Mendoza",
    "email": "carlos.mendoza@correo.com",
    "token": "4e78a6d71b80e556e6d1f0ca9b63489e218cbf5ad7d028ef79c6d4ba402519ad"
  }
}
```

#### Respuesta `POST /tareas.php` — `HTTP 201 Created`
```json
{
  "status": "success",
  "message": "Tarea creada correctamente.",
  "data": {
    "id": 1,
    "titulo": "Diseñar arquitectura de software",
    "descripcion": "Modelar diagramas de componentes y base de datos relacional para el SENA",
    "estado": "pendiente",
    "created_at": "2026-08-25 09:12:00",
    "updated_at": "2026-08-25 09:12:00"
  }
}
```

#### Respuesta `GET /tareas.php` — `HTTP 200 OK`
```json
{
  "status": "success",
  "total": 1,
  "data": [
    {
      "id": 1,
      "titulo": "Diseñar arquitectura de software",
      "descripcion": "Modelar diagramas de componentes y base de datos relacional para el SENA",
      "estado": "pendiente",
      "created_at": "2026-08-25 09:12:00",
      "updated_at": "2026-08-25 09:12:00"
    }
  ]
}
```

#### Respuesta `PUT /tareas.php?id=1` — `HTTP 200 OK`
```json
{
  "status": "success",
  "message": "Tarea actualizada correctamente.",
  "data": {
    "id": 1,
    "titulo": "Diseñar arquitectura de software (Aprobado)",
    "descripcion": "Diagramas de componentes y BD completados y validados",
    "estado": "en_progreso",
    "created_at": "2026-08-25 09:12:00",
    "updated_at": "2026-08-25 09:15:30"
  }
}
```

#### Respuesta `DELETE /tareas.php?id=1` — `HTTP 200 OK`
```json
{
  "status": "success",
  "message": "Tarea \"Diseñar arquitectura de software (Aprobado)\" eliminada correctamente.",
  "data": {
    "id_eliminado": 1
  }
}
```

---

### 4.2. Respuestas de Control de Errores y Validaciones

* **Email Duplicado (`POST /auth/register.php`):** `HTTP 409 Conflict`
  ```json
  {
    "status": "error",
    "message": "El correo electrónico ya se encuentra registrado en el sistema."
  }
  ```
* **Credenciales Incorrectas (`POST /auth/login.php`):** `HTTP 401 Unauthorized`
  ```json
  {
    "status": "error",
    "message": "Credenciales de acceso incorrectas."
  }
  ```
* **Recurso No Encontrado (`GET /tareas.php?id=999`):** `HTTP 404 Not Found`
  ```json
  {
    "status": "error",
    "message": "Tarea con id=999 no encontrada."
  }
  ```
* **Datos Incompletos o Inválidos (`POST /tareas.php`):** `HTTP 422 Unprocessable Entity`
  ```json
  {
    "status": "error",
    "errors": [
      "El campo \"titulo\" es obligatorio y no puede estar vacío."
    ]
  }
  ```
* **Método No Permitido (`POST /index.php`):** `HTTP 405 Method Not Allowed`

---

## 5. Presentación del Repositorio del Proyecto

### 5.1. URL de Acceso y Control de Versiones
* **URL Remota del Repositorio:** [https://github.com/Lain-ramirez/api-sena.git](https://github.com/Lain-ramirez/api-sena.git)
* **Rama Principal de Producción:** `production`

### 5.2. Estructura General del Proyecto
```
api-sena/
├── auth/
│   ├── login.php             # Servicio web de autenticación de usuarios y entrega de tokens
│   └── register.php          # Servicio web de registro de usuarios y hash BCRYPT
├── config/
│   └── database.php          # Clase Singleton de conexión a MySQL vía PDO con control de excepciones
├── database.sql              # Script SQL DDL de creación de la base de datos y tablas relacionales
├── index.php                 # Punto de entrada raíz, health check y catálogo JSON de rutas
├── tareas.php                # Controlador RESTful integral (CRUD de tareas: GET, POST, PUT, DELETE)
├── README.md                 # Guía de instalación, configuración del entorno y manual de pruebas
└── DOCUMENTACION_PROYECTO.md  # Documento técnico completo y guion de exposición oral
```

### 5.3. Historial Básico de Commits
El repositorio mantiene un flujo estructurado de confirmaciones:
1. `72a87ed`: Creación inicial de módulos `api`, `config` y `auth`.
2. `aa59397`: Ajustes en `tareas.php`, `index.php` y guía de pruebas para Postman.
3. `374a823`: Integración de guion y refinamiento de respuestas.
4. `00f222a`: Actualización de configuraciones y estabilidad de rutas.
5. `f9c72fc`: Depuración y limpieza de archivos temporales e innecesarios.

---

## 6. Explicación Breve del Archivo README.md

El archivo `README.md` actúa como el manual de operaciones y despliegue rápido del proyecto. Proporciona:
1. **Requisitos de Entorno:** Especificación de versiones (PHP 8.3+, MySQL 8+ sobre Cloud Workstation/Local).
2. **Estructura del Proyecto:** Vista esquemática de carpetas y responsabilidades de cada archivo.
3. **Guía de Despliegue en 4 Pasos:**
   - *Paso 1:* Inicio y verificación del daemon del servicio MySQL (`sudo service mysql start`).
   - *Paso 2:* Ejecución del script SQL para crear la base de datos `api_sena` y las tablas `usuarios` y `tareas`.
   - *Paso 3:* Inicialización del servidor embebido de PHP en el puerto 8080 (`php -S 0.0.0.0:8080`).
   - *Paso 4:* Pruebas de los endpoints en Postman con ejemplos de payloads JSON.
4. **Tabla de Códigos de Respuesta HTTP:** Referencia rápida de estados HTTP y su significado dentro de la API.
5. **Configuración de Conexión:** Parámetros estándar de host, puerto, base de datos y credenciales.

---

## 7. Guion para Sustentación Oral (Máximo 1 Minuto y 30 Segundos)

> **Tiempo estimado:** 1 minuto con 20 segundos a 1 minuto con 28 segundos (velocidad normal de exposición, ~210 palabras).

### 🎙️ Texto del Guion:

> **[0:00 - 0:15] Introducción y Propósito:**  
> "Buenas tardes/días. Presento la API RESTful del proyecto SENA, desarrollada en PHP con conexión PDO a MySQL. Su objetivo es gestionar usuarios y tareas de forma desacoplada, siguiendo las buenas prácticas de la arquitectura REST y seguridad web."

> **[0:15 - 0:45] Estructura del Repositorio y Archivos:**  
> "La arquitectura del proyecto está organizada de forma modular:  
> • En la carpeta `config`, el archivo `database.php` implementa el patrón Singleton con PDO para una conexión eficiente y protegida contra inyecciones SQL.  
> • En la carpeta `auth`, `register.php` registra usuarios encriptando contraseñas con BCRYPT, y `login.php` valida credenciales generando un token de sesión.  
> • En la raíz, `tareas.php` implementa el CRUD completo mediante los métodos GET, POST, PUT y DELETE.  
> • El archivo `index.php` actúa como health check mostrando el catálogo de endpoints, y `database.sql` contiene la estructura de las tablas."

> **[0:45 - 1:10] Documentación y Pruebas con Postman:**  
> "El archivo `README.md` documenta la instalación, el inicio de MySQL y la ejecución del servidor.  
> Cada endpoint fue validado con Postman, manejando respuestas JSON estructuradas y códigos de estado HTTP como 200, 201 para creaciones, 401 para credenciales inválidas, 404 y 422 para validaciones de campos."

> **[1:10 - 1:25] Repositorio y Cierre:**  
> "Todo el código está documentado con estándares PHPDoc y versionado en GitHub en la rama de producción con un historial de commits limpio.  
> Con esto demostramos una API sólida, segura y lista para producción. Muchas gracias."
