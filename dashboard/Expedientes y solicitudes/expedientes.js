document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Referencias al DOM 
    const periodoSelect = document.getElementById('periodo-select');
    const expedienteList = document.getElementById('expediente-list');
    const detailsContainer = document.getElementById('expediente-details');
    const btnCrear = document.getElementById('btn-crear');
    const btnEliminar = document.getElementById('btn-eliminar');
    
    // 2. Referencias al DOM 
    const modalBackdropDelete = document.getElementById('modal-backdrop-delete');
    const modalDelete = document.getElementById('modal-delete');
    const btnCancelDelete = document.getElementById('btn-cancel-delete');
    const btnConfirmDelete = document.getElementById('btn-confirm-delete');

    // 3. Variable de estado
    let currentActiveExpedienteId = null;

    // --- FUNCIÓN 1: Cargar Períodos ---
    async function loadPeriodos() {
        try {
            const response = await fetch('../../api_get_periodos.php');
            const periodos = await response.json();
            if (periodos.error) { throw new Error(periodos.error); }

            if (periodos.length > 0) {
                periodoSelect.innerHTML = '';
                periodos.forEach(periodo => {
                    const option = document.createElement('option');
                    option.value = periodo.id_periodo;
                    option.textContent = periodo.nombre_periodo;
                    periodoSelect.appendChild(option);
                });
                loadExpedientes(periodoSelect.value);
            } else {
                periodoSelect.innerHTML = '<option value="">No hay períodos</option>';
            }
        } catch (error) {
            console.error('Error al cargar períodos:', error);
            periodoSelect.innerHTML = '<option value="">Error al cargar</option>';
        }
    }

    // --- FUNCIÓN 2: Cargar LISTA DE EXPEDIENTES ---
    async function loadExpedientes(idPeriodo) {
        if (!idPeriodo) {
            expedienteList.innerHTML = '<p>Por favor, seleccione un período.</p>';
            return;
        }

        expedienteList.innerHTML = '<p>Cargando expedientes...</p>';
        detailsContainer.innerHTML = '<p>No hay expediente seleccionado.</p>';
        currentActiveExpedienteId = null; 

        try {
            const response = await fetch(`../../api_get_expedientes_por_periodo.php?id_periodo=${idPeriodo}`);
            const data = await response.json();
            if (data.error) { throw new Error(data.error); }

            expedienteList.innerHTML = '';
            if (data.length > 0) {
                data.forEach(expediente => {
                    const item = document.createElement('div');
                    item.className = 'expediente-item';
                    item.dataset.id = expediente.id_expediente;
                    item.textContent = expediente.nombre_convocatoria;
                    expedienteList.appendChild(item);
                });
                loadEstadisticas(data[0].id_expediente);
                expedienteList.children[0].classList.add('active');
            } else {
                expedienteList.innerHTML = '<p>No hay expedientes para este período.</p>';
            }
        } catch (error) {
            console.error('Error al cargar expedientes:', error);
            expedienteList.innerHTML = `<p>${error.message}</p>`;
        }
    }

    // --- FUNCIÓN 3: Cargar ESTADÍSTICAS ---
    async function loadEstadisticas(idExpediente) {
        currentActiveExpedienteId = idExpediente;
        detailsContainer.innerHTML = '<p>Cargando estadísticas...</p>';
        try {
            const response = await fetch(`../../api_get_expediente_estadisticas.php?id_expediente=${idExpediente}`);
            const data = await response.json();
            if (data.error) { throw new Error(data.error); }

            const descargarUrl = `../../api_download_expediente.php?id_expediente=${idExpediente}`;
            const visualizarUrl = `visualizar.html?id=${idExpediente}`;

            detailsContainer.innerHTML = `
                <h2>${data.nombre_expediente}</h2>
                <p><strong>Convocatoria:</strong> ${data.convocatoria}</p>
                <p><strong>Estatus:</strong> ${data.estatus}</p>
                <p><strong>Documentos:</strong> ${data.documentos_count}</p>
                <div class="links">
                    <a href="${descargarUrl}">Descargar</a>
                    <a href="${visualizarUrl}">Visualizar</a>
                </div>
            `;
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
            detailsContainer.innerHTML = `<p>${error.message}</p>`;
        }
    }

    // --- FUNCIÓN 4: Abrir/Cerrar Modal de Borrado ---
    function openDeleteModal() {
        if (!currentActiveExpedienteId) {
            alert('Por favor, seleccione un expediente para eliminar.');
            return;
        }
        modalBackdropDelete.classList.remove('hidden');
        modalDelete.classList.remove('hidden');
    }

    function closeDeleteModal() {
        modalBackdropDelete.classList.add('hidden');
        modalDelete.classList.add('hidden');
    }

    // --- FUNCIÓN 5: Ejecutar el borrado (lo que hacía confirm() antes) ---
    async function handleConfirmDelete() {
        try {
            const formData = new FormData();
            formData.append('id_expediente', currentActiveExpedienteId);

            const response = await fetch('../../api_delete_expediente.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert('Expediente eliminado con éxito.');
                closeDeleteModal(); // Cerramos el modal
                loadExpedientes(periodoSelect.value); // Recargamos la lista
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error al eliminar expediente:', error);
            alert(`Error al eliminar: ${error.message}`);
        }
    }

    // --- 6. Event Listeners ---
    
    periodoSelect.addEventListener('change', () => {
        loadExpedientes(periodoSelect.value);
    });

    expedienteList.addEventListener('click', (event) => {
        const expedienteItem = event.target.closest('.expediente-item');
        if (expedienteItem) {
            document.querySelectorAll('.expediente-item').forEach(item => {
                item.classList.remove('active');
            });
            expedienteItem.classList.add('active');
            loadEstadisticas(expedienteItem.dataset.id);
        }
    });
    
    btnCrear.addEventListener('click', () => {
        const selectedPeriodoId = periodoSelect.value;
        if (selectedPeriodoId) {
            window.location.href = `crear_expediente.html?periodo=${selectedPeriodoId}`;
        } else {
            alert('Por favor, seleccione un período primero.');
        }
    });

    // --- LÓGICA DE BORRADO ---
    btnEliminar.addEventListener('click', openDeleteModal);
    btnCancelDelete.addEventListener('click', closeDeleteModal);
    modalBackdropDelete.addEventListener('click', closeDeleteModal);
    btnConfirmDelete.addEventListener('click', handleConfirmDelete);

    // --- 7. Iniciar todo ---
    loadPeriodos();
});