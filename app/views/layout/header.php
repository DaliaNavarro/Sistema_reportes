<?php

$user = Auth::user();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="theme-color"
        content="#1f2937"
    >

    <link
        rel="manifest"
        href="manifest.webmanifest"
    >

    <link
        rel="stylesheet"
        href="assets/css/app.css"
    >

    <title>Sistema de Reportes TI</title>

</head>

<body>

<header class="navbar">

    <strong>
        Reportes TI
    </strong>

    <?php if ($user): ?>

        <nav>

            <a href="index.php?route=dashboard">
                Inicio
            </a>

            <a href="index.php?route=reportes">
                Reportes
            </a>

            <a href="index.php?route=personas">
                Personas
            </a>

            <?php if (Auth::isAdmin()): ?>

                <a href="index.php?route=departamentos">
                    Departamentos
                </a>

                <a href="index.php?route=tipos">
                    Atenciones
                </a>
                
                <a href="index.php?route=usuarios">
                    Usuarios
                </a>    

            <?php endif; ?>

            <a href="index.php?route=registros">
                Registros
            </a>

            <a href="index.php?route=logout">
                Cerrar sesión
            </a>

        </nav>

    <?php endif; ?>

</header>

<main class="container">