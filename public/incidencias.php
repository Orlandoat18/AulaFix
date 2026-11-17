<?php
declare(strict_types=1);

/**
 * Archivo: incidencias.php
 * DescripciÃ³n: Obtiene y muestra las incidencias registradas
 * en la base de datos de AulaFix.
 */

// Comprobar que el usuario ha iniciado sesiÃ³n.
require_once dirname(__DIR__) . '/includes/auth.php';
requireLogin();

// Cargar conexiÃ³n PDO y funciones comunes.
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// TÃ­tulo de la pÃ¡gina.
$titulo = 'Listado de incidencias';

/**
 * Consultar las incidencias junto con el equipo y el usuario
 * que las ha registrado.
 */
$consulta = $pdo->prepare("
    SELECT
        i.id,
        e.codigo AS equipo,
        i.categoria,
        i.prioridad,
        i.descripcion,
        i.estado,
        u.nombre AS autor,
        i.creada_en
    FROM incidencias AS i
    INNER JOIN equipos AS e
        ON i.equipo_id = e.id
    INNER JOIN usuarios AS u
        ON i.usuario_id = u.id
    ORDER BY i.creada_en DESC, i.id DESC
");

$consulta->execute();

$filas = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Preparar los datos para el frontend.
$zonaHoraria = new DateTimeZone('Europe/Madrid');

$incidencias = [];

foreach ($filas as $fila) {
    $fecha = new DateTimeImmutable(
        $fila['creada_en'],
        $zonaHoraria
    );

    $incidencias[] = [
        'id' => (int) $fila['id'],
        'equipo' => $fila['equipo'],
        'categoria' => $fila['categoria'],
        'prioridad' => $fila['prioridad'],
        'descripcion' => $fila['descripcion'],
        'estado' => $fila['estado'],
        'autor' => $fila['autor'],
        'creada_en' => $fecha->format(DATE_ATOM)
    ];
}

// Convertir los registros a JSON de forma segura.
$jsonIncidencias = json_encode(
    $incidencias,
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_AMP |
    JSON_HEX_QUOT |
    JSON_UNESCAPED_UNICODE |
    JSON_THROW_ON_ERROR
);

// Incluir la cabecera comÃºn.
require_once dirname(__DIR__) . '/includes/cabecera.php';
?>

<section class="panel-incidencias">

    <h1>Panel de incidencias</h1>

    <p>
        Consulta y gestiona las incidencias registradas
        en los equipos del aula.
    </p>

    <!-- Resumen de incidencias -->
    <section id="resumen-incidencias">

        <h2>Resumen</h2>

        <div>
            <p>Abiertas</p>
            <span id="total-abiertas">0</span>
        </div>

        <div>
            <p>En curso</p>
            <span id="total-en-curso">0</span>
        </div>

        <div>
            <p>Resueltas</p>
            <span id="total-resueltas">0</span>
        </div>

        <div>
            <p>Urgentes</p>
            <span id="total-urgentes">0</span>
        </div>

        <div>
            <p>Total de incidencias</p>
            <span id="total-incidencias">0</span>
        </div>

    </section>

    <!-- Acceso al formulario de creaciÃ³n -->
    <p>
        <a href="nueva.php">Registrar nueva incidencia</a>
    </p>

    <!-- Filtros y bÃºsqueda -->
    <section class="filtros-incidencias">

        <h2>Buscar y filtrar</h2>

        <label for="busqueda-incidencias">
            Buscar incidencia:
        </label>

        <input type="search" id="busqueda-incidencias" placeholder="Buscar por equipo, autor o descripciÃ³n...">

        <label for="filtro-estado">
            Filtrar por estado:
        </label>

        <select id="filtro-estado">
            <option value="todos">Todos los estados</option>
            <option value="abierta">Abierta</option>
            <option value="en_curso">En curso</option>
            <option value="resuelta">Resuelta</option>
        </select>

        <label for="filtro-prioridad">
            Filtrar por prioridad:
        </label>

        <select id="filtro-prioridad">
            <option value="todos">Todas las prioridades</option>
            <option value="alta">Alta</option>
            <option value="media">Media</option>
            <option value="baja">Baja</option>
        </select>

    </section>

    <!-- OrdenaciÃ³n -->
    <section class="orden-incidencias">

        <h2>Ordenar incidencias</h2>

        <label for="orden-campo">
            Ordenar por:
        </label>

        <select id="orden-campo">
            <option value="fecha">Fecha</option>
            <option value="equipo">Equipo</option>
            <option value="prioridad">Prioridad</option>
        </select>

        <label for="orden-direccion">
            Dirección:
        </label>

        <select id="orden-direccion">
            <option value="desc">Descendente</option>
            <option value="asc">Ascendente</option>
        </select>

    </section>

    <!-- Tabla de incidencias -->
    <section class="tabla-incidencias">

        <h2>Incidencias registradas</h2>

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Equipo</th>
                    <th>Categor&#237;a</th>
                    <th>Prioridad</th>
                    <th>Descripci&#243;n</th>
                    <th>Estado</th>
                    <th>Autor</th>
                    <th>Fecha</th>
                </tr>
            </thead>

            <tbody id="tabla-incidencias">
                <?php foreach ($incidencias as $incidencia): ?>
                    <tr>
                        <td>
                            <a href="incidencia.php?id=<?= (int) $incidencia['id'] ?>">
                                <?= (int) $incidencia['id'] ?>
                            </a>
                        </td>

                        <td><?= e($incidencia['equipo']) ?></td>
                        <td><?= e($incidencia['categoria']) ?></td>
                        <td><?= e($incidencia['prioridad']) ?></td>
                        <td><?= e($incidencia['descripcion']) ?></td>
                        <td><?= e($incidencia['estado']) ?></td>
                        <td><?= e($incidencia['autor']) ?></td>
                        <td><?= e($incidencia['creada_en']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($incidencias) === 0): ?>
                    <tr>
                        <td colspan="8">
                            Todav&iacute;a no hay incidencias registradas.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>

    </section>

</section>

<!-- Datos de PHP disponibles para JavaScript -->
<script id="datos-incidencias" type="application/json"><?= $jsonIncidencias ?></script>

<!-- MÃ³dulo principal del frontend -->
<script type="module" src="js/main.js"></script>

<?php
// Incluir el pie comÃºn de la pÃ¡gina.
require_once dirname(__DIR__) . '/includes/pie.php';
?>