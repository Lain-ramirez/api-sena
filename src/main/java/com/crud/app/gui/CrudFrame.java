package com.crud.app.gui;

import com.crud.app.dao.ProductoDAO;
import com.crud.app.modelo.Producto;
import com.crud.app.util.Moneda;

import javax.swing.*;
import javax.swing.border.EmptyBorder;
import javax.swing.border.LineBorder;
import javax.swing.table.DefaultTableCellRenderer;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.JTableHeader;
import javax.swing.table.TableCellRenderer;
import java.awt.*;
import java.sql.SQLException;

public class CrudFrame extends JFrame {

    private static final Color AZUL_OSCURO = new Color(33, 47, 61);
    private static final Color AZUL = new Color(41, 128, 185);
    private static final Color VERDE = new Color(39, 174, 96);
    private static final Color ROJO = new Color(192, 57, 43);
    private static final Color GRIS = new Color(127, 140, 141);
    private static final Color FONDO = new Color(245, 246, 250);
    private static final Color FILA_ALTERNA = new Color(240, 244, 248);
    private static final Color SELECCION = new Color(214, 228, 240);
    private static final Color BORDE = new Color(206, 212, 218);
    private static final Color TEXTO = new Color(52, 73, 94);

    private static final Font FUENTE = new Font("Segoe UI", Font.PLAIN, 13);
    private static final Font FUENTE_NEGRITA = new Font("Segoe UI", Font.BOLD, 13);

    private final ProductoDAO dao = new ProductoDAO();

    private final JTextField txtId = new JTextField(5);
    private final JTextField txtNombre = new JTextField(20);
    private final JTextField txtPrecio = new JTextField(10);
    private final JTextField txtStock = new JTextField(6);
    private final JLabel lblEstado = new JLabel();

    private final DefaultTableModel modeloTabla =
            new DefaultTableModel(new Object[]{"ID", "Nombre", "Precio", "Stock"}, 0) {
                @Override
                public boolean isCellEditable(int row, int column) {
                    return false;
                }
            };

    /** Pinta las filas pares e impares con fondos distintos para que se lean mejor. */
    private final JTable tabla = new JTable(modeloTabla) {
        @Override
        public Component prepareRenderer(TableCellRenderer renderer, int fila, int columna) {
            Component c = super.prepareRenderer(renderer, fila, columna);
            if (!isRowSelected(fila)) {
                c.setBackground(fila % 2 == 0 ? Color.WHITE : FILA_ALTERNA);
            }
            return c;
        }
    };

    public CrudFrame() {
        super("CRUD Productos");
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLayout(new BorderLayout());
        getContentPane().setBackground(FONDO);

        prepararTabla();

        JPanel centro = new JPanel(new BorderLayout(0, 14));
        centro.setBorder(new EmptyBorder(18, 20, 14, 20));
        centro.setBackground(FONDO);
        centro.add(construirFormulario(), BorderLayout.NORTH);
        centro.add(construirTabla(), BorderLayout.CENTER);

        add(construirCabecera(), BorderLayout.NORTH);
        add(centro, BorderLayout.CENTER);
        add(construirBarraEstado(), BorderLayout.SOUTH);

        tabla.getSelectionModel().addListSelectionListener(e -> {
            if (!e.getValueIsAdjusting() && tabla.getSelectedRow() != -1) {
                // La vista puede estar ordenada, asi que hay que traducir el indice al modelo.
                int fila = tabla.convertRowIndexToModel(tabla.getSelectedRow());
                txtId.setText(modeloTabla.getValueAt(fila, 0).toString());
                txtNombre.setText(modeloTabla.getValueAt(fila, 1).toString());
                txtPrecio.setText(formatearCelda(modeloTabla.getValueAt(fila, 2)));
                txtStock.setText(modeloTabla.getValueAt(fila, 3).toString());
            }
        });

        setSize(880, 620);
        setMinimumSize(new Dimension(720, 520));
        setLocationRelativeTo(null);

        cargarTabla();
    }

    // ------------------------------------------------------------------ cabecera

    private JPanel construirCabecera() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBackground(AZUL_OSCURO);
        panel.setBorder(new EmptyBorder(18, 24, 18, 24));

        JLabel titulo = new JLabel("Gestion de Productos");
        titulo.setFont(new Font("Segoe UI", Font.BOLD, 22));
        titulo.setForeground(Color.WHITE);

        JLabel subtitulo = new JLabel("Registro, consulta, actualizacion y eliminacion de productos");
        subtitulo.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        subtitulo.setForeground(new Color(174, 182, 191));
        subtitulo.setBorder(new EmptyBorder(4, 0, 0, 0));

        JPanel textos = new JPanel(new GridLayout(2, 1));
        textos.setOpaque(false);
        textos.add(titulo);
        textos.add(subtitulo);

        panel.add(textos, BorderLayout.WEST);
        return panel;
    }

    // ---------------------------------------------------------------- formulario

    private JPanel construirFormulario() {
        JPanel tarjeta = new JPanel(new BorderLayout(0, 14));
        tarjeta.setBackground(Color.WHITE);
        tarjeta.setBorder(BorderFactory.createCompoundBorder(
                new LineBorder(BORDE, 1, true),
                new EmptyBorder(16, 18, 16, 18)));

        JPanel campos = new JPanel(new GridBagLayout());
        campos.setOpaque(false);
        GridBagConstraints c = new GridBagConstraints();
        c.fill = GridBagConstraints.HORIZONTAL;
        c.insets = new Insets(0, 0, 0, 12);

        agregarCampo(campos, c, 0, "ID", txtId, 0.10);
        agregarCampo(campos, c, 1, "Nombre", txtNombre, 0.50);
        agregarCampo(campos, c, 2, "Precio", txtPrecio, 0.22);
        agregarCampo(campos, c, 3, "Stock", txtStock, 0.18);

        txtId.setEditable(false);
        txtId.setBackground(new Color(236, 240, 241));
        txtId.setForeground(GRIS);
        txtId.setToolTipText("Lo asigna la base de datos automaticamente");
        txtPrecio.setToolTipText("Puedes escribir 19000, 19.000 o $19.000");

        tarjeta.add(campos, BorderLayout.CENTER);
        tarjeta.add(construirBotones(), BorderLayout.SOUTH);
        return tarjeta;
    }

    private void agregarCampo(JPanel panel, GridBagConstraints c, int columna,
                              String etiqueta, JTextField campo, double peso) {
        JLabel lbl = new JLabel(etiqueta);
        lbl.setFont(FUENTE_NEGRITA);
        lbl.setForeground(TEXTO);
        lbl.setBorder(new EmptyBorder(0, 2, 5, 0));

        campo.setFont(FUENTE);
        campo.setHorizontalAlignment(SwingConstants.CENTER);
        campo.setBorder(BorderFactory.createCompoundBorder(
                new LineBorder(BORDE, 1, true),
                new EmptyBorder(7, 9, 7, 9)));

        c.gridx = columna;
        c.weightx = peso;
        c.gridy = 0;
        panel.add(lbl, c);
        c.gridy = 1;
        panel.add(campo, c);
    }

    private JPanel construirBotones() {
        JPanel panel = new JPanel(new FlowLayout(FlowLayout.LEFT, 10, 0));
        panel.setOpaque(false);
        panel.setBorder(new EmptyBorder(16, 0, 0, 0));

        panel.add(boton("Crear", VERDE, e -> crear()));
        panel.add(boton("Actualizar", AZUL, e -> actualizar()));
        panel.add(boton("Eliminar", ROJO, e -> eliminar()));
        panel.add(boton("Limpiar", GRIS, e -> limpiarFormulario()));
        panel.add(boton("Refrescar", AZUL_OSCURO, e -> cargarTabla()));
        return panel;
    }

    private BotonPlano boton(String texto, Color color, java.awt.event.ActionListener accion) {
        BotonPlano b = new BotonPlano(texto, color);
        b.addActionListener(accion);
        return b;
    }

    // --------------------------------------------------------------------- tabla

    private void prepararTabla() {
        tabla.setFont(FUENTE);
        tabla.setRowHeight(30);
        tabla.setShowVerticalLines(false);
        tabla.setGridColor(new Color(226, 232, 237));
        tabla.setSelectionBackground(SELECCION);
        tabla.setSelectionForeground(TEXTO);
        tabla.setForeground(TEXTO);
        tabla.setAutoCreateRowSorter(true);
        tabla.getTableHeader().setReorderingAllowed(false);
        tabla.setIntercellSpacing(new Dimension(0, 1));

        // Todos los valores centrados; el precio ademas con formato de pesos.
        tabla.setDefaultRenderer(Object.class, celdaCentrada(false));
        tabla.getColumnModel().getColumn(2).setCellRenderer(celdaCentrada(true));

        tabla.getColumnModel().getColumn(0).setPreferredWidth(60);
        tabla.getColumnModel().getColumn(1).setPreferredWidth(320);
        tabla.getColumnModel().getColumn(2).setPreferredWidth(140);
        tabla.getColumnModel().getColumn(3).setPreferredWidth(90);

        JTableHeader cabecera = tabla.getTableHeader();
        cabecera.setDefaultRenderer(new DefaultTableCellRenderer() {
            @Override
            public Component getTableCellRendererComponent(JTable t, Object valor, boolean sel,
                                                           boolean foco, int fila, int col) {
                JLabel lbl = (JLabel) super.getTableCellRendererComponent(t, valor, sel, foco, fila, col);
                lbl.setHorizontalAlignment(SwingConstants.CENTER);
                lbl.setOpaque(true);
                lbl.setBackground(AZUL_OSCURO);
                lbl.setForeground(Color.WHITE);
                lbl.setFont(new Font("Segoe UI", Font.BOLD, 13));
                lbl.setBorder(new EmptyBorder(10, 6, 10, 6));
                return lbl;
            }
        });
        cabecera.setPreferredSize(new Dimension(0, 38));
    }

    private DefaultTableCellRenderer celdaCentrada(boolean comoPrecio) {
        DefaultTableCellRenderer renderer = new DefaultTableCellRenderer() {
            @Override
            protected void setValue(Object value) {
                setText(comoPrecio ? formatearCelda(value) : value == null ? "" : value.toString());
            }
        };
        renderer.setHorizontalAlignment(SwingConstants.CENTER);
        return renderer;
    }

    private JScrollPane construirTabla() {
        JScrollPane scroll = new JScrollPane(tabla);
        scroll.setBorder(new LineBorder(BORDE, 1, true));
        scroll.getViewport().setBackground(Color.WHITE);
        return scroll;
    }

    private static String formatearCelda(Object valor) {
        return valor instanceof Number numero ? Moneda.formatear(numero.doubleValue()) : "";
    }

    // -------------------------------------------------------------- barra estado

    private JPanel construirBarraEstado() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBackground(new Color(236, 240, 241));
        panel.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createMatteBorder(1, 0, 0, 0, BORDE),
                new EmptyBorder(8, 22, 8, 22)));

        lblEstado.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        lblEstado.setForeground(GRIS);

        JLabel ayuda = new JLabel("Selecciona una fila para editarla o eliminarla");
        ayuda.setFont(new Font("Segoe UI", Font.ITALIC, 12));
        ayuda.setForeground(GRIS);

        panel.add(lblEstado, BorderLayout.WEST);
        panel.add(ayuda, BorderLayout.EAST);
        return panel;
    }

    // ------------------------------------------------------------------ acciones

    private void cargarTabla() {
        try {
            modeloTabla.setRowCount(0);
            for (Producto p : dao.listar()) {
                modeloTabla.addRow(new Object[]{p.getId(), p.getNombre(), p.getPrecio(), p.getStock()});
            }
            int total = modeloTabla.getRowCount();
            lblEstado.setText(total == 0
                    ? "Sin productos registrados"
                    : total + (total == 1 ? " producto registrado" : " productos registrados"));
        } catch (SQLException ex) {
            lblEstado.setText("Sin conexion a la base de datos");
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
            avisarDatosInvalidos();
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
            avisarDatosInvalidos();
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
        return Moneda.parsear(txtPrecio.getText());
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

    private void avisarDatosInvalidos() {
        JOptionPane.showMessageDialog(this,
                "Revisa el precio y el stock.\n\nPrecio: 19000, 19.000 o $19.000\nStock: un numero entero",
                "Datos invalidos", JOptionPane.WARNING_MESSAGE);
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
