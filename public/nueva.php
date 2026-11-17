<?php
declare(strict_types=1);

/**
 * Archivo: nueva.php
 * DescripciÃ³n: Valida y registra nuevas incidencias en MySQL.
 * Incluye protecciÃ³n CSRF y patrÃ³n Post-Redirect-Get.
 */

// Comprobar que el usuario ha iniciado sesiÃ³n.
require_once dirname(__DIR__) . '/includes/auth.php';
requireLogin();

// Cargar conexiÃ³n y funciones comunes.
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// TÃ­tulo de la pÃ¡gina.
$titulo = 'Nueva incidencia';

// Valores permitidos.
$categorias = ['hardware', 'software', 'red', 'otro'];
$prioridades = ['baja', 'media', 'alta'];

// Obtener los equipos disponibles.
$consultaEquipos = $pdo->query(
    'SELECT id, codigo FROM equipos ORDER BY codigo ASC'
);

$equipos = $consultaEquipos->fetchAll(PDO::FETCH_ASSOC);

// Inicializar los datos del formulario.
$datos = [
    'equipo_id' => '',
    'categoria' => '',
    'prioridad' => '',
    'descripcion' => ''
];

$errores = [];

// Crear token CSRF si todavÃ­a no existe.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar el formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recuperar los datos enviados.
    foreach ($datos as $campo => $valor) {
        $valorRecibido = $_POST[$campo] ?? '';

        $datos[$campo] = is_string($valorRecibido)
            ? trim($valorRecibido)
            : '';
    }

    // Comprobar el token CSRF.
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Solicitud no vÃ¡lida. Recarga la pÃ¡gina.');
    }

    // Validar que el equipo existe.
    $equipoId = filter_var(
        $datos['equipo_id'],
        FILTER_VALIDATE_INT
    );

    if ($equipoId === false || $equipoId < 1) {
        $errores['equipo_id'] = 'Selecciona un equipo vÃ¡lido.';
    } else {
        $comprobarEquipo = $pdo->prepare(
            'SELECT id FROM equipos WHERE id = :id'
        );

        $comprobarEquipo->execute(['id' => $equipoId]);

        if (!$comprobarEquipo->fetch()) {
            $errores['equipo_id'] = 'El equipo seleccionado no existe.';
        }
    }

    // Validar la categorÃ­a.
    if (!in_array($datos['categoria'], $categorias, true)) {
        $errores['categoria'] = 'Selecciona una categorÃ­a vÃ¡lida.';
    }

    // Validar la prioridad.
    if (!in_array($datos['prioridad'], $prioridades, true)) {
        $errores['prioridad'] = 'Selecciona una prioridad vÃ¡lida.';
    }

    // Validar la descripciÃ³n.
    $longitud = mb_strlen($datos['descripcion']);

    if ($longitud < 10 || $longitud > 500) {
        $errores['descripcion'] =
            'La descripciÃ³n debe tener entre 10 y 500 caracteres.';
    }

    // Si no existen errores, guardar la incidencia en MySQL.
    if (empty($errores)) {

        try {
            $usuarioId = (int) $_SESSION['usuario_id'];

            $insertar = $pdo->prepare(
                'INSERT INTO incidencias
                    (
                        equipo_id,
                        usuario_id,
                        categoria,
                        prioridad,
                        descripcion,
                        estado,
                        creada_en
                    )
                 VALUES
                    (
                        :equipo_id,
                        :usuario_id,
                        :categoria,
                        :prioridad,
                        :descripcion,
                        :estado,
                        NOW()
                    )'
            );

            $insertar->execute([
                'equipo_id' => $equipoId,
                'usuario_id' => $usuarioId,
                'categoria' => $datos['categoria'],
                'prioridad' => $datos['prioridad'],
                'descripcion' => $datos['descripcion'],
                'estado' => 'abierta'
            ]);

            // Guardar el mensaje para mostrarlo despuÃ©s de redirigir.
            $_SESSION['mensaje'] =
                'La incidencia se ha registrado correctamente.';

            // Renovar el token despuÃ©s del envÃ­o.
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // Aplicar Post-Redirect-Get.
            header('Location: incidencias.php?resultado=creada');
            exit;

        } catch (PDOException $e) {

            // Registrar el error sin mostrar informaciÃ³n interna.
            error_log($e->getMessage());

            $errores['general'] =
                'No se pudo registrar la incidencia. IntÃ©ntalo de nuevo.';
        }
    }
}

// Cargar la cabecera.
require_once dirname(__DIR__) . '/includes/cabecera.php';
?>

<section>
    <h2>Nueva incidencia</h2>

    <p>Completa los siguientes campos para registrar un problema.</p>

    <?php if (isset($errores['general'])): ?>
        <p class="error">
            <?= e($errores['general']) ?>
        </p>
    <?php endif; ?>

    <?php if (isset($errores['csrf'])): ?>
        <p class="error">
            <?= e($errores['csrf']) ?>
        </p>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <p class="mensaje">
            <?= e($_SESSION['mensaje']) ?>
        </p>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <form id="form-nueva" method="POST" action="nueva.php">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e($_SESSION['csrf_token']) ?>"
        >

        <div>
            <label for="equipo_id">Equipo:</label>

            <select id="equipo_id" name="equipo_id" required>
                <option value="">Selecciona un equipo</option>

                <?php foreach ($equipos as $equipo): ?>
                    <option
                        value="<?= e($equipo['id']) ?>"
                        <?= $datos['equipo_id'] === (string) $equipo['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($equipo['codigo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (isset($errores['equipo_id'])): ?>
                <p class="error">
                    <?= e($errores['equipo_id']) ?>
                </p>
            <?php endif; ?>
        </div>

        <div>
           <label for="categoria">Categor&#237;a:</label>

            <select id="categoria" name="categoria" required>
               <option value="">Selecciona una categor&#237;a</option>

                <?php foreach ($categorias as $categoria): ?>
                    <option
                        value="<?= e($categoria) ?>"
                        <?= $datos['categoria'] === $categoria
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e(ucfirst($categoria)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (isset($errores['categoria'])): ?>
                <p class="error">
                    <?= e($errores['categoria']) ?>
                </p>
            <?php endif; ?>
        </div>

        <fieldset>
            <legend>Prioridad:</legend>

            <?php foreach ($prioridades as $prioridad): ?>
                <label>
                    <input
                        type="radio"
                        name="prioridad"
                        value="<?= e($prioridad) ?>"
                        <?= $datos['prioridad'] === $prioridad
                            ? 'checked'
                            : '' ?>
                        required
                    >
                    <?= e(ucfirst($prioridad)) ?>
                </label>
            <?php endforeach; ?>

            <?php if (isset($errores['prioridad'])): ?>
                <p class="error">
                    <?= e($errores['prioridad']) ?>
                </p>
            <?php endif; ?>
        </fieldset>

        <div>
           <label for="descripcion">Descripci&#243;n:</label>
            <textarea
                id="descripcion"
                name="descripcion"
                minlength="10"
                maxlength="500"
                rows="5"
                required
            ><?= e($datos['descripcion']) ?></textarea>

            <p id="contador">0 caracteres</p>

            <?php if (isset($errores['descripcion'])): ?>
                <p class="error">
                    <?= e($errores['descripcion']) ?>
                </p>
            <?php endif; ?>
        </div>

        <button type="submit">Registrar incidencia</button>

        <a href="incidencias.php">Cancelar</a>

    </form>
</section>

<script type="module">
    import { iniciarFormulario } from './js/nueva.js';

    iniciarFormulario();
</script>

<?php
require_once dirname(__DIR__) . '/includes/pie.php';
?>
