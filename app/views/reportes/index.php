<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<h1>Reportes</h1>

<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= e($message) ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= e($message) ?></div>
<?php endif; ?>

<p>
    <a class="button" href="index.php?route=reportes/nuevo">+ Nuevo reporte</a>
    <?php if ($estadoFiltro): ?>
        <a class="button secondary" href="index.php?route=reportes">Ver todos</a>
    <?php endif; ?>
</p>

<?php if ($estadoFiltro): ?>
    <p><strong>Filtro:</strong> <?= e($estadoFiltro) ?></p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Folio</th><th>Fecha</th><th>Departamento</th><th>Solicitante</th>
            <th>Tipo</th><th>Origen</th><th>Estado</th><th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($reportes as $reporte): ?>
        <tr>
            <td><?= e($reporte['folio']) ?></td>
            <td><?= e($reporte['fecha_solicitud']) ?></td>
            <td><?= e($reporte['departamento']) ?></td>
            <td>
                <?= $reporte['es_departamento_general'] ? 'Todo el departamento' : e($reporte['persona'] ?? 'No especificado') ?>
            </td>
            <td><?= e($reporte['tipo']) ?></td>
            <td><?= e($reporte['origen']) ?></td>
            <td><?= e($reporte['estado']) ?></td>
            <td>
                <a href="index.php?route=reportes/ver&id=<?= $reporte['id'] ?>">Ver</a>
                <?php if (Auth::isAdmin()): ?>
                    <form method="POST" action="index.php?route=reportes/eliminar" style="display:inline" onsubmit="return confirm('¿Eliminar este reporte y sus archivos? Esta acción no se puede deshacer.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $reporte['id'] ?>">
                        <button type="submit" class="button-danger">Eliminar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
