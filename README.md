# API SENA — PHP + MySQL (Cloud Workstation)

API REST en PHP puro con PDO para gestión de **tareas** y autenticación de **usuarios**.  
Corre sobre el servidor built-in de PHP 8.3 + MySQL 8 en Google Cloud Workstation.

---

## Estructura del proyecto

```
api-sena/
├── config/
│   └── database.php      # Configuracion PDO (singleton)
├── auth/
│   ├── register.php      # POST  — registrar usuario
│   └── login.php         # POST  — autenticar usuario
├── tareas.php            # GET / POST / PUT / DELETE — CRUD de tareas
└── README.md
```

---

## Paso 1 — Iniciar MySQL

```bash
sudo service mysql start
sudo chmod 777 /var/run/mysqld
```

Verificar que esté corriendo:
```bash
mysqladmin -u root ping
# Debe responder: mysqld is alive
```

---

## Paso 2 — Crear la base de datos (solo la primera vez)

```bash
sudo mysql -u root << 'SQL'
CREATE DATABASE IF NOT EXISTS api_sena CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE api_sena;

CREATE TABLE IF NOT EXISTS usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tareas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(150)  NOT NULL,
    descripcion TEXT,
    estado      ENUM('pendiente','en_progreso','completada') NOT NULL DEFAULT 'pendiente',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
SQL
```

---

## Paso 3 — Levantar el servidor PHP

```bash
php -S 0.0.0.0:8080 -t /home/lain/api-sena
```

El servidor queda corriendo en el puerto **8080**.

---

## Paso 4 — Probar en Postman

La base URL es:

```
http://localhost:8080
```

> **Postman Online:** Usa el **Postman Desktop Agent** para que las peticiones a localhost funcionen.

---

### Autenticación

#### POST — Registrar usuario
- **URL:** `http://localhost:8080/auth/register.php`
- **Body (raw JSON):**
```json
{
  "nombre":   "Juan Perez",
  "email":    "juan@correo.com",
  "password": "clave123"
}
```

#### POST — Login
- **URL:** `http://localhost:8080/auth/login.php`
- **Body (raw JSON):**
```json
{
  "email":    "juan@correo.com",
  "password": "clave123"
}
```

---

### Tareas — CRUD completo

#### GET — Listar todas las tareas
```
GET http://localhost:8080/tareas.php
```

#### GET — Obtener tarea por ID
```
GET http://localhost:8080/tareas.php?id=1
```

#### POST — Crear tarea
```
POST http://localhost:8080/tareas.php
```
```json
{
  "titulo":      "Estudiar PHP",
  "descripcion": "PDO y buenas practicas",
  "estado":      "pendiente"
}
```
> Estados válidos: `pendiente`, `en_progreso`, `completada`

#### PUT — Actualizar tarea
```
PUT http://localhost:8080/tareas.php?id=1
```
```json
{
  "titulo":      "Estudiar PHP avanzado",
  "descripcion": "JWT y arquitectura REST",
  "estado":      "en_progreso"
}
```

#### DELETE — Eliminar tarea
```
DELETE http://localhost:8080/tareas.php?id=1
```

---

## Códigos de respuesta HTTP

| Código | Significado                         |
|--------|-------------------------------------|
| 200    | OK — operación exitosa              |
| 201    | Created — recurso creado            |
| 400    | Bad Request — petición mal formada  |
| 401    | Unauthorized — credenciales malas   |
| 404    | Not Found — recurso no encontrado   |
| 405    | Method Not Allowed                  |
| 409    | Conflict — email ya registrado      |
| 422    | Unprocessable Entity — validación   |
| 500    | Internal Server Error               |

---

## Configuración de BD (config/database.php)

| Parámetro | Valor         |
|-----------|--------------|
| Host      | `127.0.0.1`  |
| Puerto    | `3306`       |
| Base de datos | `api_sena` |
| Usuario   | `root`       |
| Contraseña| *(vacía)*    |
