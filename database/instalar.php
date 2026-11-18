<?php
declare(strict_types=1);

/**
 * Proyecto: AulaFix
 * Archivo: instalar.php
 * DescripciÃ³n: Inserta datos de ejemplo en la base de datos AulaFix.
 *
 * Los datos se insertan mediante una transacciÃ³n para garantizar
 * la integridad de la informaciÃ³n.
 */

require_once __DIR__ . '/../config.php';

try {

    // Iniciar una transacciÃ³n.
    $pdo->beginTransaction();


    // =====================================================
    // 1. INSERTAR USUARIOS DE EJEMPLO
    // =====================================================

    $usuarios = [
        [
            'nombre' => 'LucÃ­a Alumna',
            'email' => 'lucia@aulafix.test',
            'password' => 'Alumno12345',
            'rol' => 'alumno'
        ],
        [
            'nombre' => 'Carlos Profesor',
            'email' => 'carlos@aulafix.test',
            'password' => 'Profesor12345',
            'rol' => 'profesor'
        ]
    ];

    $sqlUsuario = "
        SELECT id
        FROM usuarios
        WHERE email = :email
    ";

    $stmtUsuario = $pdo->prepare($sqlUsuario);

    $sqlInsertarUsuario = "
        INSERT INTO usuarios (nombre, email, clave_hash, rol)
        VALUES (:nombre, :email, :clave_hash, :rol)
    ";

    $stmtInsertarUsuario = $pdo->prepare($sqlInsertarUsuario);

    foreach ($usuarios as $usuario) {

        $stmtUsuario->execute([
            'email' => $usuario['email']
        ]);

        // Insertar Ãºnicamente si el usuario no existe.
        if ($stmtUsuario->fetchColumn() === false) {

            $stmtInsertarUsuario->execute([
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'clave_hash' => password_hash(
                    $usuario['password'],
                    PASSWORD_ARGON2ID
                ),
                'rol' => $usuario['rol']
            ]);
        }
    }


    // =====================================================
    // 2. INSERTAR EQUIPOS DE EJEMPLO
    // =====================================================

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
        SELECT id
        FROM equipos
        WHERE codigo = :codigo
    ";

    $stmtEquipo = $pdo->prepare($sqlEquipo);

    $sqlInsertarEquipo = "
        INSERT INTO equipos (codigo, descripcion)
        VALUES (:codigo, :descripcion)
    ";

    $stmtInsertarEquipo = $pdo->prepare($sqlInsertarEquipo);

    foreach ($equipos as $equipo) {

        $stmtEquipo->execute([
            'codigo' => $equipo['codigo']
        ]);

        // Insertar Ãºnicamente si el equipo no existe.
        if ($stmtEquipo->fetchColumn() === false) {

            $stmtInsertarEquipo->execute($equipo);
        }
    }


    // =====================================================
    // 3. OBTENER IDENTIFICADORES
    // =====================================================

    // Obtener el identificador de la alumna.
    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE email = :email
    ");

    $stmt->execute([
        'email' => 'lucia@aulafix.test'
    ]);

    $idAlumno = (int) $stmt->fetchColumn();

    // Obtener el identificador del profesor.
    $stmt->execute([
        'email' => 'carlos@aulafix.test'
    ]);

    $idProfesor = (int) $stmt->fetchColumn();

    // Obtener el identificador del proyector.
    $stmtEquipo->execute([
        'codigo' => 'PROY-01'
    ]);

    $idProyector = (int) $stmtEquipo->fetchColumn();

    // Obtener el identificador del ordenador.
    $stmtEquipo->execute([
        'codigo' => 'PC-01'
    ]);

    $idOrdenador = (int) $stmtEquipo->fetchColumn();

    // Comprobar que todos los registros necesarios existen.
    if (
        $idAlumno <= 0 ||
        $idProfesor <= 0 ||
        $idProyector <= 0 ||
        $idOrdenador <= 0
    ) {
        throw new RuntimeException(
            'No se pudieron obtener los identificadores necesarios.'
        );
    }


    // =====================================================
    // 4. INSERTAR INCIDENCIAS DE EJEMPLO
    // =====================================================

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

    $sqlBuscarIncidencia = "
        SELECT id
        FROM incidencias
        WHERE equipo_id = :equipo_id
          AND usuario_id = :usuario_id
          AND descripcion = :descripcion
    ";

    $stmtBuscarIncidencia = $pdo->prepare(
        $sqlBuscarIncidencia
    );

    $sqlInsertarIncidencia = "
        INSERT INTO incidencias
            (
                equipo_id,
                usuario_id,
                categoria,
                prioridad,
                descripcion,
                estado
            )
        VALUES
            (
                :equipo_id,
                :usuario_id,
                :categoria,
                :prioridad,
                :descripcion,
                :estado
            )
    ";

    $stmtInsertarIncidencia = $pdo->prepare(
        $sqlInsertarIncidencia
    );

    $idIncidenciaOrdenador = 0;

    foreach ($incidencias as $incidencia) {

        $stmtBuscarIncidencia->execute([
            'equipo_id' => $incidencia['equipo_id'],
            'usuario_id' => $incidencia['usuario_id'],
            'descripcion' => $incidencia['descripcion']
        ]);

        $idIncidencia = $stmtBuscarIncidencia->fetchColumn();

        // Insertar Ãºnicamente si la incidencia no existe.
        if ($idIncidencia === false) {

            $stmtInsertarIncidencia->execute($incidencia);

            $idIncidencia = (int) $pdo->lastInsertId();
        } else {

            $idIncidencia = (int) $idIncidencia;
        }

        // Guardar el identificador de la incidencia del ordenador.
        if ($incidencia['equipo_id'] === $idOrdenador) {
            $idIncidenciaOrdenador = $idIncidencia;
        }
    }


    // =====================================================
    // 5. INSERTAR REGISTRO EN EL HISTORIAL
    // =====================================================

    $comentario = 'Estamos revisando el ordenador.';

    // Comprobar si el registro del historial ya existe.
    $sqlBuscarHistorial = "
        SELECT id
        FROM historial
        WHERE incidencia_id = :incidencia_id
          AND usuario_id = :usuario_id
          AND comentario = :comentario
    ";

    $stmtBuscarHistorial = $pdo->prepare(
        $sqlBuscarHistorial
    );

    $stmtBuscarHistorial->execute([
        'incidencia_id' => $idIncidenciaOrdenador,
        'usuario_id' => $idProfesor,
        'comentario' => $comentario
    ]);

    // Insertar Ãºnicamente si el historial no existe.
    if ($stmtBuscarHistorial->fetchColumn() === false) {

        $sqlHistorial = "
            INSERT INTO historial
                (
                    incidencia_id,
                    usuario_id,
                    estado_anterior,
                    estado_nuevo,
                    comentario
                )
            VALUES
                (
                    :incidencia_id,
                    :usuario_id,
                    :estado_anterior,
                    :estado_nuevo,
                    :comentario
                )
        ";

        $stmtHistorial = $pdo->prepare($sqlHistorial);

        $stmtHistorial->execute([
            'incidencia_id' => $idIncidenciaOrdenador,
            'usuario_id' => $idProfesor,
            'estado_anterior' => 'abierta',
            'estado_nuevo' => 'en_curso',
            'comentario' => $comentario
        ]);
    }


    // =====================================================
    // 6. CONFIRMAR TRANSACCIÃ“N
    // =====================================================

    $pdo->commit();

    echo 'InstalaciÃ³n de AulaFix completada correctamente.';

} catch (Throwable $e) {

    // Deshacer los cambios si la transacciÃ³n sigue activa.
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Guardar el error en el registro del servidor.
    error_log($e->getMessage());

    http_response_code(500);

    echo 'No se pudieron insertar los datos de ejemplo.';
}
