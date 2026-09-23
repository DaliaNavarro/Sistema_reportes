document.addEventListener('DOMContentLoaded', () => {

    const general = document.getElementById(
        'es_departamento_general'
    );

    const personaContainer = document.getElementById(
        'persona-container'
    );

    const departamento = document.getElementById(
        'departamento_id'
    );

    const persona = document.getElementById(
        'persona_id'
    );


    function actualizarPersonas() {

        if (!departamento || !persona) {
            return;
        }

        const departamentoId = departamento.value;

        Array.from(persona.options).forEach(option => {

            if (!option.value) {
                return;
            }

            option.hidden =
                option.dataset.departamento !== departamentoId;

        });

        persona.value = '';
    }


    function actualizarGeneral() {

        if (!general || !personaContainer) {
            return;
        }

        if (general.checked) {

            personaContainer.style.display = 'none';

            if (persona) {
                persona.value = '';
            }

        } else {

            personaContainer.style.display = 'block';

        }
    }


    if (departamento) {
        departamento.addEventListener(
            'change',
            actualizarPersonas
        );
    }


    if (general) {
        general.addEventListener(
            'change',
            actualizarGeneral
        );
    }


    actualizarPersonas();
    actualizarGeneral();

});