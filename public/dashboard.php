<?php

require_once
    __DIR__ . '/../app/config/database.php';

require_once
    __DIR__ . '/../app/helpers/auth.php';

require_once
    __DIR__ . '/../app/helpers/functions.php';


require_login();


$db = db();


$conteos =
    $db->query(
        "

        SELECT

            SUM(
                estado = 'Pendiente'
            ) AS pendientes,

            SUM(
                estado = 'Programado'
            ) AS programados,

            SUM(
                estado = 'Resuelto'
            ) AS resueltos,

            SUM(
                estado = 'Cancelado'
            ) AS cancelados

        FROM reportes

        "
    )->fetch();


$recientes =
    $db->query(
        "

        SELECT

            r.id,
            r.folio,
            r.estado,
            r.fecha_registro,

            d.nombre AS departamento,

            CASE

                WHEN
                    r.es_departamento_general = 1

                THEN
                    'Departamento completo'

                ELSE
                    COALESCE(
                        s.nombre,
                        'Sin solicitante'
                    )

            END AS solicitante

        FROM reportes r

        INNER JOIN departamentos d
            ON d.id =
               r.departamento_id

        LEFT JOIN solicitantes s
            ON s.id =
               r.solicitante_id

        ORDER BY
            r.fecha_registro DESC

        LIMIT 10

        "
    )->fetchAll();

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
    Dashboard
</title>

<link
    rel="manifest"
    href="/manifest.json"
>

<link
    rel="stylesheet"
    href="/assets/css/style.css"
>

</head>


<body>

<header class="topbar">

<strong>
    Sistema de Reportes TI
</strong>


<nav>

<a href="/dashboard.php">
    Inicio
</a>

<a href="/reportes/index.php">
    Reportes
</a>

<a href="/reportes/nuevo.php">
    Nuevo
</a>

<a href="/solicitantes/index.php">
    Solicitantes
</a>

<a href="/departamentos/index.php">
    Departamentos
</a>

<a href="/logout.php">
    Salir
</a>

</nav>

</header>


<main class="container">

<h1>
    Dashboard
</h1>


<div class="cards">


<div class="card">

<span>
    Pendientes
</span>

<strong>
<?= (int)(
    $conteos['pendientes']
    ?? 0
) ?>
</strong>

</div>


<div class="card">

<span>
    Programados
</span>

<strong>
<?= (int)(
    $conteos['programados']
    ?? 0
) ?>
</strong>

</div>


<div class="card">

<span>
    Resueltos
</span>

<strong>
<?= (int)(
    $conteos['resueltos']
    ?? 0
) ?>
</strong>

</div>


<div class="card">

<span>
    Cancelados
</span>

<strong>
<?= (int)(
    $conteos['cancelados']
    ?? 0
) ?>
</strong>

</div>


</div>


<section class="panel">

<h2>
    Reportes recientes
</h2>


<div class="table-wrap">

<table>

<thead>

<tr>

<th>
    Folio
</th>

<th>
    Departamento
</th>

<th>
    Solicitante
</th>

<th>
    Estado
</th>

<th>
    Fecha
</th>

</tr>

</thead>


<tbody>

<?php foreach (
    $recientes
    as $reporte
): ?>

<tr>

<td>

<a
    href="/reportes/ver.php?id=<?= $reporte['id'] ?>"
>

<?= e(
    $reporte['folio']
) ?>

</a>

</td>


<td>
<?= e(
    $reporte['departamento']
) ?>
</td>


<td>
<?= e(
    $reporte['solicitante']
) ?>
</td>


<td>
<?= e(
    $reporte['estado']
) ?>
</td>


<td>
<?= e(
    $reporte['fecha_registro']
) ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</section>


<section class="panel">

<h2>
    Documentos administrativos
</h2>


<div class="actions">

<a
    class="btn primary"
    href="/reportes/pdf.php"
>

Reporte trimestral actual

</a>


<a
    class="btn"
    href="/reportes/pdf.php?tipo=firmas"
>

Departamentos y firmas

</a>

</div>

</section>


</main>


<script
    src="/assets/js/app.js"
></script>

</body>

</html>