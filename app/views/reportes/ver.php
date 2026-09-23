<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<h1>Reporte <?= e($reporte['folio']) ?></h1>

<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= e($message) ?></div>
<?php endif; ?>
<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= e($message) ?></div>
<?php endif; ?>

<div class="card">
    <p><strong>Fecha del reporte:</strong> <?= e($reporte['fecha_solicitud']) ?></p>
    <p><strong>Departamento:</strong> <?= e($reporte['departamento']) ?></p>
    <p><strong>Encargado:</strong> <?= e($reporte['encargado'] ?? 'Sin encargado') ?></p>
    <p><strong>Solicitante:</strong> <?= $reporte['es_departamento_general'] ? 'Todo el departamento' : e($reporte['persona'] ?? 'No especificado') ?></p>
    <p><strong>Tipo de atención:</strong> <?= e($reporte['tipo']) ?></p>
    <p><strong>Origen:</strong> <?= e($reporte['origen']) ?></p>
    <?php if (!empty($reporte['numero_oficio'])): ?>
        <p><strong>Número de oficio / memorándum:</strong> <?= e($reporte['numero_oficio']) ?></p>
    <?php endif; ?>
    <p><strong>Descripción:</strong></p>
    <div><?= nl2br(e($reporte['descripcion'])) ?></div>
    <p><strong>Estado:</strong> <?= e($reporte['estado']) ?></p>
    <?php if (!empty($reporte['fecha_atencion'])): ?>
        <p><strong>Fecha de atención:</strong> <?= e($reporte['fecha_atencion']) ?></p>
    <?php endif; ?>
</div>

<h2>Cambiar estado</h2>
<form method="POST" action="index.php?route=reportes/estado">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $reporte['id'] ?>">
    <select name="estado">
        <?php foreach (['REGISTRADO'=>'Registrado','EN_PROCESO'=>'En proceso','ATENDIDO'=>'Atendido','CANCELADO'=>'Cancelado'] as $valor => $texto): ?>
            <option value="<?= $valor ?>" <?= $reporte['estado'] === $valor ? 'selected' : '' ?>><?= $texto ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Actualizar estado</button>
</form>

<?php if (Auth::isAdmin()): ?>
    <form method="POST" action="index.php?route=reportes/eliminar" onsubmit="return confirm('¿Eliminar este reporte y sus archivos? Esta acción no se puede deshacer.');">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $reporte['id'] ?>">
        <button type="submit" class="button-danger">Eliminar reporte</button>
    </form>
<?php endif; ?>

<h2>Archivos</h2>
<?php if (empty($archivos)): ?>
    <p>No hay archivos adjuntos.</p>
<?php else: ?>
    <?php foreach ($archivos as $archivo): ?>
        <p><a href="index.php?route=archivo/ver&id=<?= $archivo['id'] ?>" target="_blank"><?= e($archivo['nombre_original']) ?></a></p>
    <?php endforeach; ?>
<?php endif; ?>

<h2>Historial</h2>
<table>
    <thead><tr><th>Fecha</th><th>Usuario</th><th>Estado anterior</th><th>Estado nuevo</th><th>Comentario</th></tr></thead>
    <tbody>
    <?php foreach ($historial as $item): ?>
        <tr>
            <td><?= e($item['fecha']) ?></td>
            <td><?= e($item['usuario'] ?? 'Sistema') ?></td>
            <td><?= e($item['estado_anterior'] ?? '-') ?></td>
            <td><?= e($item['estado_nuevo'] ?? '-') ?></td>
            <td><?= e($item['comentario']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
