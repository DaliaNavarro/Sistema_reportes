<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<h1>Departamentos</h1>

<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= e($message) ?></div>
<?php endif; ?>

<?php if (Auth::isAdmin()): ?>

<form method="POST" action="index.php?route=departamentos/guardar">

    <?= csrf_field() ?>

    <input type="hidden" name="id" value="0">

    <label>
        Nombre
        <input
            type="text"
            name="nombre"
            required
            maxlength="150"
        >
    </label>

    <label>
        Encargado
        <select name="encargado_id">
            <option value="">Sin encargado</option>

            <?php foreach ($personas as $persona): ?>
                <option value="<?= $persona['id'] ?>">
                    <?= e($persona['nombre']) ?>
                </option>
            <?php endforeach; ?>

        </select>
    </label>

    <button type="submit">
        Crear departamento
    </button>

</form>

<hr>

<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Departamento</th>
            <th>Encargado</th>
            <th>Estado</th>

            <?php if (Auth::isAdmin()): ?>
                <th>Acciones</th>
            <?php endif; ?>

        </tr>
    </thead>

    <tbody>

    <?php foreach ($departamentos as $departamento): ?>

        <tr>
            <td><?= e($departamento['nombre']) ?></td>

            <td>
                <?= e($departamento['encargado'] ?? 'Sin encargado') ?>
            </td>

            <td>
                <?= $departamento['activo'] ? 'Activo' : 'Inactivo' ?>
            </td>

            <?php if (Auth::isAdmin()): ?>

            <td>

                <form
                    method="POST"
                    action="index.php?route=departamentos/guardar"
                >

                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?= $departamento['id'] ?>"
                    >

                    <input
                        type="text"
                        name="nombre"
                        value="<?= e($departamento['nombre']) ?>"
                        required
                    >

                    <select name="encargado_id">

                        <option value="">
                            Sin encargado
                        </option>

                        <?php foreach ($personas as $persona): ?>

                            <option
                                value="<?= $persona['id'] ?>"
                                <?= $departamento['encargado_id'] == $persona['id']
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= e($persona['nombre']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <button type="submit">
                        Guardar
                    </button>

                </form>

                <form
                    method="POST"
                    action="index.php?route=departamentos/estado"
                >

                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?= $departamento['id'] ?>"
                    >

                    <button type="submit">
                        Activar / desactivar
                    </button>

                </form>

            </td>

            <?php endif; ?>

        </tr>

    <?php endforeach; ?>

    </tbody>
</table>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>