<?php
declare(strict_types=1);

/**
 * Archivo: cabecera.php
 * Descripción: Estructura HTML inicial y navegación
 * compartida por las páginas de AulaFix.
 */

require_once __DIR__ . '/funciones.php';

// Iniciar la sesión si todavía no está iniciada.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Crear el token CSRF si todavía no existe.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Título de la página.
$titulo = $titulo ?? 'AulaFix';

// Comprobar si existe un usuario conectado.
$usuarioConectado = isset($_SESSION['usuario_id']);

header('Content-Type: text/html; charset=UTF-8');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> | AulaFix</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body>

    <header>
        <h1>AulaFix</h1>

        <nav>
                <?php if ($usuarioConectado): ?>

                <a href="incidencias.php">Incidencias</a>
                <a href="nueva.php">Nueva incidencia</a>

                <span>
                        <?= e($_SESSION['usuario_nombre'] ?? '') ?>
                    (<?= e($_SESSION['usuario_rol'] ?? '') ?>)
                </span>

                <form action="logout.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">

                    <button type="submit">Cerrar sesión</button>
                </form>

                <?php else: ?>

                <a href="login.php">Iniciar sesión</a>
                <a href="registro.php">Registrarse</a>

                <?php endif; ?>
        </nav>
    </header>

    <main>