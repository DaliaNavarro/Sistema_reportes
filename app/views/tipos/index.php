<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<h1>Tipos de atención</h1>

<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= e($message) ?></div>
<?php endif; ?>

<?php if (Auth::isAdmin()): ?>

<form method="POST" action="index.php?route=tipos/guardar">

    <?= csrf_field() ?>

    <input type="hidden" name="id" value="0">

    <label>
        Atención
        <input
            type="text"
            name="nombre"
            required
            maxlength="120"
        >
    </label>

    <label>
        Descripción
        <textarea
            name="descripcion"
            rows="4"
        ></textarea>
    </label>

    <button type="submit">
        Crear tipo de atención
    </button>

</form>

<hr>

<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Atención</th>
            <th>Descripción</th>
            <th>Estado</th>

            <?php if (Auth::isAdmin()): ?>
                <th>Acciones</th>
            <?php endif; ?>
        </tr>
    </thead>

    <tbody>

    <?php foreach ($tipos as $tipo): ?>

        <tr>

            <td><?= e($tipo['nombre']) ?></td>

            <td><?= e($tipo['descripcion'] ?? '') ?></td>

            <td>
                <?= $tipo['activo'] ? 'Activo' : 'Inactivo' ?>
            </td>

            <?php if (Auth::isAdmin()): ?>

            <td>

                <form
                    method="POST"
                    action="index.php?route=tipos/guardar"
                >

                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?= $tipo['id'] ?>"
                    >

                    <input
                        type="text"
                        name="nombre"
                        value="<?= e($tipo['nombre']) ?>"
                        required
                        maxlength="120"
                    >

                    <textarea
                        name="descripcion"
                        rows="2"
                    ><?= e($tipo['descripcion'] ?? '') ?></textarea>

                    <button type="submit">
                        Guardar
                    </button>

                </form>

                <form
                    method="POST"
                    action="index.php?route=tipos/estado"
                >

                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?= $tipo['id'] ?>"
                    >

                    <button type="submit">
                        Activar / desactivar
                    </button>

                </form>

                <form method="POST" action="index.php?route=tipos/eliminar" onsubmit="return confirm('¿Eliminar esta atención? Solo puede eliminarse si no está asociada a reportes.');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $tipo['id'] ?>">
                    <button type="submit" class="button-danger">Eliminar</button>
                </form>

            </td>

            <?php endif; ?>

        </tr>

    <?php endforeach; ?>

    </tbody>
</table>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
