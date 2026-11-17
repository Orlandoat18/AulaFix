<?php

declare(strict_types=1);

/**
 * Archivo: cabecera.php
 * Descripción: Contiene la estructura HTML inicial
 * compartida por las páginas de AulaFix.
 */

// Cargar las funciones comunes.
require_once __DIR__ . '/funciones.php';

// Título predeterminado.
$titulo = $titulo ?? 'AulaFix';

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
</header>
<main>