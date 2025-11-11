// Se ejecuta cuando el HTML se ha cargado
document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Obtenemos referencias a los elementos del DOM
    const periodoSelect = document.getElementById('periodo-select');
    const previewsContainer = document.getElementById('document-previews');
    const detailsContainer = document.getElementById('expediente-details');

    // 2. Función para cargar los períodos en el filtro
    async function loadPeriodos() {
        try {
            // Subimos 2 niveles (de 'dashboard/Expedientes y solicitudes/' a la raíz)
            const response = await fetch('../../api_get_periodos.php');
            const periodos = await response.json();

            if (periodos.error) {
                periodoSelect.innerHTML = `<option value="">${periodos.error}</option>`;
                return;
            }

            if (periodos.length > 0) {
                periodoSelect.innerHTML = ''; // Limpiamos el "cargando"
                periodos.forEach(periodo => {
                    const option = document.createElement('option');
                    option.value = periodo.id_periodo;
                    option.textContent = periodo.nombre_periodo;
                    periodoSelect.appendChild(option);
                });
                
                // 3. Al cargar los períodos, cargamos el expediente del primer período
                loadExpediente(periodoSelect.value);
            } else {
                periodoSelect.innerHTML = '<option value="">No hay períodos</option>';
            }

        } catch (error) {
            console.error('Error al cargar períodos:', error);
            periodoSelect.innerHTML = '<option value="">Error al cargar</option>';
        }
    }

    // 4. Función para cargar los detalles del expediente y sus documentos
    async function loadExpediente(idPeriodo) {
        if (!idPeriodo) {
            detailsContainer.innerHTML = '<p>Por favor, seleccione un período.</p>';
            previewsContainer.innerHTML = '';
            return;
        }

        // Mostramos "Cargando..."
        detailsContainer.innerHTML = '<p>Cargando datos del expediente...</p>';
        previewsContainer.innerHTML = '';

        try {
            // Subimos 2 niveles de nuevo
            const response = await fetch(`../../api_get_expediente_detalle.php?id_periodo=${idPeriodo}`);
            const data = await response.json();

            if (data.error) {
                // Si no se encontró un expediente, lo indicamos
                detailsContainer.innerHTML = `<p>${data.error}</p>`;
                previewsContainer.innerHTML = '';
                return;
            }

            // 5. Rellenamos el panel derecho (Detalles)
            detailsContainer.innerHTML = `
                <h2>${data.nombre_expediente}</h2>
                <p><strong>Convocatoria:</strong> ${data.convocatoria}</p>
                <p><strong>Estatus:</strong> ${data.estatus}</p>
                <p><strong>Documentos:</strong> ${data.documentos_count}</p>
                <div class="links">
                    <a href="#">Descargar</a>
                    <a href="#">Visualizar</a>
                </div>
            `;

            // 6. Rellenamos el panel izquierdo (Vistas Previa)
            if (data.documentos_list && data.documentos_list.length > 0) {
                data.documentos_list.forEach(doc => {
                    previewsContainer.innerHTML += `
                        <div class="preview-item">
                            <div class="preview-placeholder">
                                Vista previa (${doc.tipo_archivo})
                            </div>
                            <p>${doc.nombre_documento_manual}</p>
                        </div>
                    `;
                });
            } else {
                previewsContainer.innerHTML = '<p>Este expediente no tiene documentos aún.</p>';
            }

        } catch (error) {
            console.error('Error al cargar expediente:', error);
            detailsContainer.innerHTML = '<p>Error al cargar el expediente.</p>';
        }
    }

    // 7. Añadimos el "escuchador" al <select>
    periodoSelect.addEventListener('change', () => {
        loadExpediente(periodoSelect.value);
    });

    // 8. Iniciamos todo cargando los períodos
    loadPeriodos();
});