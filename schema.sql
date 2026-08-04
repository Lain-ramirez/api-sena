-- Ejecuta este script en phpMyAdmin (cPanel) sobre la base de datos
-- que vayas a usar, para crear la tabla de ejemplo del CRUD.

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0
);
