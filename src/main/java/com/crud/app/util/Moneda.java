package com.crud.app.util;

import java.text.DecimalFormat;
import java.text.DecimalFormatSymbols;
import java.util.Locale;

/**
 * Formato de pesos colombianos: punto para separar miles y coma para los decimales.
 *
 * Los separadores se fijan a mano en vez de dejarlos en manos del Locale porque el
 * resultado depende de la configuracion regional del equipo donde corra la app, y aqui
 * interesa que se vea igual en cualquier PC.
 */
public final class Moneda {

    private static final DecimalFormatSymbols SIMBOLOS;

    static {
        SIMBOLOS = new DecimalFormatSymbols(Locale.forLanguageTag("es-CO"));
        SIMBOLOS.setGroupingSeparator('.');
        SIMBOLOS.setDecimalSeparator(',');
    }

    private Moneda() {
    }

    /**
     * 19000 -> "$19.000" y 19000.5 -> "$19.000,50".
     *
     * Los precios exactos se muestran sin decimales, que es como se escriben los pesos
     * en la practica; los centavos solo aparecen cuando el valor realmente los tiene.
     */
    public static String formatear(double valor) {
        String patron = valor == Math.floor(valor) ? "$#,##0" : "$#,##0.00";
        return new DecimalFormat(patron, SIMBOLOS).format(valor);
    }

    /**
     * Convierte a numero lo que el usuario escriba en el formulario, tolerando el simbolo
     * de peso y los separadores: "$19.000", "19.000" y "19000" dan todos 19000.
     *
     * @throws NumberFormatException si el texto no representa un numero
     */
    public static double parsear(String texto) {
        if (texto == null) {
            throw new NumberFormatException("El precio esta vacio.");
        }
        String limpio = texto.trim()
                .replace("$", "")
                .replace(" ", "")
                .replace("\u00A0", "");  // espacio duro, aparece al copiar y pegar precios
        if (limpio.isEmpty()) {
            throw new NumberFormatException("El precio esta vacio.");
        }

        if (limpio.contains(",")) {
            // Formato colombiano completo: el punto agrupa miles y la coma separa decimales.
            limpio = limpio.replace(".", "").replace(',', '.');
        } else {
            // Sin coma el punto es ambiguo. Con tres digitos detras es separador de miles
            // ("19.000" son diecinueve mil); en cualquier otro caso es decimal ("19000.50").
            int ultimoPunto = limpio.lastIndexOf('.');
            if (ultimoPunto > 0 && limpio.length() - ultimoPunto - 1 == 3) {
                limpio = limpio.replace(".", "");
            }
        }

        return Double.parseDouble(limpio);
    }
}
