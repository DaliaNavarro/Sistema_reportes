
-- Usado para eliminar campos en tablas de una version anterior

USE reportes;

ALTER TABLE usuarios_sistema
    DROP INDEX uk_usuario_correo,
    DROP COLUMN correo,
    ADD UNIQUE KEY uk_usuario_nombre (nombre);

ALTER TABLE personas
    DROP COLUMN cargo,
    DROP COLUMN correo,
    DROP COLUMN telefono;

ALTER TABLE reportes
    DROP COLUMN asunto;
