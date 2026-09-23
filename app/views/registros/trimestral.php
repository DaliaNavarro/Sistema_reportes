<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<h1>Concentrado trimestral</h1>

<p>
    Seleccione el año y trimestre que desea exportar.
</p>

<form
    method="GET"
    action="index.php"
>

    <input
        type="hidden"
        name="route"
        value="registros/trimestral/exportar"
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

    <label>
        Trimestre

        <select name="trimestre" required>
            <option value="1">Primer trimestre (enero-marzo)</option>
            <option value="2">Segundo trimestre (abril-junio)</option>
            <option value="3">Tercer trimestre (julio-septiembre)</option>
            <option value="4">Cuarto trimestre (octubre-diciembre)</option>
        </select>
    </label>

    <button type="submit">
        Generar concentrado trimestral
    </button>

</form>

<p>
    <a href="index.php?route=registros">
        Regresar a Registros
    </a>
</p>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
