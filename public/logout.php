<?php
declare(strict_types=1);

/**
 * Archivo: logout.php
 * DescripciÃ³n: Cierra la sesiÃ³n del usuario de forma segura.
 * Comprueba el token CSRF, destruye la sesiÃ³n y redirige
 * al usuario a la pÃ¡gina de inicio de sesiÃ³n.
 */

// Iniciar la sesiÃ³n actual.
session_start();

// Permitir Ãºnicamente solicitudes POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('MÃ©todo no permitido.');
}

// Recuperar el token enviado desde el formulario.
$token = $_POST['csrf_token'] ?? '';

// Comprobar que el token CSRF es vÃ¡lido.
if (
    !is_string($token) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);
    exit('Solicitud no vÃ¡lida. No se pudo cerrar la sesiÃ³n.');
}

// Obtener los parÃ¡metros de la cookie de sesiÃ³n.
$parametrosCookie = session_get_cookie_params();

// Eliminar la cookie de sesiÃ³n del navegador.
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $parametrosCookie['path'],
    'domain' => $parametrosCookie['domain'],
    'secure' => $parametrosCookie['secure'],
    'httponly' => $parametrosCookie['httponly'],
    'samesite' => $parametrosCookie['samesite'] ?? 'Lax'
]);

// Eliminar todos los datos almacenados en la sesiÃ³n.
$_SESSION = [];

// Destruir la sesiÃ³n en el servidor.
session_destroy();

// Redirigir al usuario al inicio de sesiÃ³n.
header('Location: login.php');
exit;
