/**
 * AulaFix - nueva.js
 * Contador de caracteres y borrador del formulario.
 */

const CLAVE_BORRADOR = "aulafix_borrador";

/**
 * Inicializa el formulario de nueva incidencia.
 */
export function iniciarFormulario() {
    const formulario = document.querySelector("#form-nueva");
    if (!formulario) return;

    const descripcion = formulario.querySelector("#descripcion");
    const contador = formulario.querySelector("#contador");

    if (!descripcion || !contador) return;

    // Recuperar el borrador guardado.
    try {
        const borrador = localStorage.getItem(CLAVE_BORRADOR);

        if (borrador !== null && descripcion.value === "") {
            descripcion.value = borrador;
        }
    } catch (error) {
        console.warn("No se pudo recuperar el borrador:", error);
    }

    /**
     * Actualiza el contador de caracteres.
     */
    function actualizarContador() {
        contador.textContent =
            `${descripcion.value.length} caracteres`;
    }

    actualizarContador();

    descripcion.addEventListener("input", () => {
        actualizarContador();

        try {
            localStorage.setItem(
                CLAVE_BORRADOR,
                descripcion.value
            );
        } catch (error) {
            console.warn("No se pudo guardar el borrador:", error);
        }
    });
}

/**
 * Elimina el borrador cuando se confirma el guardado.
 */
export function borrarBorrador() {
    try {
        localStorage.removeItem(CLAVE_BORRADOR);
    } catch (error) {
        console.warn("No se pudo borrar el borrador:", error);
    }
}