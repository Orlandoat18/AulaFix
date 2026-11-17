/**
 * AulaFix - incidencia.js
 * Clase que representa una incidencia informática.
 */

export class Incidencia {
    /**
     * Crea una incidencia a partir de los datos del servidor.
     *
     * @param {Object} datos - Datos de la incidencia.
     */
    constructor(datos) {
        this.id = datos.id;
        this.equipo = datos.equipo;
        this.categoria = datos.categoria;
        this.prioridad = datos.prioridad;
        this.descripcion = datos.descripcion;
        this.estado = datos.estado;
        this.autor = datos.autor;
        this.creada_en = datos.creada_en;
    }

    /**
     * Indica si una incidencia es urgente.
     *
     * @returns {boolean} true si es de prioridad alta
     * y todavía no está resuelta.
     */
    get esUrgente() {
        return this.prioridad === "alta" &&
               this.estado !== "resuelta";
    }

    /**
     * Calcula los días transcurridos desde su creación.
     *
     * @returns {number} Días transcurridos.
     */
    get diasAbierta() {
        const fecha = new Date(this.creada_en);

        if (Number.isNaN(fecha.getTime())) {
            return 0;
        }

        const diferencia = Date.now() - fecha.getTime();

        return Math.max(0, Math.floor(diferencia / 86400000));
    }
}