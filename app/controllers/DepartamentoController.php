<?php

class DepartamentoController
{
    public static function index(): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();

        $stmt = $db->query("
            SELECT
                d.id,
                d.nombre,
                d.activo,
                d.encargado_id,
                p.nombre AS encargado
            FROM departamentos d
            LEFT JOIN personas p
                ON p.id = d.encargado_id
            ORDER BY d.nombre
        ");

        $departamentos = $stmt->fetchAll();

        $personas = $db->query("
            SELECT id, nombre
            FROM personas
            WHERE activo = 1
            ORDER BY nombre
        ")->fetchAll();

        view('departamentos/index', [
            'departamentos' => $departamentos,
            'personas' => $personas
        ]);
    }

    public static function guardar(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $db = Database::getConnection();

        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $encargadoId = !empty($_POST['encargado_id'])
            ? (int)$_POST['encargado_id']
            : null;

        if ($nombre === '') {
            flash('error', 'El nombre es obligatorio.');
            redirect('departamentos');
        }

        if ($id > 0) {

            $stmt = $db->prepare("
                UPDATE departamentos
                SET nombre = ?,
                    encargado_id = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $nombre,
                $encargadoId,
                $id
            ]);

            flash('success', 'Departamento actualizado.');

        } else {

            $stmt = $db->prepare("
                INSERT INTO departamentos
                (nombre, encargado_id)
                VALUES (?, ?)
            ");

            $stmt->execute([
                $nombre,
                $encargadoId
            ]);

            flash('success', 'Departamento creado.');
        }

        redirect('departamentos');
    }

    public static function cambiarEstado(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);

        $db = Database::getConnection();

        $stmt = $db->prepare("
            UPDATE departamentos
            SET activo = NOT activo
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        flash('success', 'Estado del departamento actualizado.');

        redirect('departamentos');
    }
}