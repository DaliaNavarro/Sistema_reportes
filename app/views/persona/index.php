<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<h1>Personas</h1>
<?php if ($message = flash('success')): ?><div class="alert success"><?= e($message) ?></div><?php endif; ?>
<?php if ($message = flash('error')): ?><div class="alert error"><?= e($message) ?></div><?php endif; ?>

<?php if (Auth::isAdmin()): ?>
<h2>Registrar persona</h2>
<form method="POST" action="index.php?route=personas/guardar">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="0">
    <label>Departamento<select name="departamento_id" required><option value="">Seleccione</option><?php foreach ($departamentos as $departamento): ?><option value="<?= $departamento['id'] ?>"><?= e($departamento['nombre']) ?></option><?php endforeach; ?></select></label>
    <label>Nombre<input type="text" name="nombre" required maxlength="150"></label>
    <button type="submit">Registrar</button>
</form>
<?php endif; ?>

<h2>Personas registradas</h2>
<table>
<thead><tr><th>Nombre</th><th>Departamento</th><th>Estado</th><?php if (Auth::isAdmin()): ?><th>Acciones</th><?php endif; ?></tr></thead>
<tbody>
<?php foreach ($personas as $persona): ?>
<tr>
    <td><?= e($persona['nombre']) ?></td>
    <td><?= e($persona['departamento']) ?></td>
    <td><?= $persona['activo'] ? 'Activo' : 'Inactivo' ?></td>
    <?php if (Auth::isAdmin()): ?>
    <td>
        <form method="POST" action="index.php?route=personas/guardar">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $persona['id'] ?>">
            <select name="departamento_id" required><?php foreach ($departamentos as $departamento): ?><option value="<?= $departamento['id'] ?>" <?= (int)$persona['departamento_id'] === (int)$departamento['id'] ? 'selected' : '' ?>><?= e($departamento['nombre']) ?></option><?php endforeach; ?></select>
            <input type="text" name="nombre" value="<?= e($persona['nombre']) ?>" required maxlength="150">
            <button type="submit">Guardar</button>
        </form>
        <form method="POST" action="index.php?route=personas/estado">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $persona['id'] ?>">
            <button type="submit">Activar / desactivar</button>
        </form>
        <form method="POST" action="index.php?route=personas/eliminar" onsubmit="return confirm('¿Eliminar esta persona? Los reportes existentes conservarán el registro, pero quedarán sin solicitante.');">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $persona['id'] ?>">
            <button type="submit" class="button-danger">Eliminar</button>
        </form>
    </td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
