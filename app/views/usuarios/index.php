<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="container">

    <h1>Usuarios</h1>

    <?php if ($success = flash('success')): ?>
        <div class="alert alert-success">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error = flash('error')): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>


    <div class="card">

        <h2>Nuevo usuario</h2>

        <form method="post" action="index.php?route=usuarios/guardar">

            <?= csrf_field() ?>

            <input type="hidden" name="id" value="0">

            <div class="form-group">

                <label>Nombre de usuario</label>

                <input
                    type="text"
                    name="nombre"
                    required
                    maxlength="120"
                    autocomplete="username"
                >

            </div>


            <div class="form-group">

                <label>Contraseña</label>

                <input
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                >

            </div>


            <div class="form-group">

                <label>Rol</label>

                <select name="rol" required>

                    <option value="Tecnico">
                        Técnico
                    </option>

                    <option value="Administrador">
                        Administrador
                    </option>

                </select>

            </div>

<p></p>
            <button type="submit">
                Crear usuario
            </button>

        </form>

    </div>


    <div class="card">

        <h2>Usuarios registrados</h2>

        <table>

            <thead>

                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Fecha de creación</th>
                    <th>Acciones</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach ($usuarios as $usuario): ?>

                <tr>

                    <td>
                        <?= e($usuario['nombre']) ?>
                    </td>

                    <td>
                        <?= e($usuario['rol']) ?>
                    </td>

                    <td>

                        <?php if ($usuario['activo']): ?>

                            <span>Activo</span>

                        <?php else: ?>

                            <span>Inactivo</span>

                        <?php endif; ?>

                    </td>

                    <td>
                        <?= e($usuario['fecha_creacion']) ?>
                    </td>

                    <td>

                        <details>

                            <summary>Editar</summary>

                            <form
                                method="post"
                                action="index.php?route=usuarios/guardar"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$usuario['id'] ?>"
                                >


                                <label>
                                    Nombre de usuario
                                </label>

                                <input
                                    type="text"
                                    name="nombre"
                                    value="<?= e($usuario['nombre']) ?>"
                                    required
                                    maxlength="120"
                                >


                                <label>
                                    Nueva contraseña
                                </label>

                                <input
                                    type="password"
                                    name="password"
                                    autocomplete="new-password"
                                    placeholder="Dejar vacío para conservar"
                                >


                                <label>
                                    Rol
                                </label>

                                <select name="rol">

                                    <option
                                        value="Tecnico"
                                        <?= $usuario['rol'] === 'Tecnico'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Técnico
                                    </option>

                                    <option
                                        value="Administrador"
                                        <?= $usuario['rol'] === 'Administrador'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Administrador
                                    </option>

                                </select>


                                <button type="submit">
                                    Guardar cambios
                                </button>

                            </form>

                        </details>


                        <?php if ($usuario['id'] != Auth::user()['id']): ?>

                            <form
                                method="post"
                                action="index.php?route=usuarios/estado"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$usuario['id'] ?>"
                                >

                                <button type="submit">

                                    <?= $usuario['activo']
                                        ? 'Desactivar'
                                        : 'Activar'
                                    ?>

                                </button>

                            </form>


                            <form
                                method="post"
                                action="index.php?route=usuarios/eliminar"
                                onsubmit="return confirm(
                                    '¿Eliminar este usuario?'
                                );"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$usuario['id'] ?>"
                                >

                                <button type="submit">
                                    Eliminar
                                </button>

                            </form>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>