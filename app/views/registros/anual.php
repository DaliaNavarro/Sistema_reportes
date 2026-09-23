<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<h1>Concentrado anual</h1>

<p>
    Seleccione el año que desea exportar.
</p>

<form
    method="GET"
    action="index.php"
>

    <input
        type="hidden"
        name="route"
        value="registros/anual/exportar"
    >

    <label>
        Año
        <input
            type="number"
            name="anio"
            value="<?= $anioActual ?>"
            min="2000"
            max="2100"
            required
        >
    </label>

    <button type="submit">
        Generar concentrado anual
    </button>

</form>

<p>
    <a href="index.php?route=registros">
        Regresar a Registros
    </a>
</p>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
