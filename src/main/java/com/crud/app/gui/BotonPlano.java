package com.crud.app.gui;

import javax.swing.BorderFactory;
import javax.swing.JButton;
import java.awt.Color;
import java.awt.Cursor;
import java.awt.Font;
import java.awt.Graphics;
import java.awt.Graphics2D;
import java.awt.RenderingHints;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;

/**
 * Boton de color plano con esquinas redondeadas.
 *
 * Se dibuja a mano porque el Look and Feel de Windows ignora setBackground en los
 * botones: el color asignado no se ve. Pintando el fondo en paintComponent el
 * resultado es el mismo en cualquier sistema.
 */
class BotonPlano extends JButton {

    private final Color base;
    private final Color encimaColor;
    private final Color pulsadoColor;
    private boolean encima;
    private boolean pulsado;

    BotonPlano(String texto, Color base) {
        super(texto);
        this.base = base;
        this.encimaColor = mezclar(base, Color.WHITE, 0.15f);
        this.pulsadoColor = mezclar(base, Color.BLACK, 0.15f);

        setForeground(Color.WHITE);
        setFont(new Font("Segoe UI", Font.BOLD, 12));
        setBorder(BorderFactory.createEmptyBorder(9, 18, 9, 18));
        setCursor(new Cursor(Cursor.HAND_CURSOR));
        setContentAreaFilled(false);
        setBorderPainted(false);
        setFocusPainted(false);
        setOpaque(false);

        addMouseListener(new MouseAdapter() {
            @Override
            public void mouseEntered(MouseEvent e) {
                encima = true;
                repaint();
            }

            @Override
            public void mouseExited(MouseEvent e) {
                encima = false;
                pulsado = false;
                repaint();
            }

            @Override
            public void mousePressed(MouseEvent e) {
                pulsado = true;
                repaint();
            }

            @Override
            public void mouseReleased(MouseEvent e) {
                pulsado = false;
                repaint();
            }
        });
    }

    /** Mezcla dos colores; proporcion 0 devuelve el primero y 1 el segundo. */
    private static Color mezclar(Color a, Color b, float proporcion) {
        return new Color(
                (int) (a.getRed() + (b.getRed() - a.getRed()) * proporcion),
                (int) (a.getGreen() + (b.getGreen() - a.getGreen()) * proporcion),
                (int) (a.getBlue() + (b.getBlue() - a.getBlue()) * proporcion));
    }

    @Override
    protected void paintComponent(Graphics g) {
        Graphics2D g2 = (Graphics2D) g.create();
        g2.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);
        g2.setColor(pulsado ? pulsadoColor : encima ? encimaColor : base);
        g2.fillRoundRect(0, 0, getWidth(), getHeight(), 10, 10);
        g2.dispose();
        super.paintComponent(g);
    }
}
