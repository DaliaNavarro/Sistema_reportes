<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body>

<main class="login">

    <section class="card">

        <h1>Sistema de Reportes TI</h1>

        <?php if ($message = flash('error')): ?>
            <div class="alert error"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?route=login">

            <?= csrf_field() ?>

            <label>
                Nombre de usuario
                <input
                    type="text"
                    name="nombre_usuario"
                    required
                    autocomplete="username"
                >
            </label>

            <label>
                Contraseña
                <input
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
            </label>

            <button type="submit">
                Iniciar sesión
            </button>

        </form>

    </section>

</main>

</body>
</html>
