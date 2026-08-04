package com.crud.app;

import com.crud.app.dao.ProductoDAO;
import com.crud.app.modelo.Producto;

import java.sql.SQLException;
import java.util.List;
import java.util.Scanner;

public class App {

    private static final ProductoDAO dao = new ProductoDAO();
    private static final Scanner sc = new Scanner(System.in);

    public static void main(String[] args) {
        int opcion;
        do {
            mostrarMenu();
            opcion = leerEntero("Elige una opcion: ");
            try {
                switch (opcion) {
                    case 1 -> crear();
                    case 2 -> listar();
                    case 3 -> actualizar();
                    case 4 -> eliminar();
                    case 0 -> System.out.println("Hasta luego.");
                    default -> System.out.println("Opcion invalida.");
                }
            } catch (SQLException e) {
                System.out.println("Error de base de datos: " + e.getMessage());
            }
        } while (opcion != 0);
    }

    private static void mostrarMenu() {
        System.out.println("\n--- CRUD Productos ---");
        System.out.println("1. Crear producto");
        System.out.println("2. Listar productos");
        System.out.println("3. Actualizar producto");
        System.out.println("4. Eliminar producto");
        System.out.println("0. Salir");
    }

    private static void crear() throws SQLException {
        System.out.print("Nombre: ");
        String nombre = sc.nextLine();
        double precio = leerDecimal("Precio: ");
        int stock = leerEntero("Stock: ");
        dao.crear(new Producto(nombre, precio, stock));
        System.out.println("Producto creado.");
    }

    private static void listar() throws SQLException {
        List<Producto> productos = dao.listar();
        if (productos.isEmpty()) {
            System.out.println("No hay productos.");
        } else {
            productos.forEach(System.out::println);
        }
    }

    private static void actualizar() throws SQLException {
        int id = leerEntero("ID del producto a actualizar: ");
        Producto producto = dao.buscarPorId(id);
        if (producto == null) {
            System.out.println("No existe un producto con ese ID.");
            return;
        }
        System.out.print("Nuevo nombre (" + producto.getNombre() + "): ");
        producto.setNombre(sc.nextLine());
        producto.setPrecio(leerDecimal("Nuevo precio (" + producto.getPrecio() + "): "));
        producto.setStock(leerEntero("Nuevo stock (" + producto.getStock() + "): "));
        boolean ok = dao.actualizar(producto);
        System.out.println(ok ? "Producto actualizado." : "No se pudo actualizar.");
    }

    private static void eliminar() throws SQLException {
        int id = leerEntero("ID del producto a eliminar: ");
        boolean ok = dao.eliminar(id);
        System.out.println(ok ? "Producto eliminado." : "No existe un producto con ese ID.");
    }

    private static int leerEntero(String mensaje) {
        System.out.print(mensaje);
        while (!sc.hasNextInt()) {
            System.out.print("Valor invalido, intenta de nuevo: ");
            sc.next();
        }
        int valor = sc.nextInt();
        sc.nextLine();
        return valor;
    }

    private static double leerDecimal(String mensaje) {
        System.out.print(mensaje);
        while (!sc.hasNextDouble()) {
            System.out.print("Valor invalido, intenta de nuevo: ");
            sc.next();
        }
        double valor = sc.nextDouble();
        sc.nextLine();
        return valor;
    }
}
