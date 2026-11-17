/**
 * AulaFix - fechas.js
 * Funciones para mostrar fechas en español.
 */

/**
 * Convierte una fecha ISO a una fecha completa en español.
 *
 * @param {string} fechaISO - Fecha original.
 * @returns {string} Fecha formateada.
 */
export function formatearFecha(fechaISO) {
    const fecha = new Date(fechaISO);

    if (Number.isNaN(fecha.getTime())) {
        return "Fecha no válida";
    }

    return new Intl.DateTimeFormat("es-ES", {
        dateStyle: "long",
        timeStyle: "short"
    }).format(fecha);
}

/**
 * Calcula cuánto tiempo ha pasado desde una fecha.
 *
 * @param {string} fechaISO - Fecha original.
 * @returns {string} Tiempo relativo.
 */
export function tiempoRelativo(fechaISO) {
    const fecha = new Date(fechaISO);

    if (Number.isNaN(fecha.getTime())) {
        return "Fecha no válida";
    }

    const diferencia = fecha.getTime() - Date.now();
    const minutos = Math.round(diferencia / 60000);
    const horas = Math.round(diferencia / 3600000);
    const dias = Math.round(diferencia / 86400000);

    const formato = new Intl.RelativeTimeFormat("es-ES", {
        numeric: "auto"
    });

    if (Math.abs(minutos) < 60) {
        return formato.format(minutos, "minute");
    }

    if (Math.abs(horas) < 24) {
        return formato.format(horas, "hour");
    }

    return formato.format(dias, "day");
}