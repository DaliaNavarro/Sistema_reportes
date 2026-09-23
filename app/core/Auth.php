<?php

class Auth
{
    public static function login(
        string $nombreUsuario,
        string $password
    ): bool {

        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT *
            FROM usuarios_sistema
            WHERE nombre = ?
              AND activo = 1
            LIMIT 1
        ");

        $stmt->execute([$nombreUsuario]);

        $usuario = $stmt->fetch();

        if (!$usuario) {
            return false;
        }

        if (!password_verify($password, $usuario['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'rol' => $usuario['rol']
        ];

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function user(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::check()
            && $_SESSION['usuario']['rol'] === 'Administrador';
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('login');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();

        if (!self::isAdmin()) {
            http_response_code(403);
            exit('No tienes permisos para realizar esta operación.');
        }
    }
}
