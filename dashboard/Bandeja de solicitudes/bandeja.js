document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Referencias al DOM
    const searchBox = document.getElementById('search-box');
    const btnFiltros = document.getElementById('btn-filtros');
    const filterDropdown = document.getElementById('filter-dropdown');
    const tablaBody = document.getElementById('tabla-body');
    const detailsBox = document.getElementById('details-box');
    const btnRevisar = document.getElementById('btn-revisar');

    // 2. Estado de la aplicación
    let currentSolicitudes = []; // Almacena los datos cargados
    let currentSort = { by: 'fecha', order: 'ASC' };
    let searchTimeout = null;

    // 3. Función principal para cargar datos
    async function loadSolicitudes(searchTerm = '', sort = 'fecha', order = 'ASC') {
        // Guardamos el estado del filtro
        currentSort = { by: sort, order: order };
        
        // Construimos la URL con los parámetros
        const url = new URL('../api_get_solicitudes.php', window.location.origin);
        if (searchTerm) {
            url.searchParams.append('search', searchTerm);
        }
        url.searchParams.append('sort', sort);
        url.searchParams.append('order', order);

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }
            
            currentSolicitudes = data; // Guardamos los datos
            populateTable(data);

        } catch (error) {
            console.error('Error al cargar solicitudes:', error);
            tablaBody.innerHTML = `<tr><td colspan="4">${error.message}</td></tr>`;
        }
    }

    // 4. Función para pintar la tabla
    function populateTable(solicitudes) {
        tablaBody.innerHTML = ''; // Limpiamos la tabla
        
        if (solicitudes.length === 0) {
            tablaBody.innerHTML = '<tr><td colspan="4">No se encontraron solicitudes pendientes.</td></tr>';
            return;
        }

        solicitudes.forEach((sol, index) => {
            const row = document.createElement('tr');
            row.dataset.index = index; // Usamos el índice para encontrar los datos
            row.innerHTML = `
                <td>${sol.folio}</td>
                <td>${sol.nombre_docente}</td>
                <td>${sol.nombre_documento}</td>
                <td>${sol.fecha}</td>
            `;
            tablaBody.appendChild(row);
        });

        // Limpiamos los detalles
        clearDetails();
    }

    // 5. Función para mostrar detalles al hacer clic
    function handleRowClick(event) {
        const row = event.target.closest('tr');
        if (!row || !row.dataset.index) return; // No es una fila válida

        // Quitar 'active' de todas las filas
        tablaBody.querySelectorAll('tr').forEach(tr => tr.classList.remove('active'));
        // Poner 'active' en la fila seleccionada
        row.classList.add('active');

        // Obtener los datos de esta fila
        const data = currentSolicitudes[row.dataset.index];
        
        // Llenar el panel de detalles
        detailsBox.innerHTML = `
            <p><strong>Documento seleccionado:</strong></p>
            <p>${data.nombre_documento}</p>
            <p><strong>Docente:</strong></p>
            <p>${data.nombre_docente}</p>
        `;
        
        // Activar el botón de Revisar y ponerle el enlace
        btnRevisar.classList.remove('disabled');
        btnRevisar.href = `revisar.html?folio=${data.folio}`;
    }

    // 6. Función para limpiar el panel de detalles
    function clearDetails() {
        detailsBox.innerHTML = '<p>No hay solicitud seleccionada.</p>';
        btnRevisar.classList.add('disabled');
        btnRevisar.href = '#';
    }

    // 7. Event Listeners
    
    // Búsqueda (con debounce para no spampear la API)
    searchBox.addEventListener('input', () => {
        clearTimeout(searchTimeout); // Reseteamos el timer
        searchTimeout = setTimeout(() => {
            loadSolicitudes(searchBox.value, currentSort.by, currentSort.order);
        }, 300); // Espera 300ms después de que el usuario deja de teclear
    });

    // Clic en la tabla
    tablaBody.addEventListener('click', handleRowClick);
    
    // Botón de Filtros
    btnFiltros.addEventListener('click', (e) => {
        e.stopPropagation(); // Evita que el clic se propague al 'document'
        filterDropdown.classList.toggle('hidden');
    });
    
    // Clic en una opción de filtro
    filterDropdown.addEventListener('click', (e) => {
        e.preventDefault();
        const link = e.target.closest('a');
        if (link && link.dataset.sort) {
            loadSolicitudes(searchBox.value, link.dataset.sort, link.dataset.order);
            filterDropdown.classList.add('hidden');
        }
    });

    // Cerrar dropdown si se hace clic en cualquier otro lado
    document.addEventListener('click', () => {
        filterDropdown.classList.add('hidden');
    });

    // 8. Carga inicial
    loadSolicitudes();
});