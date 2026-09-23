<?php

require_once
    __DIR__ . '/../app/config/database.php';

require_once
    __DIR__ . '/../app/helpers/auth.php';

require_once
    __DIR__ . '/../app/helpers/functions.php';


if (
    !empty(
        $_SESSION['usuario_id']
    )
) {

    redirect(
        '/dashboard.php'
    );
}


$error = null;


if (
    $_SERVER['REQUEST_METHOD']
    === 'POST'
) {

    verify_csrf(
        $_POST['csrf'] ?? null
    );


    $correo =
        trim(
            $_POST[
                'correo'
            ] ?? ''
        );


    $password =
        $_POST[
            'password'
        ] ?? '';


    $stmt =
        db()->prepare(
            "

            SELECT *

            FROM usuarios

            WHERE correo = ?

            AND activo = 1

            LIMIT 1

            "
        );


    $stmt->execute([
        $correo
    ]);


    $usuario =
        $stmt->fetch();


    if (
        $usuario &&
        password_verify(
            $password,
            $usuario[
                'password_hash'
            ]
        )
    ) {

        login_user(
            $usuario
        );


        redirect(
            '/dashboard.php'
        );
    }


    $error =
        'Correo o contraseña incorrectos.';
}

?>

<!doctype html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>
    Iniciar sesión
</title>

<link
    rel="stylesheet"
    href="/assets/css/style.css"
>

</head>


<body class="login-page">

<main class="login-card">

<h1>
    Reportes TI
</h1>


<?php if ($error): ?>

<div class="alert error">

<?= e($error) ?>

</div>

<?php endif; ?>


<form
    method="POST"
>


<input
    type="hidden"
    name="csrf"
    value="<?= e(
        csrf_token()
    ) ?>"
>


<label>
    Correo
</label>

<input
    type="email"
    name="correo"
    required
>


<label>
    Contraseña
</label>

<input
    type="password"
    name="password"
    required
>


<button
    class="btn primary"
    type="submit"
>

Iniciar sesión

</button>


</form>

</main>

</body>

</html>