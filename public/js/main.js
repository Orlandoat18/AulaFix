/**
 * AulaFix - main.js
 * Archivo principal del frontend.
 */

import { Incidencia } from "./incidencia.js";
import {
    filtrarIncidencias,
    ordenarIncidencias,
    calcularResumen
} from "./vistas.js";

import {
    formatearFecha,
    tiempoRelativo
} from "./fechas.js";

import {
    guardarPreferencias,
    cargarPreferencias
} from "./preferencias.js";

/**
 * Lee las incidencias enviadas por PHP.
 * @returns {Array} Lista de objetos Incidencia.
 */
export function cargarIncidencias() {
    const elemento = document.querySelector("#datos-incidencias");

    if (!elemento) return [];

    try {
        const datos = JSON.parse(elemento.textContent);

        if (!Array.isArray(datos)) return [];

        return datos.map(dato => new Incidencia(dato));
    } catch (error) {
        console.error("Error al cargar incidencias:", error);
        return [];
    }
}

/**
 * Inicializa el panel cuando el HTML está preparado.
 */
function iniciarPanel() {
    const incidencias = cargarIncidencias();

    const resumen = calcularResumen(incidencias);

    console.log("Incidencias cargadas:", incidencias.length);
    console.log("Resumen:", resumen);
}

if (typeof document !== "undefined") {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", iniciarPanel);
    } else {
        iniciarPanel();
    }
}