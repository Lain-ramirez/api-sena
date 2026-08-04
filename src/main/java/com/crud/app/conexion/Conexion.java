package com.crud.app.conexion;

import java.io.IOException;
import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;

public class Conexion {

    private static Properties cargarConfig() throws IOException {
        Properties props = new Properties();
        try (InputStream in = Conexion.class.getClassLoader().getResourceAsStream("config.properties")) {
            if (in == null) {
                throw new IOException(
                        "No se encontro config.properties en src/main/resources. "
                        + "Copia config.properties.example y completa los datos de tu base de datos.");
            }
            props.load(in);
        }
        return props;
    }

    public static Connection obtenerConexion() throws SQLException {
        try {
            Properties props = cargarConfig();
            String host = props.getProperty("db.host");
            String port = props.getProperty("db.port");
            String nombre = props.getProperty("db.name");
            String usuario = props.getProperty("db.user");
            String password = props.getProperty("db.password");

            String url = "jdbc:mysql://" + host + ":" + port + "/" + nombre
                    + "?useSSL=false&serverTimezone=UTC&allowPublicKeyRetrieval=true";

            return DriverManager.getConnection(url, usuario, password);
        } catch (IOException e) {
            throw new SQLException(e.getMessage(), e);
        }
    }
}
