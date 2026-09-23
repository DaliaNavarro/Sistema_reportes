document.addEventListener(
    'DOMContentLoaded',
    () => {

        const checkbox =
            document.getElementById(
                'departamento_general'
            );

        const container =
            document.getElementById(
                'solicitante-container'
            );

        const departamento =
            document.getElementById(
                'departamento_id'
            );

        const solicitante =
            document.getElementById(
                'solicitante_id'
            );


        function actualizarFormulario()
        {
            const general =
                checkbox.checked;


            if (general) {

                container.style.display =
                    'none';

                solicitante.required =
                    false;

                solicitante.value =
                    '';

            } else {

                container.style.display =
                    'block';

                solicitante.required =
                    true;
            }


            const departamentoId =
                departamento.value;


            Array.from(
                solicitante.options
            ).forEach(
                option => {

                    if (!option.value) {
                        return;
                    }


                    option.hidden =
                        departamentoId &&
                        option.dataset
                            .departamento
                        !== departamentoId;

                }
            );
        }


        checkbox.addEventListener(
            'change',
            actualizarFormulario
        );


        departamento.addEventListener(
            'change',
            actualizarFormulario
        );


        actualizarFormulario();

    }
);