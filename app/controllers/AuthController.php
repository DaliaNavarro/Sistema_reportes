<?php

class AuthController
{
    public static function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            verify_csrf();

            $nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($nombreUsuario === '' || $password === '') {
                flash('error', 'Nombre de usuario y contraseña son obligatorios.');
                redirect('login');
            }

            if (Auth::login($nombreUsuario, $password)) {
                redirect('dashboard');
            }

            flash('error', 'Nombre de usuario o contraseña incorrectos.');
            redirect('login');
        }

        view('auth/login');
    }

    public static function logout(): void
    {
        Auth::logout();
        redirect('login');
    }
}
