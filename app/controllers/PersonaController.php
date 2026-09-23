<?php

class PersonaController
{
    public static function index(): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();

        $personas = $db->query("
            SELECT
                p.id,
                p.departamento_id,
                p.nombre,
                p.activo,
                d.nombre AS departamento
            FROM personas p
            INNER JOIN departamentos d
                ON d.id = p.departamento_id
            ORDER BY p.nombre
        ")->fetchAll();

        $departamentos = $db->query("
            SELECT id, nombre
            FROM departamentos
            WHERE activo = 1
            ORDER BY nombre
        ")->fetchAll();

        view('persona/index', [
            'personas' => $personas,
            'departamentos' => $departamentos
        ]);
    }

    public static function guardar(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $db = Database::getConnection();

        $id = (int)($_POST['id'] ?? 0);
        $departamentoId = (int)($_POST['departamento_id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');

        if ($departamentoId <= 0 || $nombre === '') {
            flash('error', 'Departamento y nombre son obligatorios.');
            redirect('personas');
        }

        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE personas
                SET departamento_id = ?,
                    nombre = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $departamentoId,
                $nombre,
                $id
            ]);

            flash('success', 'Persona actualizada.');
        } else {
            $stmt = $db->prepare("
                INSERT INTO personas
                (departamento_id, nombre)
                VALUES (?, ?)
            ");

            $stmt->execute([
                $departamentoId,
                $nombre
            ]);

            flash('success', 'Persona registrada.');
        }

        redirect('personas');
    }

    public static function cambiarEstado(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);

        $db = Database::getConnection();

        $stmt = $db->prepare("
            UPDATE personas
            SET activo = NOT activo
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        flash('success', 'Estado actualizado.');
        redirect('personas');
    }
    public static function eliminar(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Persona inválida.');
            redirect('personas');
        }

        $db = Database::getConnection();
        try {
            $stmt = $db->prepare('SELECT nombre FROM personas WHERE id = ?');
            $stmt->execute([$id]);
            $persona = $stmt->fetch();
            if (!$persona) {
                throw new RuntimeException('Persona no encontrada.');
            }

            $stmt = $db->prepare('DELETE FROM personas WHERE id = ?');
            $stmt->execute([$id]);
            flash('success', 'Persona eliminada.');
        } catch (Throwable $e) {
            flash('error', 'No se pudo eliminar la persona: ' . $e->getMessage());
        }

        redirect('personas');
    }

}
