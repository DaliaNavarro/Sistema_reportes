<?php

class UsuarioController
{
    public static function index(): void
    {
        Auth::requireAdmin();

        $db = Database::getConnection();

        $usuarios = $db->query("
            SELECT
                id,
                nombre,
                rol,
                activo,
                fecha_creacion,
                fecha_actualizacion
            FROM usuarios_sistema
            ORDER BY nombre
        ")->fetchAll();

        view('usuarios/index', [
            'usuarios' => $usuarios
        ]);
    }

    public static function guardar(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $db = Database::getConnection();

        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $rol = $_POST['rol'] ?? 'Tecnico';
        $password = $_POST['password'] ?? '';

        if ($nombre === '') {
            flash('error', 'El nombre de usuario es obligatorio.');
            redirect('usuarios');
        }

        if (!in_array($rol, ['Administrador', 'Tecnico'], true)) {
            flash('error', 'El rol seleccionado no es válido.');
            redirect('usuarios');
        }

        try {

            if ($id > 0) {

                if ($password !== '') {

                    $stmt = $db->prepare("
                        UPDATE usuarios_sistema
                        SET nombre = ?,
                            rol = ?,
                            password_hash = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $nombre,
                        $rol,
                        password_hash($password, PASSWORD_DEFAULT),
                        $id
                    ]);

                } else {

                    $stmt = $db->prepare("
                        UPDATE usuarios_sistema
                        SET nombre = ?,
                            rol = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $nombre,
                        $rol,
                        $id
                    ]);
                }

                flash('success', 'Usuario actualizado.');

            } else {

                if ($password === '') {
                    flash(
                        'error',
                        'La contraseña es obligatoria para crear un usuario.'
                    );

                    redirect('usuarios');
                }

                $stmt = $db->prepare("
                    INSERT INTO usuarios_sistema
                    (
                        nombre,
                        password_hash,
                        rol,
                        activo
                    )
                    VALUES (?, ?, ?, 1)
                ");

                $stmt->execute([
                    $nombre,
                    password_hash($password, PASSWORD_DEFAULT),
                    $rol
                ]);

                flash('success', 'Usuario creado.');
            }

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {
                flash(
                    'error',
                    'Ya existe un usuario con ese nombre.'
                );
            } else {
                throw $e;
            }
        }

        redirect('usuarios');
    }

    public static function cambiarEstado(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);

        if ($id === (int)Auth::user()['id']) {
            flash(
                'error',
                'No puedes desactivar tu propio usuario.'
            );

            redirect('usuarios');
        }

        $db = Database::getConnection();

        $stmt = $db->prepare("
            UPDATE usuarios_sistema
            SET activo = NOT activo
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        flash('success', 'Estado del usuario actualizado.');

        redirect('usuarios');
    }

    public static function eliminar(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);

        if ($id === (int)Auth::user()['id']) {
            flash(
                'error',
                'No puedes eliminar tu propio usuario.'
            );

            redirect('usuarios');
        }

        $db = Database::getConnection();

        $stmt = $db->prepare("
            DELETE FROM usuarios_sistema
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        flash('success', 'Usuario eliminado.');

        redirect('usuarios');
    }
}