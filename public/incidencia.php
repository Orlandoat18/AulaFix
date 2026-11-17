<?php
declare(strict_types=1);

/**
 * Archivo: incidencia.php
 * DescripciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n: Muestra el detalle de una incidencia,
 * su historial y las acciones permitidas segÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºn el rol.
 */

// Comprobar que el usuario ha iniciado sesiÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n.
require_once dirname(__DIR__) . '/includes/auth.php';
header('Content-Type: text/html; charset=UTF-8');
requireLogin();

// Cargar la conexiÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n y las funciones comunes.
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// Obtener y validar el identificador de la incidencia.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Identificador de incidencia no vÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡lido.');
}

// Consultar los datos de la incidencia.
$consulta = $pdo->prepare(
    'SELECT i.*, e.codigo AS equipo_codigo,
            u.nombre AS autor_nombre
     FROM incidencias i
     INNER JOIN equipos e ON i.equipo_id = e.id
     INNER JOIN usuarios u ON i.usuario_id = u.id
     WHERE i.id = :id'
);

$consulta->execute(['id' => $id]);
$incidencia = $consulta->fetch();

// Comprobar que la incidencia existe.
if (!$incidencia) {
    http_response_code(404);
    exit('La incidencia no existe.');
}

// Consultar el historial de cambios.
$consultaHistorial = $pdo->prepare(
    'SELECT h.*, u.nombre AS usuario_nombre
     FROM historial h
     INNER JOIN usuarios u ON h.usuario_id = u.id
     WHERE h.incidencia_id = :id
     ORDER BY h.creado_en DESC'
);

$consultaHistorial->execute(['id' => $id]);
$historial = $consultaHistorial->fetchAll();

// Obtener los datos del usuario conectado.
$usuarioId = (int) $_SESSION['usuario_id'];
$rol = $_SESSION['usuario_rol'] ?? 'alumno';

// Comprobar el rol y la propiedad de la incidencia.
$esProfesor = $rol === 'profesor';
$esPropia = (int) $incidencia['usuario_id'] === $usuarioId;

// El profesor puede borrar todas.
// El alumno solo puede borrar las suyas si estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡n abiertas.
$puedeBorrar = $esProfesor || (
    $esPropia && $incidencia['estado'] === 'abierta'
);

$mensaje = '';
$error = '';

// Procesar los formularios enviados mediante POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificar el token CSRF.
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Solicitud no vÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡lida. Recarga la pÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡gina.');
    }

    $accion = $_POST['accion'] ?? '';

    // CAMBIAR ESTADO: solo puede hacerlo el profesor.
    if ($accion === 'cambiar_estado') {

        if (!$esProfesor) {
            http_response_code(403);
            exit('No tienes permiso para cambiar el estado.');
        }

        $nuevoEstado = $_POST['estado'] ?? '';
        $comentario = trim($_POST['comentario'] ?? '');

        $estadosPermitidos = [
            'abierta',
            'en_curso',
            'resuelta'
        ];

        if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
            $error = 'El estado seleccionado no es vÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡lido.';
        } elseif (
            $comentario === '' ||
            mb_strlen($comentario) > 500
        ) {
            $error = 'El comentario es obligatorio y no puede superar los 500 caracteres.';
        } elseif ($nuevoEstado === $incidencia['estado']) {
            $error = 'La incidencia ya tiene ese estado.';
        } else {

            try {
                // Actualizar la incidencia y guardar el historial
                // dentro de una misma transacciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n.
                $pdo->beginTransaction();

                $actualizar = $pdo->prepare(
                    'UPDATE incidencias
                     SET estado = :estado,
                         actualizada_en = NOW()
                     WHERE id = :id'
                );

                $actualizar->execute([
                    'estado' => $nuevoEstado,
                    'id' => $id
                ]);

                $insertarHistorial = $pdo->prepare(
                    'INSERT INTO historial
                    (incidencia_id, usuario_id, estado_anterior,
                     estado_nuevo, comentario, creado_en)
                    VALUES
                    (:incidencia_id, :usuario_id, :estado_anterior,
                     :estado_nuevo, :comentario, NOW())'
                );

                $insertarHistorial->execute([
                    'incidencia_id' => $id,
                    'usuario_id' => $usuarioId,
                    'estado_anterior' => $incidencia['estado'],
                    'estado_nuevo' => $nuevoEstado,
                    'comentario' => $comentario
                ]);

                $pdo->commit();

                header(
                    'Location: incidencia.php?id=' . $id .
                    '&resultado=actualizada'
                );
                exit;

            } catch (Throwable $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log($e->getMessage());
                $error = 'No se pudo actualizar la incidencia.';
            }
        }

    // ELIMINAR: segÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºn los permisos del usuario.
    } elseif ($accion === 'eliminar') {

        // Volver a comprobar los permisos en el servidor.
        if (!$puedeBorrar) {
            http_response_code(403);
            exit('No tienes permiso para eliminar esta incidencia.');
        }

        try {
            $eliminar = $pdo->prepare(
                'DELETE FROM incidencias WHERE id = :id'
            );

            $eliminar->execute(['id' => $id]);

            header('Location: incidencias.php?resultado=eliminada');
            exit;

        } catch (PDOException $e) {

            error_log($e->getMessage());
            $error = 'No se pudo eliminar la incidencia.';
        }

    } else {
        http_response_code(400);
        exit('AcciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n no vÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡lida.');
    }
}

// Mostrar el resultado despuÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©s de una redirecciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n.
if (($_GET['resultado'] ?? '') === 'actualizada') {
    $mensaje = 'El estado se ha actualizado correctamente.';
}

// Definir el tÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tulo y cargar la cabecera.
$titulo = 'Detalle de incidencia';
require_once dirname(__DIR__) . '/includes/cabecera.php';
?>

<h2>Detalle de incidencia</h2>

<?php if ($mensaje !== ''): ?>
    <p role="status"><?= e($mensaje) ?></p>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <p role="alert"><?= e($error) ?></p>
<?php endif; ?>

<section>
    <h3>Informaci&#243;n general</h3>

    <p><strong>ID:</strong> <?= e($incidencia['id']) ?></p>

    <p><strong>Equipo:</strong> <?= e($incidencia['equipo_codigo']) ?></p>

    <p><strong>Categor&#237;a:</strong> <?= e($incidencia['categoria']) ?></p>

    <p><strong>Prioridad:</strong> <?= e($incidencia['prioridad']) ?></p>

    <p><strong>Descripci&#243;n:</strong> <?= e($incidencia['descripcion']) ?></p>

    <p><strong>Estado:</strong> <?= e($incidencia['estado']) ?></p>

    <p><strong>Autor:</strong> <?= e($incidencia['autor_nombre']) ?></p>

    <p><strong>Fecha de creaci&#243;n:</strong> <?= e($incidencia['creada_en']) ?></p>

    <p>
        <strong>&#218;ltima actualizaci&#243;n:</strong>
        <?= e($incidencia['actualizada_en'] ?? 'Sin actualizar') ?>
    </p>
</section>

<hr>

<section>
    <h3>Historial de cambios</h3>

    <?php if (count($historial) > 0): ?>

        <?php foreach ($historial as $registro): ?>

            <article>
                <p>
                    <strong>Usuario:</strong>
                    <?= e($registro['usuario_nombre']) ?>
                </p>

                <p>
                    <strong>Estado anterior:</strong>
                    <?= e($registro['estado_anterior']) ?>
                </p>

                <p>
                    <strong>Estado nuevo:</strong>
                    <?= e($registro['estado_nuevo']) ?>
                </p>

                <p>
                    <strong>Comentario:</strong>
                    <?= e($registro['comentario']) ?>
                </p>

                <p>
                    <strong>Fecha:</strong>
                    <?= e($registro['creado_en']) ?>
                </p>
            </article>

            <hr>

        <?php endforeach; ?>

    <?php else: ?>
        <p>Todav&#237;a no hay cambios registrados.</p>

    <?php endif; ?>
</section>

<hr>

<?php if ($esProfesor): ?>

    <section>
        <h3>Actualizar estado</h3>

        <form action="incidencia.php?id=<?= e($id) ?>" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >

            <input
                type="hidden"
                name="accion"
                value="cambiar_estado"
            >

            <label for="estado">Nuevo estado:</label>

            <select name="estado" id="estado" required>
                <option value="abierta"
                    <?= $incidencia['estado'] === 'abierta' ? 'selected' : '' ?>>
                    Abierta
                </option>

                <option value="en_curso"
                    <?= $incidencia['estado'] === 'en_curso' ? 'selected' : '' ?>>
                    En curso
                </option>

                <option value="resuelta"
                    <?= $incidencia['estado'] === 'resuelta' ? 'selected' : '' ?>>
                    Resuelta
                </option>
            </select>

            <br><br>

            <label for="comentario">Comentario del cambio:</label>

            <textarea
                name="comentario"
                id="comentario"
                rows="4"
                maxlength="500"
                required
            ></textarea>

            <br><br>

            <button type="submit">Guardar cambio</button>

        </form>
    </section>

<?php endif; ?>

<?php if ($puedeBorrar): ?>

    <section>
        <h3>Eliminar incidencia</h3>

        <form
            action="incidencia.php?id=<?= e($id) ?>"
            method="POST"
            onsubmit="return confirm('ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¿Seguro que quieres eliminar esta incidencia?');"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >

            <input
                type="hidden"
                name="accion"
                value="eliminar"
            >

            <button type="submit">Eliminar incidencia</button>

        </form>
    </section>

<?php endif; ?>

<p>
    <a href="incidencias.php">Volver al listado</a>
</p>

<?php
require_once dirname(__DIR__) . '/includes/pie.php';
?>
