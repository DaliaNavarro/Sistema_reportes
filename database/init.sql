CREATE DATABASE IF NOT EXISTS reportes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE reportes;

-- =========================================================
-- USUARIOS DEL SISTEMA
-- Son las personas que pueden iniciar sesión.
-- =========================================================

CREATE TABLE usuarios_sistema (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(120) NOT NULL,

    password_hash VARCHAR(255) NOT NULL,

    rol ENUM(
        'Administrador',
        'Tecnico'
    ) NOT NULL DEFAULT 'Tecnico',

    activo TINYINT(1) NOT NULL DEFAULT 1,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_usuario_nombre (nombre)
) ENGINE=InnoDB;


-- =========================================================
-- DEPARTAMENTOS
-- =========================================================

CREATE TABLE departamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(150) NOT NULL,

    encargado_id INT UNSIGNED NULL,

    activo TINYINT(1) NOT NULL DEFAULT 1,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_departamento_nombre (nombre),

    KEY idx_departamento_encargado (encargado_id)

) ENGINE=InnoDB;


-- =========================================================
-- PERSONAS / SOLICITANTES
-- Son las personas pertenecientes a los departamentos.
-- =========================================================

CREATE TABLE personas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    departamento_id INT UNSIGNED NOT NULL,

    nombre VARCHAR(150) NOT NULL,

    activo TINYINT(1) NOT NULL DEFAULT 1,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_persona_departamento (departamento_id),

    CONSTRAINT fk_persona_departamento
        FOREIGN KEY (departamento_id)
        REFERENCES departamentos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT

) ENGINE=InnoDB;


-- =========================================================
-- AHORA SE PUEDE CREAR LA RELACIÓN DEL ENCARGADO

ALTER TABLE departamentos
ADD CONSTRAINT fk_departamento_encargado
    FOREIGN KEY (encargado_id)
    REFERENCES personas(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL;


-- =========================================================
-- TIPOS DE SOLICITUD / SERVICIO
-- =========================================================

CREATE TABLE tipos_problema (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(120) NOT NULL,

    descripcion TEXT NULL,

    activo TINYINT(1) NOT NULL DEFAULT 1,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_tipo_problema_nombre (nombre)

) ENGINE=InnoDB;


-- =========================================================
-- REPORTES / SOLICITUDES
-- =========================================================

CREATE TABLE reportes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    folio VARCHAR(30) NOT NULL,

    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    departamento_id INT UNSIGNED NOT NULL,

    persona_id INT UNSIGNED NULL,

    -- 1 = solicitud para todo el departamento
    -- 0 = solicitud de una persona específica
    es_departamento_general TINYINT(1) NOT NULL DEFAULT 0,

    tipo_problema_id INT UNSIGNED NOT NULL,

    origen ENUM(
        'MEMORANDUM',
        'INFORMAL'
    ) NOT NULL DEFAULT 'INFORMAL',

    numero_oficio VARCHAR(100) NULL,

    descripcion TEXT NOT NULL,

    estado ENUM(
        'REGISTRADO',
        'EN_PROCESO',
        'ATENDIDO',
        'CANCELADO'
    ) NOT NULL DEFAULT 'REGISTRADO',

    observaciones TEXT NULL,

    fecha_atencion DATETIME NULL,

    usuario_registro_id INT UNSIGNED NOT NULL,

    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_reporte_folio (folio),

    KEY idx_reporte_departamento (departamento_id),

    KEY idx_reporte_persona (persona_id),

    KEY idx_reporte_tipo (tipo_problema_id),

    KEY idx_reporte_estado (estado),

    KEY idx_reporte_fecha_solicitud (fecha_solicitud),

    KEY idx_reporte_usuario (usuario_registro_id),

    CONSTRAINT fk_reporte_departamento
        FOREIGN KEY (departamento_id)
        REFERENCES departamentos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_reporte_persona
        FOREIGN KEY (persona_id)
        REFERENCES personas(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_reporte_tipo
        FOREIGN KEY (tipo_problema_id)
        REFERENCES tipos_problema(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_reporte_usuario
        FOREIGN KEY (usuario_registro_id)
        REFERENCES usuarios_sistema(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT

) ENGINE=InnoDB;


-- =========================================================
-- ARCHIVOS DE LOS REPORTES
-- Principalmente memorándums PDF.
-- =========================================================

CREATE TABLE archivos_reportes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    reporte_id BIGINT UNSIGNED NOT NULL,

    nombre_original VARCHAR(255) NOT NULL,

    nombre_archivo VARCHAR(255) NOT NULL,

    ruta VARCHAR(500) NOT NULL,

    tipo_mime VARCHAR(100) NULL,

    tamano BIGINT UNSIGNED NULL,

    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_archivo_reporte (reporte_id),

    CONSTRAINT fk_archivo_reporte
        FOREIGN KEY (reporte_id)
        REFERENCES reportes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE

) ENGINE=InnoDB;


-- =========================================================
-- HISTORIAL DE CAMBIOS
-- =========================================================

CREATE TABLE historial_reportes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    reporte_id BIGINT UNSIGNED NOT NULL,

    usuario_id INT UNSIGNED NULL,

    estado_anterior VARCHAR(30) NULL,

    estado_nuevo VARCHAR(30) NULL,

    comentario TEXT NULL,

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_historial_reporte (reporte_id),

    CONSTRAINT fk_historial_reporte
        FOREIGN KEY (reporte_id)
        REFERENCES reportes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_historial_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios_sistema(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL

) ENGINE=InnoDB;


-- =========================================================
-- DATOS INICIALES EJEMPLOS
-- =========================================================

INSERT INTO departamentos (nombre) VALUES
('DEPARTAMENTO01'),
('DEPARTAMENTO02'),
('DEPARTAMENTO03'),


INSERT INTO tipos_problema (nombre, descripcion) VALUES
(
    'Limpieza de hardware',
    'Servicio de limpieza de hardware de los equipos correspondientes.'
),
(
    'Configuración de software',
    'Se realizó la configuración de software solicitada por el usuario.'
),
(
    'Mantenimiento completo',
    'Limpieza de hardware, configuración y limpieza de software.'
),
(
    'Problema de periféricos',
    'Se atendieron los problemas relacionados con teclado, mouse u otros periféricos.'
),
(
    'Problemas de red/WiFi',
    'Detección y solución de problemas relacionados con la conexión de red.'
),
(
    'Instalación de software',
    'Instalación de programas, drivers o aplicaciones solicitadas.'
),
(
    'Atasco en impresora',
    'Atención general de soporte técnico.'
);