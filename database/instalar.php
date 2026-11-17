<?php
declare(strict_types=1);

/**
 * Archivo: instalar.php
 * Descripción: Inserta datos de ejemplo en la base de datos AulaFix.
 */

require_once __DIR__ . '/../config.php';

// Comprobar que las tablas están disponibles.
$pdo->beginTransaction();

try {
    // Insertar usuarios de prueba.
    $usuarios = [
        [
            'nombre' => 'Lucía Alumna',
            'email' => 'lucia@aulafix.test',
            'clave_hash' => password_hash(
                'Alumno12345',
                PASSWORD_ARGON2ID
            ),
            'rol' => 'alumno'
        ],
        [
            'nombre' => 'Carlos Profesor',
            'email' => 'carlos@aulafix.test',
            'clave_hash' => password_hash(
                'Profesor12345',
                PASSWORD_ARGON2ID
            ),
            'rol' => 'profesor'
        ]
    ];

    $sqlUsuario = "
        INSERT INTO usuarios (nombre, email, clave_hash, rol)
        VALUES (:nombre, :email, :clave_hash, :rol)
    ";

    $stmtUsuario = $pdo->prepare($sqlUsuario);

    foreach ($usuarios as $usuario) {
        $stmtUsuario->execute($usuario);
    }

    // Insertar equipos de ejemplo.
    $equipos = [
        [
            'codigo' => 'PC-01',
            'descripcion' => 'Ordenador del aula 1'
        ],
        [
            'codigo' => 'PROY-01',
            'descripcion' => 'Proyector principal del aula'
        ],
        [
            'codigo' => 'IMP-01',
            'descripcion' => 'Impresora del aula'
        ]
    ];

    $sqlEquipo = "
        INSERT INTO equipos (codigo, descripcion)
        VALUES (:codigo, :descripcion)
    ";

    $stmtEquipo = $pdo->prepare($sqlEquipo);

    foreach ($equipos as $equipo) {
        $stmtEquipo->execute($equipo);
    }

    // Obtener los identificadores de los usuarios.
    $idAlumno = (int) $pdo->query(
        "SELECT id FROM usuarios WHERE email = 'lucia@aulafix.test'"
    )->fetchColumn();

    // Obtener los identificadores de los equipos.
    $idProyector = (int) $pdo->query(
        "SELECT id FROM equipos WHERE codigo = 'PROY-01'"
    )->fetchColumn();

    $idOrdenador = (int) $pdo->query(
        "SELECT id FROM equipos WHERE codigo = 'PC-01'"
    )->fetchColumn();

    // Insertar incidencias de ejemplo.
    $incidencias = [
        [
            'equipo_id' => $idProyector,
            'usuario_id' => $idAlumno,
            'categoria' => 'hardware',
            'prioridad' => 'alta',
            'descripcion' => 'El proyector se apaga solo.',
            'estado' => 'abierta'
        ],
        [
            'equipo_id' => $idOrdenador,
            'usuario_id' => $idAlumno,
            'categoria' => 'software',
            'prioridad' => 'media',
            'descripcion' => 'El ordenador no inicia correctamente.',
            'estado' => 'en_curso'
        ]
    ];

    $sqlIncidencia = "
        INSERT INTO incidencias
            (equipo_id, usuario_id, categoria, prioridad, descripcion, estado)
        VALUES
            (:equipo_id, :usuario_id, :categoria, :prioridad, :descripcion, :estado)
    ";

    $stmtIncidencia = $pdo->prepare($sqlIncidencia);

    foreach ($incidencias as $incidencia) {
        $stmtIncidencia->execute($incidencia);
    }

    // Registrar un cambio de estado en el historial.
    $idIncidencia = (int) $pdo->query(
        "SELECT id FROM incidencias WHERE estado = 'en_curso' LIMIT 1"
    )->fetchColumn();

    $sqlHistorial = "
        INSERT INTO historial
            (incidencia_id, usuario_id, estado_anterior, estado_nuevo, comentario)
        VALUES
            (:incidencia_id, :usuario_id, :estado_anterior, :estado_nuevo, :comentario)
    ";

    $stmtHistorial = $pdo->prepare($sqlHistorial);

    $stmtHistorial->execute([
        'incidencia_id' => $idIncidencia,
        'usuario_id' => (int) $pdo->query(
            "SELECT id FROM usuarios WHERE email = 'carlos@aulafix.test'"
        )->fetchColumn(),
        'estado_anterior' => 'abierta',
        'estado_nuevo' => 'en_curso',
        'comentario' => 'Estamos revisando el ordenador.'
    ]);

    $pdo->commit();

    echo 'Instalación de AulaFix completada correctamente.';

} catch (Throwable $e) {
    $pdo->rollBack();


    error_log($e->getMessage());

    http_response_code(500);
    echo 'No se pudieron insertar los datos de ejemplo.';
}
