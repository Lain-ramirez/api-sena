-- =============================================================================
-- API SENA - Script de Creación y Estructura de Base de Datos MySQL
-- =============================================================================
-- Descripción:
-- Define el esquema relacional para la API RESTful de gestión de tareas y usuarios.
-- Configura la base de datos `api_sena` con codificación UTF8MB4 y establece las
-- tablas requeridas con sus respectivas restricciones de integridad y tipos de datos.
--
-- Contenido:
-- - Base de datos: `api_sena` (utf8mb4 / utf8mb4_general_ci)
-- - Tabla `usuarios`: Gestión de cuentas de usuario, emails únicos y contraseñas cifradas.
-- - Tabla `tareas`: Gestión de tareas con estados tipados (ENUM) y timestamps automáticos.
-- =============================================================================

-- 1. Creación de la base de datos si no existe
CREATE DATABASE IF NOT EXISTS api_sena
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE api_sena;

-- 2. Creación de la tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del usuario',
    nombre     VARCHAR(100)  NOT NULL COMMENT 'Nombre completo del usuario',
    email      VARCHAR(150)  NOT NULL UNIQUE COMMENT 'Correo electrónico único para autenticación',
    password   VARCHAR(255)  NOT NULL COMMENT 'Contraseña encriptada con algoritmo BCRYPT',
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de registro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Almacén de credenciales y datos de usuarios';

-- 3. Creación de la tabla de tareas (CRUD)
CREATE TABLE IF NOT EXISTS tareas (
    id          INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la tarea',
    titulo      VARCHAR(150)  NOT NULL COMMENT 'Título o asunto de la tarea',
    descripcion TEXT          NULL COMMENT 'Descripción detallada opcional de la tarea',
    estado      ENUM('pendiente', 'en_progreso', 'completada') NOT NULL DEFAULT 'pendiente' COMMENT 'Estado actual del ciclo de vida de la tarea',
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de creación del registro',
    updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de última modificación'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Almacén de tareas y seguimiento de estados';

