<?php
declare(strict_types=1);

/**
 * Archivo: login.php
 * Descripción: Permite iniciar sesión en AulaFix.
 * Comprueba las credenciales, crea una sesión segura
 * y permite recordar el correo durante 30 días.
 */

session_start();

require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/funciones.php';

$titulo = 'Iniciar sesión';

$errores = [];

$email = '';

// Recuperar el correo guardado en una cookie, si existe.
if (isset($_COOKIE['recordar_email'])) {
    $email = (string) $_COOKIE['recordar_email'];
}

// Crear el token CSRF.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar el formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    $recordar = isset($_POST['recordar_email']);

    // Comprobar el token CSRF.
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $errores['csrf'] = 'La solicitud no es válida. Recarga la página.';
    }

    // Validar el correo.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'Introduce un correo electrónico válido.';
    }

    // Validar que la contraseña no esté vacía.
    if ($password === '') {
        $errores['password'] = 'Introduce tu contraseña.';
    }

    // Comprobar las credenciales si los campos son válidos.
    if (empty($errores)) {

        try {
            // Buscar al usuario por su correo.
            $consulta = $pdo->prepare(
                'SELECT id, nombre, email, clave_hash, rol
                 FROM usuarios
                 WHERE email = :email
                 LIMIT 1'
            );

            $consulta->execute([
                ':email' => $email
            ]);

            $usuario = $consulta->fetch();

            // Comprobar la contraseña almacenada.
            if (
                !$usuario ||
                !password_verify($password, $usuario['clave_hash'])
            ) {
                $errores['general'] =
                    'El correo electrónico o la contraseña son incorrectos.';
            } else {

                // Regenerar el identificador para evitar fijación de sesión.
                session_regenerate_id(true);

                // Guardar los datos necesarios del usuario en la sesión.
                $_SESSION['usuario_id'] = (int) $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol'];

                // Actualizar o eliminar la cookie de recuerdo.
                if ($recordar) {
                    setcookie('recordar_email', $email, [
                        'expires' => time() + (30 * 24 * 60 * 60),
                        'path' => '/',
                        'secure' => !empty($_SERVER['HTTPS'])
                            && $_SERVER['HTTPS'] !== 'off',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                } else {
                    setcookie('recordar_email', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'secure' => !empty($_SERVER['HTTPS'])
                            && $_SERVER['HTTPS'] !== 'off',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }

                // Eliminar el token utilizado.
                unset($_SESSION['csrf_token']);

                // Redirigir al panel de incidencias.
                header('Location: incidencias.php');
                exit;
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());

            $errores['general'] =
                'No se pudo iniciar sesión. Inténtalo de nuevo.';
        }
    }
}

require_once dirname(__DIR__) . '/includes/cabecera.php';
?>

<section>
    <h2>Iniciar sesión</h2>

    <p>Accede a tu cuenta de AulaFix.</p>

    <?php if (isset($_SESSION['mensaje_registro'])): ?>
        <p class="mensaje">
            <?= e($_SESSION['mensaje_registro']) ?>
        </p>
        <?php unset($_SESSION['mensaje_registro']); ?>
    <?php endif; ?>

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

    <form method="POST" action="login.php">

        <!-- Token CSRF. -->
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e($_SESSION['csrf_token']) ?>"
        >

        <!-- Correo electrónico. -->
        <div>
            <label for="email">Correo electrónico:</label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($email) ?>"
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
                autocomplete="current-password"
                required
            >

            <?php if (isset($errores['password'])): ?>
                <p class="error">
                    <?= e($errores['password']) ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Recordar correo durante 30 días. -->
        <div>
            <label>
                <input
                    type="checkbox"
                    name="recordar_email"
                    value="1"
                    <?= $email !== '' ? 'checked' : '' ?>
                >
                Recordar mi correo durante 30 días
            </label>
        </div>

        <button type="submit">Iniciar sesión</button>

    </form>

    <p>
        ¿Todavía no tienes cuenta?
        <a href="registro.php">Regístrate</a>
    </p>
</section>

<?php require_once dirname(__DIR__) . '/includes/pie.php'; ?>
