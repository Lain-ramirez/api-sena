# GA7-220501096-AA3-EV02
JAVA

CRUD en Java (consola) con JDBC contra una base de datos MySQL alojada en cPanel.

## Requisitos ya disponibles en este entorno

- Java 21 (`java -version`)
- Maven 3.9 (`mvn -version`)
- Extensiones de VS Code instaladas: soporte de Java (`redhat.java`, `vscjava.vscode-maven`,
  `vscjava.vscode-java-debug`, `vscjava.vscode-java-test`, `vscjava.vscode-java-dependency`)
  y cliente de MySQL (`cweijan.vscode-mysql-client2`) para explorar la base de datos desde el IDE.

## Configurar la conexion a la base de datos

1. Copia `src/main/resources/config.properties.example` a `src/main/resources/config.properties`.
2. Completa `db.host` (IP o dominio que te dio cPanel), `db.port`, `db.name`, `db.user` y `db.password`.
3. En cPanel, en **Bases de datos remotas MySQL**, agrega esta IP a la lista de hosts permitidos:

   **IP publica de este entorno: `34.26.141.240`**

   (Esta IP puede cambiar si el entorno se reinicia; si la conexion falla, vuelve a verificarla
   con `curl -s ifconfig.me` en la terminal.)
4. Ejecuta `schema.sql` en phpMyAdmin (cPanel) para crear la tabla `productos` de ejemplo.

## Ejecutar la aplicacion

```bash
mvn compile exec:java
```

## Compilar un jar ejecutable

```bash
mvn package
java -jar target/app.jar
```

## Estructura

- `com.crud.app.App` — menu de consola (CRUD).
- `com.crud.app.conexion.Conexion` — abre la conexion JDBC leyendo `config.properties`.
- `com.crud.app.modelo.Producto` — entidad de ejemplo.
- `com.crud.app.dao.ProductoDAO` — operaciones CRUD (crear, listar, actualizar, eliminar).
