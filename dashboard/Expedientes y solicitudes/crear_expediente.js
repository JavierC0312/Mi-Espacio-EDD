document.addEventListener("DOMContentLoaded", function() {

    // 1. Referencias al DOM
    const selectConvocatoria = document.getElementById('select-convocatoria');
    const periodoNombreEl = document.getElementById('periodo-nombre');
    const form = document.getElementById('form-crear-expediente');
    const formError = document.getElementById('form-error');

    // 2. Leer el ID del período de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const idPeriodo = urlParams.get('periodo');

    if (!idPeriodo) {
        // Si no hay ID, no podemos continuar
        periodoNombreEl.textContent = "ERROR";
        selectConvocatoria.innerHTML = '<option value="">No se especificó un período</option>';
        return;
    }

    // 3. Cargar las convocatorias para este período
    async function loadConvocatorias() {
        try {
            const response = await fetch(`../../api_get_convocatorias_por_periodo.php?id_periodo=${idPeriodo}`);
            const convocatorias = await response.json();
            
            if (convocatorias.error) { throw new Error(convocatorias.error); }

            if (convocatorias.length > 0) {
                // Rellenamos el <select>
                selectConvocatoria.innerHTML = '';
                convocatorias.forEach(conv => {
                    const option = document.createElement('option');
                    option.value = conv.id_convocatoria;
                    option.textContent = conv.nombre;
                    selectConvocatoria.appendChild(option);
                });
                
                const periodoResponse = await fetch('../../api_get_periodos.php');
                const periodos = await periodoResponse.json();
                const periodo = periodos.find(p => p.id_periodo == idPeriodo);
                if (periodo) {
                    periodoNombreEl.textContent = periodo.nombre_periodo;
                }

            } else {
                selectConvocatoria.innerHTML = '<option value="">No hay convocatorias para este período</M option>';
            }
        } catch (error) {
            selectConvocatoria.innerHTML = `<option value="">${error.message}</option>`;
        }
    }

    // 4. Manejar el envío del formulario
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        formError.textContent = '';

        const formData = new FormData(form);

        try {
            const response = await fetch('../../api_create_expediente.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // ¡Éxito! Regresamos a la página de expedientes
                window.location.href = 'expedientes.html';
            } else {
                // Mostramos el error (ej. "ya existe")
                formError.textContent = result.message;
            }
        } catch (error) {
            formError.textContent = 'Error de conexión. Inténtelo de nuevo.';
        }
    });

    // 5. Iniciar la carga
    loadConvocatorias();
});