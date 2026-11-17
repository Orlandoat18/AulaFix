<?php

declare(strict_types=1);

/**
 * Archivo: incidencias.php
 * Descripción: Muestra el listado de incidencias de AulaFix.
 */

// Título de la página.
$titulo = 'Listado de incidencias';

// Incluir la cabecera común.
require_once dirname(__DIR__) . '/includes/cabecera.php';

// Datos de ejemplo de las incidencias.
$incidencias = [
    [
        'id' => 1,
        'equipo' => 'PC-01',
        'categoria' => 'hardware',
        'prioridad' => 'alta',
        'descripcion' => 'El ordenador no arranca.',
        'estado' => 'abierta',
        'autor' => 'Lucía Alumna',
        'creada_en' => '2026-11-16 09:30:00'
    ],
    [
        'id' => 2,
        'equipo' => 'PROY-01',
        'categoria' => 'hardware',
        'prioridad' => 'media',
        'descripcion' => 'El proyector se apaga solo.',
        'estado' => 'en_curso',
        'autor' => 'Carlos Profesor',
        'creada_en' => '2026-11-16 10:15:00'
    ],
    [
        'id' => 3,
        'equipo' => 'IMP-01',
        'categoria' => 'otro',
        'prioridad' => 'baja',
        'descripcion' => 'La impresora necesita papel.',
        'estado' => 'resuelta',
        'autor' => 'Lucía Alumna',
        'creada_en' => '2026-11-17 08:45:00'
    ]
];

    // Convertir las incidencias a JSON de forma segura.
    $jsonIncidencias = json_encode(
        $incidencias,
         JSON_HEX_TAG |
         JSON_HEX_APOS |
         JSON_HEX_AMP |
         JSON_HEX_QUOT |
         JSON_UNESCAPED_UNICODE |
         JSON_THROW_ON_ERROR
     );

    ?>

<h2>Listado de incidencias</h2>

<p>Bienvenido al panel de incidencias de AulaFix.</p>

<!-- Datos JSON para el módulo JavaScript. -->
<script id="datos-incidencias" type="application/json"><?= $jsonIncidencias ?></script>

<!-- Cargar el módulo principal de JavaScript. -->
<script type="module" src="js/main.js"></script>

<h3>Incidencias registradas</h3>

<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Equipo</th>
            <th>Categoría</th>
            <th>Prioridad</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Autor</th>
            <th>Fecha</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($incidencias as $incidencia): ?>
            <tr>
                <td><?= e($incidencia['id']) ?></td>
                <td><?= e($incidencia['equipo']) ?></td>
                <td><?= e($incidencia['categoria']) ?></td>
                <td><?= e($incidencia['prioridad']) ?></td>
                <td><?= e($incidencia['descripcion']) ?></td>
                <td><?= e($incidencia['estado']) ?></td>
                <td><?= e($incidencia['autor']) ?></td>
                <td><?= e($incidencia['creada_en']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php

// Incluir el pie común.
require_once dirname(__DIR__) . '/includes/pie.php';
