<?php

class TipoProblemaController
{
    public static function index(): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();

        $tipos = $db->query("
            SELECT *
            FROM tipos_problema
            ORDER BY nombre
        ")->fetchAll();

        view('tipos/index', [
            'tipos' => $tipos
        ]);
    }

    public static function guardar(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $db = Database::getConnection();

        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($nombre === '') {
            flash('error', 'El nombre es obligatorio.');
            redirect('tipos');
        }

        if ($id > 0) {

            $stmt = $db->prepare("
                UPDATE tipos_problema
                SET nombre = ?,
                    descripcion = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $nombre,
                $descripcion ?: null,
                $id
            ]);

        } else {

            $stmt = $db->prepare("
                INSERT INTO tipos_problema
                (nombre, descripcion)
                VALUES (?, ?)
            ");

            $stmt->execute([
                $nombre,
                $descripcion ?: null
            ]);
        }

        flash('success', 'Tipo de servicio guardado.');

        redirect('tipos');
    }

    public static function cambiarEstado(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $id = (int)$_POST['id'];

        $db = Database::getConnection();

        $stmt = $db->prepare("
            UPDATE tipos_problema
            SET activo = NOT activo
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        redirect('tipos');
    }
    public static function eliminar(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Atención inválida.');
            redirect('tipos');
        }

        $db = Database::getConnection();
        try {
            $stmt = $db->prepare('SELECT nombre FROM tipos_problema WHERE id = ?');
            $stmt->execute([$id]);
            $tipo = $stmt->fetch();
            if (!$tipo) {
                throw new RuntimeException('Atención no encontrada.');
            }

            $stmt = $db->prepare('SELECT COUNT(*) FROM reportes WHERE tipo_problema_id = ?');
            $stmt->execute([$id]);
            if ((int)$stmt->fetchColumn() > 0) {
                throw new RuntimeException('No se puede eliminar porque esta atención está asociada a uno o más reportes. Puede desactivarla.');
            }

            $stmt = $db->prepare('DELETE FROM tipos_problema WHERE id = ?');
            $stmt->execute([$id]);
            flash('success', 'Atención eliminada.');
        } catch (Throwable $e) {
            flash('error', 'No se pudo eliminar la atención: ' . $e->getMessage());
        }

        redirect('tipos');
    }

}