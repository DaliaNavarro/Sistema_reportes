<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<h1>Nuevo reporte</h1>

<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= e($message) ?></div>
<?php endif; ?>

<form method="POST" action="index.php?route=reportes/guardar" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <label>
        Fecha del reporte
        <input
            type="datetime-local"
            name="fecha_solicitud"
            value="<?= date('Y-m-d\\TH:i') ?>"
            required
        >
    </label>

    <label>
        Departamento
        <select name="departamento_id" id="departamento_id" required>
            <option value="">Seleccione</option>
            <?php foreach ($departamentos as $departamento): ?>
                <option value="<?= $departamento['id'] ?>"><?= e($departamento['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label class="checkbox">
        <input type="checkbox" name="es_departamento_general" id="es_departamento_general" value="1">
        Para todo el departamento
    </label>

    <div id="persona-container">
        <label>
            Persona solicitante
            <select name="persona_id" id="persona_id">
                <option value="">Seleccione una persona</option>
                <?php foreach ($personas as $persona): ?>
                    <option value="<?= $persona['id'] ?>" data-departamento="<?= $persona['departamento_id'] ?>">
                        <?= e($persona['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <label>
        Tipo de atención
        <select name="tipo_problema_id" id="tipo_problema_id" required>
            <option value="">Seleccione</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?= $tipo['id'] ?>" data-descripcion="<?= e($tipo['descripcion'] ?? '') ?>">
                    <?= e($tipo['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <fieldset>
        <legend>Origen</legend>
        <label><input type="radio" name="origen" value="INFORMAL" checked> Informal</label>
        <label><input type="radio" name="origen" value="MEMORANDUM"> Memorándum</label>
    </fieldset>

    <label>
        Número de oficio / memorándum
        <input type="text" name="numero_oficio" maxlength="100">
    </label>

    <label>
        Descripción
        <textarea name="descripcion" id="descripcion" rows="6" required></textarea>
    </label>

    <label>
        Estado inicial
        <select name="estado" required>
            <option value="REGISTRADO">Registrado</option>
            <option value="EN_PROCESO">En proceso</option>
            <option value="ATENDIDO">Atendido</option>
            <option value="CANCELADO">Cancelado</option>
        </select>
    </label>

    <label>
        Memorándum / documento PDF (opcional)
        <input type="file" name="memorandum" id="memorandum" accept=".pdf,application/pdf">
    </label>

    <button type="submit">Registrar reporte</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipo = document.getElementById('tipo_problema_id');
    const descripcion = document.getElementById('descripcion');
    const departamento = document.getElementById('departamento_id');
    const persona = document.getElementById('persona_id');
    const general = document.getElementById('es_departamento_general');
    const personaContainer = document.getElementById('persona-container');

    function actualizarDescripcion() {
        const option = tipo.options[tipo.selectedIndex];
        if (option && option.value !== '') {
            descripcion.value = option.dataset.descripcion || '';
        }
    }

    function filtrarPersonas() {
        const departamentoId = departamento.value;
        Array.from(persona.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = option.dataset.departamento !== departamentoId;
        });
        const selected = persona.options[persona.selectedIndex];
        if (selected && selected.hidden) persona.value = '';
    }

    function actualizarPersona() {
        personaContainer.style.display = general.checked ? 'none' : 'block';
        persona.required = !general.checked;
        if (general.checked) persona.value = '';
    }

    tipo.addEventListener('change', actualizarDescripcion);
    departamento.addEventListener('change', filtrarPersonas);
    general.addEventListener('change', actualizarPersona);
    filtrarPersonas();
    actualizarPersona();
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
