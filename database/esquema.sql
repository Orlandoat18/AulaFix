
/*
 * Proyecto: AulaFix
 * Archivo: esquema.sql
 * Descripción: Crea la estructura de la base de datos,
 * incluyendo usuarios, equipos, incidencias e historial.
 *
 * Motor: MySQL 8
 * Codificación: UTF-8 (utf8mb4)
 */


-- =========================================================
-- CREACIÓN Y SELECCIÓN DE LA BASE DE DATOS
-- =========================================================

CREATE DATABASE IF NOT EXISTS aulafix
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE aulafix;


-- =========================================================
-- 1. TABLA: usuarios
-- =========================================================
-- Almacena las cuentas de alumnos y profesores.

CREATE TABLE IF NOT EXISTS usuarios (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(80) NOT NULL,

    email VARCHAR(255) NOT NULL UNIQUE,

    clave_hash VARCHAR(255) NOT NULL,

    rol ENUM('alumno', 'profesor')
        NOT NULL DEFAULT 'alumno',

    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 2. TABLA: equipos
-- =========================================================
-- Almacena los equipos informáticos del aula.

CREATE TABLE IF NOT EXISTS equipos (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    codigo VARCHAR(20) NOT NULL UNIQUE,

    descripcion VARCHAR(255) NOT NULL

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 3. TABLA: incidencias
-- =========================================================
-- Almacena las incidencias comunicadas por los usuarios.

CREATE TABLE IF NOT EXISTS incidencias (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    equipo_id INT UNSIGNED NOT NULL,

    usuario_id INT UNSIGNED NOT NULL,

    categoria ENUM(
        'hardware',
        'software',
        'red',
        'otro'
    ) NOT NULL,

    prioridad ENUM(
        'baja',
        'media',
        'alta'
    ) NOT NULL,

    descripcion VARCHAR(500) NOT NULL,

    estado ENUM(
        'abierta',
        'en_curso',
        'resuelta'
    ) NOT NULL DEFAULT 'abierta',

    creada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    actualizada_en TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    -- Relación con la tabla equipos.
    CONSTRAINT fk_incidencias_equipos
        FOREIGN KEY (equipo_id)
        REFERENCES equipos(id),

    -- Relación con el usuario que comunica la incidencia.
    CONSTRAINT fk_incidencias_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id),

    -- Índices para facilitar las consultas y filtros.
    INDEX idx_incidencias_estado (estado),

    INDEX idx_incidencias_prioridad (prioridad),

    INDEX idx_incidencias_creada (creada_en),

    INDEX idx_incidencias_usuario (usuario_id),

    INDEX idx_incidencias_equipo (equipo_id)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 4. TABLA: historial
-- =========================================================
-- Registra los cambios de estado de las incidencias
-- y los comentarios realizados por los profesores.

CREATE TABLE IF NOT EXISTS historial (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    incidencia_id INT UNSIGNED NOT NULL,

    usuario_id INT UNSIGNED NOT NULL,

    estado_anterior ENUM(
        'abierta',
        'en_curso',
        'resuelta'
    ) NOT NULL,

    estado_nuevo ENUM(
        'abierta',
        'en_curso',
        'resuelta'
    ) NOT NULL,

    comentario VARCHAR(500) NOT NULL,

    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Relación con la incidencia correspondiente.
    -- Al eliminar una incidencia se elimina su historial.
    CONSTRAINT fk_historial_incidencias
        FOREIGN KEY (incidencia_id)
        REFERENCES incidencias(id)
        ON DELETE CASCADE,

    -- Relación con el usuario que realiza el cambio.
    CONSTRAINT fk_historial_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id),

    -- Índices para consultar el historial.
    INDEX idx_historial_incidencia (incidencia_id),

    INDEX idx_historial_usuario (usuario_id),

    INDEX idx_historial_fecha (creado_en)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


