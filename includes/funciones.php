<?php
declare(strict_types=1);


/**
 * Escapa un valor antes de mostrarlo en HTML.
 * Evita que contenido introducido por usuarios pueda
 * interpretarse como código HTML o JavaScript (XSS).
 *
 * @param mixed $valor Valor que se desea mostrar.
 * @return string Texto seguro para HTML.
 */
function e(mixed $valor): string
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}
