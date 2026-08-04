# GA7-220501096-AA3-EV02

CRUD en Java con JDBC contra una base de datos MySQL alojada en cPanel (`adso.menu08.com`).
Incluye dos interfaces sobre la misma logica: una de consola y una grafica (Swing).

## Requisitos (ya disponibles en este Cloud Shell)

- Java 21 (`java -version`)
- Maven 3.9 (`mvn -version`)
- Extensiones de VS Code instaladas: soporte de Java (`redhat.java`, `vscjava.vscode-maven`,
  `vscjava.vscode-java-debug`, `vscjava.vscode-java-test`, `vscjava.vscode-java-dependency`)
  y cliente de MySQL (`cweijan.vscode-mysql-client2`) para explorar la base de datos desde el IDE.

## Estructura del proyecto

- `com.crud.app.App` — menu de consola (CRUD).
- `com.crud.app.gui.CrudFrame` — interfaz grafica Swing (mismo CRUD, con ventana, tabla y formulario).
- `com.crud.app.conexion.Conexion` — abre la conexion JDBC leyendo `config.properties`.
- `com.crud.app.modelo.Producto` — entidad de ejemplo.
- `com.crud.app.dao.ProductoDAO` — operaciones CRUD (crear, listar, actualizar, eliminar).
- `schema.sql` — script para crear la tabla `productos` de ejemplo en phpMyAdmin (cPanel).

## 1. Configurar la conexion a la base de datos

1. Copia `src/main/resources/config.properties.example` a `src/main/resources/config.properties`.
2. Completa `db.host`, `db.port`, `db.name`, `db.user` y `db.password` con los datos de cPanel.
3. Ejecuta `schema.sql` en phpMyAdmin (cPanel) para crear la tabla `productos` de ejemplo.
4. En cPanel, en **Bases de datos remotas MySQL**, autoriza la IP publica desde donde te vas a
   conectar (ver siguiente seccion). Sin esto la conexion falla con `Access denied`.

`config.properties` contiene credenciales reales y **nunca se sube a git** (esta en
`.gitignore` y protegido con permisos `600`). Usa siempre `config.properties.example` como
plantilla al compartir o clonar el proyecto.

## 2. Autorizar la IP en cPanel

cPanel solo acepta conexiones desde IPs que agregues explicitamente en
**Bases de datos remotas MySQL**:

- **IP de este Cloud Shell (para la version de consola, que corre aqui):** `34.26.141.240`
  (puede cambiar si el entorno se reinicia; verifica con `curl -s ifconfig.me`).
- **IP de tu PC** (para la version Swing, que corre localmente): autoriza tambien tu IP
  publica de casa/oficina si vas a ejecutar `CrudFrame` fuera de este Cloud Shell.

## 3. Ejecutar la aplicacion

### Version consola (funciona en este Cloud Shell)

```bash
mvn compile exec:java
```

### Version ventanas / Swing (requiere pantalla — correr en tu PC)

Este Cloud Shell no tiene servidor grafico, asi que la interfaz Swing no se puede ver aqui.
Copia o clona el repo en tu PC (con Java 21 y Maven), configura su propio
`config.properties` y autoriza su IP como se indico arriba, luego corre:

```bash
mvn compile exec:java -Dexec.mainClass=com.crud.app.gui.CrudFrame
```

## 4. Compilar un jar ejecutable

```bash
mvn package
java -jar target/app.jar                              # version consola
java -cp target/app.jar com.crud.app.gui.CrudFrame     # version ventanas (Swing)
```

## 5. Conectar el repo con NetBeans

Es un proyecto Maven estandar (`pom.xml`), asi que NetBeans lo reconoce sin configuracion extra.
Estos pasos corren en tu PC (NetBeans no esta disponible dentro de este Cloud Shell).

1. Instala **Apache NetBeans** (paquete "Java with Maven") desde https://netbeans.apache.org/download
   y verifica que tenga JDK 21 configurado en *Tools > Java Platforms*.
2. Clona el repositorio:
   - Desde NetBeans: *Team > Git > Clone...* y pega la URL
     `git@github.com:JovannyCO/GA7-220501096-AA3-EV02.git`.
   - O desde terminal: `git clone git@github.com:JovannyCO/GA7-220501096-AA3-EV02.git`.
3. Abre el proyecto: *File > Open Project...*, selecciona la carpeta clonada (la que contiene
   `pom.xml`). NetBeans lo detecta como proyecto Maven y descarga las dependencias
   (`mysql-connector-j`, etc.) automaticamente.
4. Crea tu propio `src/main/resources/config.properties` a partir de
   `config.properties.example` y completa las credenciales — este archivo no viaja en git,
   hay que crearlo en cada maquina donde clones el proyecto.
5. Autoriza la IP publica de tu PC en cPanel (ver seccion 2): NetBeans corre localmente, igual
   que la version Swing.
6. Ejecuta la app:
   - Version consola: clic derecho en `App.java` (paquete `com.crud.app`) > **Run File**.
   - Version ventanas (Swing): clic derecho en `CrudFrame.java` (paquete `com.crud.app.gui`) >
     **Run File**.
   - Tambien puedes fijar la clase principal por defecto del proyecto en
     *Project Properties > Run > Main Class*.
