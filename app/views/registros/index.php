<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<h1>Registros</h1>

<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= e($message) ?></div>
<?php endif; ?>

<h2>Personas</h2>

<?php if (Auth::isAdmin()): ?>
    <form
        method="POST"
        action="index.php?route=registros/personas/importar"
        enctype="multipart/form-data"
    >
        <?= csrf_field() ?>

        <label>
            Archivo XLSX
            <input
                type="file"
                name="archivo"
                accept=".xlsx"
                required
            >
        </label>

        <button type="submit">
            Importar Personas
        </button>
    </form>
<?php endif; ?>

<p>
    <a href="index.php?route=registros/personas/exportar">
        Exportar Personas
    </a>
</p>

<p>
    Estructura requerida:
    <strong>Nombre | Departamento</strong>
</p>

<hr>

<h2>Atenciones</h2>

<?php if (Auth::isAdmin()): ?>
    <form
        method="POST"
        action="index.php?route=registros/atenciones/importar"
        enctype="multipart/form-data"
    >
        <?= csrf_field() ?>

        <label>
            Archivo XLSX
            <input
                type="file"
                name="archivo"
                accept=".xlsx"
                required
            >
        </label>

        <button type="submit">
            Importar Atenciones
        </button>
    </form>
<?php endif; ?>

<p>
    <a href="index.php?route=registros/atenciones/exportar">
        Exportar Atenciones
    </a>
</p>

<p>
    Estructura requerida:
    <strong>Atención | Descripción</strong>
</p>

<hr>

<h2>Reportes</h2>

<?php if (Auth::isAdmin()): ?>
<form method="POST" action="index.php?route=registros/reportes/importar" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <label>Archivo XLSX de reportes
        <input type="file" name="archivo" accept=".xlsx" required>
    </label>
    <button type="submit">Importar reportes</button>
</form>
<?php endif; ?>

<p><a class="button" href="index.php?route=registros/reportes/exportar">Exportar reportes</a></p>

<p>Columnas para importación: <strong>Folio | Fecha | Departamento | Persona | Todo el departamento | Atención | Origen | Número de oficio | Descripción | Estado | Fecha de atención</strong>.</p>

<hr>

<h2>Concentrados</h2>

<p>
    <a href="index.php?route=registros/trimestral">
        Generar Concentrado Trimestral
    </a>
</p>

<p>
    <a href="index.php?route=registros/anual">
        Generar Concentrado Anual
    </a>
</p>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
