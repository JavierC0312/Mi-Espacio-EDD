// Variable global para el estado actual (Pendiente o Reportado)
let currentEstado = 'Pendiente';

document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Referencias al DOM
    const searchBox = document.getElementById('search-box');
    const btnFiltros = document.getElementById('btn-filtros');
    const filterDropdown = document.getElementById('filter-dropdown');
    const tablaBody = document.getElementById('tabla-body');
    const detailsBox = document.getElementById('details-box');
    const btnRevisar = document.getElementById('btn-revisar');

    // 2. Variables de estado
    let currentSolicitudes = []; 
    let currentSort = { by: 'fecha', order: 'ASC' };
    let searchTimeout = null;

    // 3. Función principal para cargar datos
    async function loadSolicitudes(searchTerm = '', sort = 'fecha', order = 'ASC') {
        currentSort = { by: sort, order: order };
        
        // Usamos la ruta relativa correcta
        const url = new URL('../../api_get_solicitudes.php', window.location.href);
        
        // --- CLAVE: ENVIAMOS EL ESTADO ACTUAL ---
        url.searchParams.append('estado', currentEstado);
        
        if (searchTerm) url.searchParams.append('search', searchTerm);
        url.searchParams.append('sort', sort);
        url.searchParams.append('order', order);

        try {
            // Limpiamos la tabla y mostramos "Cargando..."
            tablaBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Cargando...</td></tr>';
            clearDetails(); // Limpiamos detalles al cambiar de pestaña

            const response = await fetch(url);
            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }
            
            currentSolicitudes = data;
            populateTable(data);

        } catch (error) {
            console.error('Error:', error);
            tablaBody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: red;">No se encontraron solicitudes o hubo un error.</td></tr>`;
        }
    }

    // 4. Función para pintar la tabla
    function populateTable(solicitudes) {
        tablaBody.innerHTML = ''; 
        
        if (solicitudes.length === 0) {
            // Mensaje personalizado según la pestaña
            const mensaje = currentEstado === 'Pendiente' 
                ? 'No hay solicitudes pendientes.' 
                : 'No hay reportes de corrección.';
            tablaBody.innerHTML = `<tr><td colspan="4" style="text-align:center;">${mensaje}</td></tr>`;
            return;
        }

        solicitudes.forEach((sol, index) => {
            const row = document.createElement('tr');
            row.dataset.index = index;
            
            // Si estamos en reportes, pintamos la fila un poco roja para destacar
            if (currentEstado === 'Reportado') {
                row.style.backgroundColor = '#fff5f5';
            }

            row.innerHTML = `
                <td>${sol.folio}</td>
                <td>${sol.nombre_docente}</td>
                <td>${sol.nombre_documento}</td>
                <td>${sol.fecha}</td>
            `;
            tablaBody.appendChild(row);
        });
    }

    // 5. Mostrar detalles al hacer clic
    function handleRowClick(event) {
        const row = event.target.closest('tr');
        if (!row || typeof row.dataset.index === 'undefined') return;

        // Estilos de selección
        tablaBody.querySelectorAll('tr').forEach(tr => {
            tr.classList.remove('active');
            // Restaurar color de fondo si es reporte
            if(currentEstado === 'Reportado') tr.style.backgroundColor = '#fff5f5';
            else tr.style.backgroundColor = ''; 
        });
        
        row.classList.add('active');
        row.style.backgroundColor = '#E8DAEF'; // Color de selección (morado claro)

        const data = currentSolicitudes[row.dataset.index];
        
        // Llenar detalles
        detailsBox.innerHTML = `
            <p><strong>Documento seleccionado:</strong></p>
            <p>${data.nombre_documento}</p>
            <p><strong>Docente:</strong></p>
            <p>${data.nombre_docente}</p>
            <p><strong>Folio:</strong> ${data.folio}</p>
        `;
        
        btnRevisar.classList.remove('disabled');
        btnRevisar.href = `revisar.html?folio=${data.folio}`;
        
        // Si es reporte, cambiamos el texto del botón
        if (currentEstado === 'Reportado') {
            btnRevisar.textContent = "Atender Reporte";
            btnRevisar.style.backgroundColor = "#d9534f"; // Rojo
        } else {
            btnRevisar.textContent = "Revisar";
            btnRevisar.style.backgroundColor = ""; // Reset
        }
    }

    function clearDetails() {
        detailsBox.innerHTML = '<p>No hay solicitud seleccionada.</p>';
        btnRevisar.classList.add('disabled');
        btnRevisar.href = '#';
        btnRevisar.textContent = "Revisar";
        btnRevisar.style.backgroundColor = "";
    }

    // 6. Event Listeners
    searchBox.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadSolicitudes(searchBox.value, currentSort.by, currentSort.order);
        }, 300);
    });

    tablaBody.addEventListener('click', handleRowClick);
    
    btnFiltros.addEventListener('click', (e) => {
        e.stopPropagation();
        filterDropdown.classList.toggle('hidden');
    });
    
    filterDropdown.addEventListener('click', (e) => {
        e.preventDefault();
        const link = e.target.closest('a');
        if (link && link.dataset.sort) {
            loadSolicitudes(searchBox.value, link.dataset.sort, link.dataset.order);
            filterDropdown.classList.add('hidden');
        }
    });

    document.addEventListener('click', () => {
        filterDropdown.classList.add('hidden');
    });

    // --- 7. EXPORTAR FUNCIÓN PARA EL HTML ---
    // Esta función permite que el onclick="..." del HTML funcione
    window.cambiarPestana = function(estado, elemento) {
        currentEstado = estado;
        
        // Actualizar clases visuales de las pestañas
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        elemento.classList.add('active');
        
        // Recargar la tabla con el nuevo estado
        loadSolicitudes(searchBox.value, currentSort.by, currentSort.order);
    };

    // 8. Carga inicial
    loadSolicitudes();
});