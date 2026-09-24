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
</p>

<form class="filters-panel" method="GET" action="index.php">
    <input type="hidden" name="route" value="reportes">

    <div class="filters-grid">
        <label>
            Departamento
            <select name="departamento_id">
                <option value="">Todos</option>
                <?php foreach ($departamentos as $departamento): ?>
                    <option
                        value="<?= (int)$departamento['id'] ?>"
                        <?= $departamentoFiltro == $departamento['id'] ? 'selected' : '' ?>
                    >
                        <?= e($departamento['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Nombre del solicitante
            <input
                type="text"
                name="solicitante"
                value="<?= e($solicitanteFiltro) ?>"
                placeholder="Buscar por nombre"
            >
        </label>

        <label>
            Tipo de atención
            <select name="tipo_problema_id">
                <option value="">Todos</option>
                <?php foreach ($tipos as $tipo): ?>
                    <option
                        value="<?= (int)$tipo['id'] ?>"
                        <?= $tipoFiltro == $tipo['id'] ? 'selected' : '' ?>
                    >
                        <?= e($tipo['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Origen
            <select name="origen">
                <option value="">Todos</option>
                <option value="INFORMAL" <?= $origenFiltro === 'INFORMAL' ? 'selected' : '' ?>>Informal</option>
                <option value="MEMORANDUM" <?= $origenFiltro === 'MEMORANDUM' ? 'selected' : '' ?>>Memorándum</option>
            </select>
        </label>

        <label>
            Estado
            <select name="estado">
                <option value="">Todos</option>
                <option value="REGISTRADO" <?= $estadoFiltro === 'REGISTRADO' ? 'selected' : '' ?>>Registrado</option>
                <option value="EN_PROCESO" <?= $estadoFiltro === 'EN_PROCESO' ? 'selected' : '' ?>>En proceso</option>
                <option value="ATENDIDO" <?= $estadoFiltro === 'ATENDIDO' ? 'selected' : '' ?>>Atendido</option>
                <option value="CANCELADO" <?= $estadoFiltro === 'CANCELADO' ? 'selected' : '' ?>>Cancelado</option>
            </select>
        </label>
    </div>

    <div class="actions">
        <button class="btn primary" type="submit">Filtrar</button>
        <a class="btn" href="index.php?route=reportes">Limpiar filtros</a>
    </div>
</form>

<?php
$filtrosActivos = $estadoFiltro || $departamentoFiltro || $tipoFiltro || $origenFiltro || $solicitanteFiltro !== '';
?>

<?php if ($filtrosActivos): ?>
    <p><strong>Filtros aplicados:</strong> se muestran únicamente los reportes que coinciden con la búsqueda.</p>
<?php endif; ?>

<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>Folio</th>
            <th>Fecha</th>
            <th>Departamento</th>
            <th>Solicitante</th>
            <th>Tipo</th>
            <th>Origen</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($reportes)): ?>
        <tr>
            <td colspan="8">No se encontraron reportes con los filtros seleccionados.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($reportes as $reporte): ?>
            <tr>
                <td><?= e($reporte['folio']) ?></td>
                <td><?= e($reporte['fecha_solicitud']) ?></td>
                <td><?= e($reporte['departamento']) ?></td>
                <td>
                    <?php if ($reporte['es_departamento_general']): ?>
                        <?= e($reporte['encargado'] ? 'Encargado: ' . $reporte['encargado'] : 'Todo el departamento') ?>
                    <?php else: ?>
                        <?= e($reporte['persona'] ?? 'No especificado') ?>
                    <?php endif; ?>
                </td>
                <td><?= e($reporte['tipo']) ?></td>
                <td><?= e($reporte['origen']) ?></td>
                <td><?= e($reporte['estado']) ?></td>
                <td>
                    <a href="index.php?route=reportes/ver&id=<?= (int)$reporte['id'] ?>">Ver</a>
                    <?php if (Auth::isAdmin()): ?>
                        <form method="POST" action="index.php?route=reportes/eliminar" style="display:inline" onsubmit="return confirm('¿Eliminar este reporte y sus archivos? Esta acción no se puede deshacer.');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$reporte['id'] ?>">
                            <button type="submit" class="button-danger">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
