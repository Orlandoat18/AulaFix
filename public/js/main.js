
/**
 * AulaFix - main.js
 * Archivo principal del frontend.
 * Gestiona el panel interactivo de incidencias.
 */

import { Incidencia } from "./incidencia.js";

import {
    filtrarIncidencias,
    ordenarIncidencias,
    calcularResumen
} from "./vistas.js";

import { formatearFecha } from "./fechas.js";

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
 * Inicializa el panel de incidencias.
 */
function iniciarPanel() {

    const incidencias = cargarIncidencias();
    const preferencias = cargarPreferencias();

    // Obtener los controles del panel.
    const filtroEstado = document.querySelector("#filtro-estado");
    const filtroPrioridad = document.querySelector("#filtro-prioridad");
    const busqueda = document.querySelector("#busqueda-incidencias");

    const ordenCampo = document.querySelector("#orden-campo");
    const ordenDireccion = document.querySelector("#orden-direccion");

    const tabla = document.querySelector("#tabla-incidencias");

    // Comprobar que existen los elementos necesarios.
    if (
        !filtroEstado ||
        !filtroPrioridad ||
        !busqueda ||
        !ordenCampo ||
        !ordenDireccion ||
        !tabla
    ) {
        console.error("No se encontraron los controles del panel.");
        return;
    }

    /**
     * Recuperar preferencias y evitar valores vacíos
     * o incompatibles con las opciones de los select.
     */
    function aplicarPreferencia(select, valor, valorDefecto) {
        const valorValido = Array.from(select.options)
            .some(opcion => opcion.value === valor);

        select.value = valorValido ? valor : valorDefecto;
    }

    if (preferencias) {
        aplicarPreferencia(
            filtroEstado,
            preferencias.estado,
            "todas"
        );

        aplicarPreferencia(
            filtroPrioridad,
            preferencias.prioridad,
            "todas"
        );

        busqueda.value = preferencias.busqueda ?? "";

        aplicarPreferencia(
            ordenCampo,
            preferencias.ordenCampo,
            "fecha"
        );

        aplicarPreferencia(
            ordenDireccion,
            preferencias.ordenDireccion,
            "desc"
        );
    }

    // Garantizar valores iniciales válidos.
    if (!filtroEstado.value) {
        filtroEstado.value = "todas";
    }

    if (!filtroPrioridad.value) {
        filtroPrioridad.value = "todas";
    }

    /**
     * Actualiza la tabla aplicando filtros y ordenación.
     */
    function actualizarTabla() {

        const filtros = {
    estado: filtroEstado.value === "todos"
        ? "todas"
        : filtroEstado.value,

    prioridad: filtroPrioridad.value === "todos"
        ? "todas"
        : filtroPrioridad.value,

    busqueda: busqueda.value
};

        // Filtrar las incidencias.
        const incidenciasFiltradas = filtrarIncidencias(
            incidencias,
            filtros
        );

        // Ordenar las incidencias filtradas.
        const incidenciasOrdenadas = ordenarIncidencias(
            incidenciasFiltradas,
            ordenCampo.value,
            ordenDireccion.value
        );

        // Limpiar la tabla.
        tabla.innerHTML = "";

        // Crear una fila por incidencia.
        incidenciasOrdenadas.forEach(incidencia => {

            const fila = document.createElement("tr");

            // Crear enlace al detalle de la incidencia.
            const celdaId = document.createElement("td");
            const enlaceDetalle = document.createElement("a");

            enlaceDetalle.href =
                `incidencia.php?id=${encodeURIComponent(incidencia.id)}`;

            enlaceDetalle.textContent = incidencia.id;

            enlaceDetalle.setAttribute(
                "aria-label",
                `Ver detalle de la incidencia ${incidencia.id}`
            );

            celdaId.appendChild(enlaceDetalle);
            fila.appendChild(celdaId);

            // Preparar los datos de las demás columnas.
            const valores = [
                incidencia.equipo,
                incidencia.categoria,
                incidencia.prioridad,
                incidencia.descripcion,
                incidencia.estado,
                incidencia.autor,
                formatearFecha(incidencia.creada_en)
            ];

            // Crear las celdas de forma segura.
            valores.forEach(valor => {

                const celda = document.createElement("td");

                celda.textContent = valor ?? "";

                fila.appendChild(celda);
            });

            tabla.appendChild(fila);
        });

        // Mostrar un mensaje cuando no hay resultados.
        if (incidenciasOrdenadas.length === 0) {
            const fila = document.createElement("tr");
            const celda = document.createElement("td");

            celda.colSpan = 8;
            celda.textContent = "No se encontraron incidencias.";

            fila.appendChild(celda);
            tabla.appendChild(fila);
        }
    }

    /**
     * Guarda las preferencias actuales del usuario.
     */
    function guardarPreferenciasActuales() {

        guardarPreferencias({
            estado: filtroEstado.value || "todas",
            prioridad: filtroPrioridad.value || "todas",
            busqueda: busqueda.value,
            ordenCampo: ordenCampo.value,
            ordenDireccion: ordenDireccion.value
        });
    }

    // Escuchar los cambios en los filtros.
    filtroEstado.addEventListener("change", () => {
        actualizarTabla();
        guardarPreferenciasActuales();
    });

    filtroPrioridad.addEventListener("change", () => {
        actualizarTabla();
        guardarPreferenciasActuales();
    });

    // Escuchar los cambios en la ordenación.
    ordenCampo.addEventListener("change", () => {
        actualizarTabla();
        guardarPreferenciasActuales();
    });

    ordenDireccion.addEventListener("change", () => {
        actualizarTabla();
        guardarPreferenciasActuales();
    });

    // Actualizar la búsqueda mientras se escribe.
    busqueda.addEventListener("input", () => {
        actualizarTabla();
        guardarPreferenciasActuales();
    });

    // Mostrar inicialmente las incidencias.
    actualizarTabla();

    // Calcular el resumen general.
    const resumen = calcularResumen(incidencias);

    // Mostrar los contadores.
    const totalAbiertas = document.querySelector("#total-abiertas");
    const totalEnCurso = document.querySelector("#total-en-curso");
    const totalResueltas = document.querySelector("#total-resueltas");
    const totalUrgentes = document.querySelector("#total-urgentes");
    const totalIncidencias = document.querySelector("#total-incidencias");

    if (totalAbiertas) {
        totalAbiertas.textContent = resumen.abiertas;
    }

    if (totalEnCurso) {
        totalEnCurso.textContent = resumen.enCurso;
    }

    if (totalResueltas) {
        totalResueltas.textContent = resumen.resueltas;
    }

    if (totalUrgentes) {
        totalUrgentes.textContent = resumen.urgentes;
    }

    if (totalIncidencias) {
        totalIncidencias.textContent = incidencias.length;
    }

    console.log("Incidencias cargadas:", incidencias.length);
    console.log("Resumen:", resumen);
}

// Ejecutar el panel cuando el documento esté preparado.
if (typeof document !== "undefined") {

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", iniciarPanel);
    } else {
        iniciarPanel();
    }
}
