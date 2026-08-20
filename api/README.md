# API SENA — PHP + MySQL (XAMPP)

API REST en PHP puro con PDO para gestión de **tareas** y autenticación de **usuarios**.  
Se ejecuta sobre XAMPP (Apache + MySQL) en local.

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

## Paso 1 — Abrir XAMPP y levantar los servicios

1. Abre **XAMPP Control Panel**.
2. Haz clic en **Start** junto a **Apache** → debe quedar en verde.
3. Haz clic en **Start** junto a **MySQL** → debe quedar en verde.
4. Verifica que ambos estén corriendo antes de continuar.

---

## Paso 2 — Copiar el proyecto a htdocs

1. Ve a la carpeta donde instalaste XAMPP.  
   Por defecto en Windows: `C:\xampp\htdocs\`  
   Por defecto en Linux/Mac: `/opt/lampp/htdocs/`
2. Crea una carpeta llamada **`api-sena`** dentro de `htdocs`.
3. Copia todos los archivos de este repositorio dentro de esa carpeta.

Resultado esperado:
```
C:\xampp\htdocs\api-sena\
    config\database.php
    auth\register.php
    auth\login.php
    tareas.php
    README.md
```

---

## Paso 3 — Crear la base de datos en phpMyAdmin

1. Abre el navegador y ve a: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. En el panel izquierdo haz clic en **Nueva** (o "New").
3. En **Nombre de la base de datos** escribe exactamente: `api_sena`
4. Collation: `utf8mb4_general_ci`
5. Clic en **Crear**.
6. Selecciona la base `api_sena` en el panel izquierdo.
7. Ve a la pestaña **SQL** y pega y ejecuta el siguiente script:

```sql
-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de tareas
CREATE TABLE IF NOT EXISTS tareas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(150)  NOT NULL,
    descripcion TEXT,
    estado      ENUM('pendiente','en_progreso','completada') NOT NULL DEFAULT 'pendiente',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

8. Haz clic en **Ejecutar** (o **Go**). Deberías ver las dos tablas creadas.

---

## Paso 4 — Verificar la configuración de conexión

Abre `config/database.php` y confirma que los valores coincidan con tu XAMPP:

| Constante   | Valor por defecto XAMPP |
|-------------|------------------------|
| `DB_HOST`   | `localhost`            |
| `DB_PORT`   | `3306`                 |
| `DB_NAME`   | `api_sena`             |
| `DB_USER`   | `root`                 |
| `DB_PASS`   | *(vacío)*              |

Si cambiaste la contraseña de MySQL en XAMPP, actualiza `DB_PASS`.

---

## Paso 5 — Probar en Postman

La base URL de todos los endpoints es:

```
http://localhost/api-sena/
```

### Autenticación

#### Registrar usuario
| Campo  | Valor                          |
|--------|-------------------------------|
| Método | `POST`                        |
| URL    | `http://localhost/api-sena/auth/register.php` |
| Body   | raw → JSON                    |

```json
{
  "nombre":   "Juan Pérez",
  "email":    "juan@correo.com",
  "password": "miClave123"
}
```

#### Login
| Campo  | Valor                          |
|--------|-------------------------------|
| Método | `POST`                        |
| URL    | `http://localhost/api-sena/auth/login.php` |
| Body   | raw → JSON                    |

```json
{
  "email":    "juan@correo.com",
  "password": "miClave123"
}
```

---

### Tareas — CRUD completo

#### GET — Listar todas las tareas
```
GET http://localhost/api-sena/tareas.php
```

#### GET — Obtener una tarea por ID
```
GET http://localhost/api-sena/tareas.php?id=1
```

#### POST — Crear tarea
```
POST http://localhost/api-sena/tareas.php
```
Body (raw JSON):
```json
{
  "titulo":      "Estudiar PHP",
  "descripcion": "Repasar PDO y buenas practicas",
  "estado":      "pendiente"
}
```
> Los estados válidos son: `pendiente`, `en_progreso`, `completada`.

#### PUT — Actualizar tarea
```
PUT http://localhost/api-sena/tareas.php?id=1
```
Body (raw JSON):
```json
{
  "titulo":      "Estudiar PHP avanzado",
  "descripcion": "PDO, JWT y arquitectura REST",
  "estado":      "en_progreso"
}
```

#### DELETE — Eliminar tarea
```
DELETE http://localhost/api-sena/tareas.php?id=1
```

---

## Configuración en Postman Online (paso a paso)

1. Abre [https://web.postman.co](https://web.postman.co) o la app de Postman.
2. Crea una **colección** nueva llamada `API SENA`.
3. Para cada endpoint, crea un **request** nuevo:
   - Selecciona el método HTTP (GET, POST, PUT, DELETE).
   - Pega la URL correspondiente.
   - Para POST y PUT: ve a la pestaña **Body** → selecciona **raw** → cambia el tipo a **JSON**.
   - Pega el JSON de ejemplo en el área de texto.
4. Haz clic en **Send**.
5. La respuesta llegará en el panel inferior en formato JSON.

> **Importante:** Postman Online envía peticiones desde los servidores de Postman, NO desde tu PC.  
> Para que llegue a `localhost`, necesitas instalar el **Postman Desktop Agent**:  
> - En Postman Online, busca el ícono de agente (esquina inferior izquierda).  
> - Descarga e instala el agente para tu OS.  
> - Una vez activo, las peticiones a `localhost` funcionarán normalmente.

---

## Códigos de respuesta HTTP utilizados

| Código | Significado                         |
|--------|-------------------------------------|
| 200    | OK — operación exitosa              |
| 201    | Created — recurso creado            |
| 204    | No Content — preflight CORS         |
| 400    | Bad Request — petición mal formada  |
| 401    | Unauthorized — credenciales malas   |
| 404    | Not Found — recurso no encontrado   |
| 405    | Method Not Allowed                  |
| 409    | Conflict — email ya registrado      |
| 422    | Unprocessable Entity — validación   |
| 500    | Internal Server Error               |

---

## Credenciales phpMyAdmin (XAMPP por defecto)

| Campo      | Valor        |
|------------|-------------|
| URL        | http://localhost/phpmyadmin |
| Usuario    | root         |
| Contraseña | *(vacía)*    |
