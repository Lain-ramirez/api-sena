package com.crud.app.gui;

import com.crud.app.dao.ProductoDAO;
import com.crud.app.modelo.Producto;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.sql.SQLException;

public class CrudFrame extends JFrame {

    private final ProductoDAO dao = new ProductoDAO();

    private final JTextField txtId = new JTextField(5);
    private final JTextField txtNombre = new JTextField(20);
    private final JTextField txtPrecio = new JTextField(10);
    private final JTextField txtStock = new JTextField(6);

    private final DefaultTableModel modeloTabla =
            new DefaultTableModel(new Object[]{"ID", "Nombre", "Precio", "Stock"}, 0) {
                @Override
                public boolean isCellEditable(int row, int column) {
                    return false;
                }
            };
    private final JTable tabla = new JTable(modeloTabla);

    public CrudFrame() {
        super("CRUD Productos");
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLayout(new BorderLayout(10, 10));

        add(construirFormulario(), BorderLayout.NORTH);
        add(new JScrollPane(tabla), BorderLayout.CENTER);
        add(construirBotones(), BorderLayout.SOUTH);

        tabla.getSelectionModel().addListSelectionListener(e -> {
            if (!e.getValueIsAdjusting() && tabla.getSelectedRow() != -1) {
                int fila = tabla.getSelectedRow();
                txtId.setText(modeloTabla.getValueAt(fila, 0).toString());
                txtNombre.setText(modeloTabla.getValueAt(fila, 1).toString());
                txtPrecio.setText(modeloTabla.getValueAt(fila, 2).toString());
                txtStock.setText(modeloTabla.getValueAt(fila, 3).toString());
            }
        });

        txtId.setEditable(false);
        setSize(600, 450);
        setLocationRelativeTo(null);

        cargarTabla();
    }

    private JPanel construirFormulario() {
        JPanel panel = new JPanel(new GridBagLayout());
        panel.setBorder(BorderFactory.createTitledBorder("Datos del producto"));
        GridBagConstraints c = new GridBagConstraints();
        c.insets = new Insets(5, 5, 5, 5);
        c.anchor = GridBagConstraints.WEST;

        c.gridx = 0; c.gridy = 0; panel.add(new JLabel("ID:"), c);
        c.gridx = 1; panel.add(txtId, c);

        c.gridx = 0; c.gridy = 1; panel.add(new JLabel("Nombre:"), c);
        c.gridx = 1; panel.add(txtNombre, c);

        c.gridx = 0; c.gridy = 2; panel.add(new JLabel("Precio:"), c);
        c.gridx = 1; panel.add(txtPrecio, c);

        c.gridx = 0; c.gridy = 3; panel.add(new JLabel("Stock:"), c);
        c.gridx = 1; panel.add(txtStock, c);

        return panel;
    }

    private JPanel construirBotones() {
        JPanel panel = new JPanel(new FlowLayout());

        JButton btnCrear = new JButton("Crear");
        JButton btnActualizar = new JButton("Actualizar");
        JButton btnEliminar = new JButton("Eliminar");
        JButton btnLimpiar = new JButton("Limpiar");
        JButton btnRefrescar = new JButton("Refrescar");

        btnCrear.addActionListener(e -> crear());
        btnActualizar.addActionListener(e -> actualizar());
        btnEliminar.addActionListener(e -> eliminar());
        btnLimpiar.addActionListener(e -> limpiarFormulario());
        btnRefrescar.addActionListener(e -> cargarTabla());

        panel.add(btnCrear);
        panel.add(btnActualizar);
        panel.add(btnEliminar);
        panel.add(btnLimpiar);
        panel.add(btnRefrescar);
        return panel;
    }

    private void cargarTabla() {
        try {
            modeloTabla.setRowCount(0);
            for (Producto p : dao.listar()) {
                modeloTabla.addRow(new Object[]{p.getId(), p.getNombre(), p.getPrecio(), p.getStock()});
            }
        } catch (SQLException ex) {
            mostrarError(ex);
        }
    }

    private void crear() {
        try {
            Producto p = new Producto(txtNombre.getText(), leerPrecio(), leerStock());
            dao.crear(p);
            limpiarFormulario();
            cargarTabla();
        } catch (NumberFormatException ex) {
            JOptionPane.showMessageDialog(this, "Precio y stock deben ser numericos.", "Datos invalidos", JOptionPane.WARNING_MESSAGE);
        } catch (SQLException ex) {
            mostrarError(ex);
        }
    }

    private void actualizar() {
        if (txtId.getText().isBlank()) {
            JOptionPane.showMessageDialog(this, "Selecciona un producto de la tabla primero.", "Aviso", JOptionPane.WARNING_MESSAGE);
            return;
        }
        try {
            Producto p = new Producto(Integer.parseInt(txtId.getText()), txtNombre.getText(), leerPrecio(), leerStock());
            dao.actualizar(p);
            limpiarFormulario();
            cargarTabla();
        } catch (NumberFormatException ex) {
            JOptionPane.showMessageDialog(this, "Precio y stock deben ser numericos.", "Datos invalidos", JOptionPane.WARNING_MESSAGE);
        } catch (SQLException ex) {
            mostrarError(ex);
        }
    }

    private void eliminar() {
        if (txtId.getText().isBlank()) {
            JOptionPane.showMessageDialog(this, "Selecciona un producto de la tabla primero.", "Aviso", JOptionPane.WARNING_MESSAGE);
            return;
        }
        int confirmacion = JOptionPane.showConfirmDialog(this,
                "¿Eliminar el producto \"" + txtNombre.getText() + "\"?", "Confirmar",
                JOptionPane.YES_NO_OPTION);
        if (confirmacion != JOptionPane.YES_OPTION) {
            return;
        }
        try {
            dao.eliminar(Integer.parseInt(txtId.getText()));
            limpiarFormulario();
            cargarTabla();
        } catch (SQLException ex) {
            mostrarError(ex);
        }
    }

    private double leerPrecio() {
        return Double.parseDouble(txtPrecio.getText().trim());
    }

    private int leerStock() {
        return Integer.parseInt(txtStock.getText().trim());
    }

    private void limpiarFormulario() {
        txtId.setText("");
        txtNombre.setText("");
        txtPrecio.setText("");
        txtStock.setText("");
        tabla.clearSelection();
    }

    private void mostrarError(SQLException ex) {
        JOptionPane.showMessageDialog(this, "Error de base de datos: " + ex.getMessage(), "Error", JOptionPane.ERROR_MESSAGE);
    }

    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> {
            try {
                UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
            } catch (Exception ignored) {
            }
            new CrudFrame().setVisible(true);
        });
    }
}
