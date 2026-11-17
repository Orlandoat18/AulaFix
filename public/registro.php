<?php
declare(strict_types=1);

/**
 * Archivo: registro.php
 * Descripción: Permite registrar nuevos usuarios en AulaFix.
 * Valida los datos, protege el formulario mediante CSRF
 * y almacena las contraseñas utilizando Argon2id.
 */

// Iniciar la sesión antes de generar cualquier contenido.
session_start();

// Cargar la conexión PDO y las funciones comunes.
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

// Título de la página.
$titulo = 'Registro de usuario';

// Inicializar los datos del formulario.
$datos = [
    'nombre' => '',
    'email' => ''
];

// Inicializar los errores.
$errores = [];

// Crear el token CSRF si todavía no existe.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar el formulario cuando se envía mediante POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recuperar y limpiar los datos introducidos.
    $datos['nombre'] = trim((string) ($_POST['nombre'] ?? ''));
    $datos['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));

    $password = (string) ($_POST['password'] ?? '');
    $passwordRepetida = (string) ($_POST['password_repetida'] ?? '');

    // Comprobar el token CSRF.
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $errores['csrf'] = 'La solicitud no es válida. Recarga la página.';
    }

    // Validar el nombre.
    $longitudNombre = mb_strlen($datos['nombre']);

    if ($longitudNombre < 2 || $longitudNombre > 80) {
        $errores['nombre'] =
            'El nombre debe tener entre 2 y 80 caracteres.';
    }

    // Validar el correo electrónico.
    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'Introduce un correo electrónico válido.';
    }

    /*
     * Comprobar que el correo no esté registrado.
     * Se utiliza una consulta preparada para evitar SQL Injection.
     */
    if (!isset($errores['email'])) {

        $consultaEmail = $pdo->prepare(
            'SELECT id FROM usuarios WHERE email = :email LIMIT 1'
        );

        $consultaEmail->execute([
            ':email' => $datos['email']
        ]);

        if ($consultaEmail->fetch()) {
            $errores['email'] = 'Este correo electrónico ya está registrado.';
        }
    }

    // Validar la longitud de la contraseña.
    if (strlen($password) < 10) {
        $errores['password'] =
            'La contraseña debe tener al menos 10 caracteres.';
    }

    // Comprobar que la contraseña contiene letras y números.
    elseif (
        !preg_match('/[a-zA-Z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {
        $errores['password'] =
            'La contraseña debe contener letras y números.';
    }

    // Comprobar que ambas contraseñas coinciden.
    if ($password !== $passwordRepetida) {
        $errores['password_repetida'] =
            'Las contraseñas no coinciden.';
    }

    // Si no existen errores, registrar al usuario.
    if (empty($errores)) {

        try {
            // Generar el hash seguro de la contraseña con Argon2id.
            $claveHash = password_hash(
                $password,
                PASSWORD_ARGON2ID
            );

            if ($claveHash === false) {
                throw new RuntimeException(
                    'No se pudo proteger la contraseña.'
                );
            }

            /*
             * El rol se asigna desde el servidor.
             * Los registros públicos siempre comienzan como alumno.
             */
            $rol = 'alumno';

            // Insertar el usuario utilizando una consulta preparada.
            $consulta = $pdo->prepare(
                'INSERT INTO usuarios
                (nombre, email, clave_hash, rol, creado_en)
                VALUES
                (:nombre, :email, :clave_hash, :rol, NOW())'
            );

            $consulta->execute([
                ':nombre' => $datos['nombre'],
                ':email' => $datos['email'],
                ':clave_hash' => $claveHash,
                ':rol' => $rol
            ]);

            // Eliminar el token utilizado.
            unset($_SESSION['csrf_token']);

            // Guardar un mensaje para mostrarlo en el inicio de sesión.
            $_SESSION['mensaje_registro'] =
                'Registro completado correctamente. Ya puedes iniciar sesión.';

            // Redirigir después del registro.
            header('Location: login.php');
            exit;

        } catch (PDOException $e) {

            // Registrar el error técnico sin mostrarlo al usuario.
            error_log($e->getMessage());

            $errores['general'] =
                'No se pudo completar el registro. Inténtalo de nuevo.';
        }
    }
}

// Cargar la cabecera común.
require_once dirname(__DIR__) . '/includes/cabecera.php';
?>

<section>
    <h2>Crear una cuenta</h2>

    <p>Completa el formulario para registrarte en AulaFix.</p>

    <?php if (isset($errores['csrf'])): ?>
        <p class="error">
            <?= e($errores['csrf']) ?>
        </p>
    <?php endif; ?>

    <?php if (isset($errores['general'])): ?>
        <p class="error">
            <?= e($errores['general']) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="registro.php">

        <!-- Token de protección CSRF. -->
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e($_SESSION['csrf_token']) ?>"
        >

        <!-- Nombre del usuario. -->
        <div>
            <label for="nombre">Nombre completo:</label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                minlength="2"
                maxlength="80"
                value="<?= e($datos['nombre']) ?>"
                autocomplete="name"
                required
            >

            <?php if (isset($errores['nombre'])): ?>
                <p class="error">
                    <?= e($errores['nombre']) ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Correo electrónico. -->
        <div>
            <label for="email">Correo electrónico:</label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($datos['email']) ?>"
                autocomplete="email"
                required
            >

            <?php if (isset($errores['email'])): ?>
                <p class="error">
                    <?= e($errores['email']) ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Contraseña. -->
        <div>
            <label for="password">Contraseña:</label>

            <input
                type="password"
                id="password"
                name="password"
                minlength="10"
                autocomplete="new-password"
                required
            >

            <small>
                Mínimo 10 caracteres, incluyendo letras y números.
            </small>

            <?php if (isset($errores['password'])): ?>
                <p class="error">
                    <?= e($errores['password']) ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Repetición de contraseña. -->
        <div>
            <label for="password_repetida">
                Repetir contraseña:
            </label>

            <input
                type="password"
                id="password_repetida"
                name="password_repetida"
                minlength="10"
                autocomplete="new-password"
                required
            >

            <?php if (isset($errores['password_repetida'])): ?>
                <p class="error">
                    <?= e($errores['password_repetida']) ?>
                </p>
            <?php endif; ?>
        </div>

        <button type="submit">Registrarse</button>

    </form>

    <p>
        ¿Ya tienes una cuenta?
        <a href="login.php">Inicia sesión</a>
    </p>
</section>

<?php require_once dirname(__DIR__) . '/includes/pie.php'; ?>
