/**
 * AulaFix - preferencias.js
 * Guarda y recupera las preferencias del panel
 * utilizando localStorage.
 */

const CLAVE = "aulafix_preferencias";

/**
 * Guarda las preferencias del usuario.
 *
 * @param {Object} preferencias - Filtros y orden seleccionados.
 * @returns {boolean} true si se han guardado correctamente.
 */
export function guardarPreferencias(preferencias) {
    try {
        localStorage.setItem(CLAVE, JSON.stringify(preferencias));
        return true;
    } catch (error) {
        console.warn("No se pudieron guardar las preferencias:", error);
        return false;
    }
}

/**
 * Recupera las preferencias guardadas.
 *
 * @returns {Object|null} Preferencias o null si no existen.
 */
export function cargarPreferencias() {
    try {
        const datos = localStorage.getItem(CLAVE);

        if (datos === null) {
            return null;
        }

        const preferencias = JSON.parse(datos);

        if (preferencias === null ||
            typeof preferencias !== "object" ||
            Array.isArray(preferencias)) {
            return null;
        }

        return preferencias;
    } catch (error) {
        console.warn("No se pudieron recuperar las preferencias:", error);
        return null;
    }
}

/**
 * Elimina las preferencias guardadas.
 *
 * @returns {boolean} true si se han eliminado correctamente.
 */
export function borrarPreferencias() {
    try {
        localStorage.removeItem(CLAVE);
        return true;
    } catch (error) {
        console.warn("No se pudieron borrar las preferencias:", error);
        return false;
    }
}