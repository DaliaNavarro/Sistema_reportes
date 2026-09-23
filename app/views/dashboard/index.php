<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<h1>Dashboard</h1>

<div class="dashboard">
    <a class="dashboard-card" href="index.php?route=reportes">
        <div class="card">
            <h2><?= $total ?></h2>
            <p>Total de reportes</p>
        </div>
    </a>

    <a class="dashboard-card" href="index.php?route=reportes&estado=REGISTRADO">
        <div class="card">
            <h2><?= $pendientes ?></h2>
            <p>Registrados</p>
            <small>Ver reportes registrados</small>
        </div>
    </a>

    <a class="dashboard-card" href="index.php?route=reportes&estado=EN_PROCESO">
        <div class="card">
            <h2><?= $proceso ?></h2>
            <p>En proceso</p>
            <small>Ver reportes en proceso</small>
        </div>
    </a>

    <a class="dashboard-card" href="index.php?route=reportes&estado=ATENDIDO">
        <div class="card">
            <h2><?= $atendidos ?></h2>
            <p>Atendidos</p>
            <small>Ver reportes atendidos</small>
        </div>
    </a>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
