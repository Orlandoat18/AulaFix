/**
 * AulaFix - vistas.js
 * Funciones para filtrar, ordenar y resumir incidencias.
 */

/**
 * Filtra las incidencias por estado, prioridad y búsqueda.
 * @param {Array} incidencias - Lista de incidencias.
 * @param {Object} filtros - Filtros seleccionados.
 * @returns {Array} - Incidencias filtradas.
 */
export function filtrarIncidencias(incidencias, filtros = {}) {
    const {
        estado = "todas",
        prioridad = "todas",
        busqueda = ""
    } = filtros;

    const texto = busqueda.trim().toLowerCase();

    return incidencias.filter(incidencia => {
        const coincideEstado =
            estado === "todas" || incidencia.estado === estado;

        const coincidePrioridad =
            prioridad === "todas" || incidencia.prioridad === prioridad;

        const contenido = [
            incidencia.equipo,
            incidencia.descripcion,
            incidencia.autor
        ].join(" ").toLowerCase();

        const coincideBusqueda = contenido.includes(texto);

        return coincideEstado && coincidePrioridad && coincideBusqueda;
    });
}

/**
 * Ordena las incidencias sin modificar la lista original.
 * @param {Array} incidencias - Lista de incidencias.
 * @param {string} campo - fecha, equipo o prioridad.
 * @param {string} direccion - asc o desc.
 * @returns {Array} Incidencias ordenadas.
 */
export function ordenarIncidencias(
    incidencias,
    campo = "fecha",
    direccion = "desc"
) {
    const prioridades = { baja: 1, media: 2, alta: 3 };

    return [...incidencias].sort((a, b) => {
        let resultado = 0;

        if (campo === "fecha") {
            resultado = new Date(a.creada_en) - new Date(b.creada_en);
        } else if (campo === "equipo") {
            resultado = a.equipo.localeCompare(b.equipo, "es");
        } else if (campo === "prioridad") {
            resultado = prioridades[a.prioridad] - prioridades[b.prioridad];
        }

        return direccion === "asc" ? resultado : -resultado;
    });
}

/**
 * Calcula los contadores del panel.
 * @param {Array} incidencias - Lista de incidencias.
 * @returns {Object} Resumen de incidencias.
 */
export function calcularResumen(incidencias) {
    return incidencias.reduce((resumen, incidencia) => {
        if (incidencia.estado === "abierta") {
            resumen.abiertas++;
        }

        if (incidencia.estado === "en_curso") {
            resumen.enCurso++;
        }

        if (incidencia.estado === "resuelta") {
            resumen.resueltas++;
        }

        if (incidencia.prioridad === "alta" &&
            incidencia.estado !== "resuelta") {
            resumen.urgentes++;
        }

        return resumen;
    }, {
        abiertas: 0,
        enCurso: 0,
        resueltas: 0,
        urgentes: 0
    });
}