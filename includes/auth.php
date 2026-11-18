<?php
declare(strict_types=1);

/**
 * Archivo: auth.php
 * DescripciÃ³n: Controla el acceso a las pÃ¡ginas
 * segÃºn la sesiÃ³n y el rol del usuario.
 */

// Iniciar la sesiÃ³n si todavÃ­a no estÃ¡ iniciada.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Comprueba que el usuario haya iniciado sesiÃ³n.
 *
 * Si no estÃ¡ autenticado, lo redirige al login.
 */
function requireLogin(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Comprueba que el usuario tenga el rol indicado.
 *
 * @param string $rol Rol necesario para acceder.
 */
function requireRole(string $rol): void
{
    requireLogin();

    if (($_SESSION['usuario_rol'] ?? '') !== $rol) {
        http_response_code(403);
        exit('No tienes permisos para acceder a esta pÃ¡gina.');
    }
}
