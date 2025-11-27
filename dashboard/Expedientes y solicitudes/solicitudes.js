document.addEventListener("DOMContentLoaded", function() {
    
    // Referencias
    const tablaBody = document.getElementById('tabla-solicitudes');
    const filtroEstado = document.getElementById('filtro-estado');
    const btnNueva = document.getElementById('btn-nueva-solicitud');
    
    // Paneles
    const panelDetalles = document.getElementById('panel-detalles');
    const panelNueva = document.getElementById('panel-nueva');
    const infoContent = document.getElementById('info-content');
    const placeholderText = document.querySelector('.placeholder-text');
    
    // Elementos de detalle
    const detDoc = document.getElementById('det-doc');
    const detFolio = document.getElementById('det-folio');
    const detEstado = document.getElementById('det-estado');
    const cajaMensaje = document.getElementById('caja-mensaje-admin');
    const txtMensaje = document.getElementById('txt-mensaje-admin');
    const btnVisualizar = document.getElementById('btn-visualizar');

    // Formulario
    const formSolicitud = document.getElementById('form-solicitud');
    const selectPlantilla = document.getElementById('select-plantilla');
    const btnCancelarNueva = document.getElementById('btn-cancelar-nueva');

    let solicitudesData = [];

    // 1. CARGAR SOLICITUDES
    async function loadSolicitudes() {
        const estado = filtroEstado.value;
        try {
            const res = await fetch(`../../api_get_mis_solicitudes.php?estado=${estado}`);
            const data = await res.json();
            
            solicitudesData = data;
            renderTable(data);
        } catch (error) {
            console.error(error);
        }
    }

    function renderTable(data) {
        tablaBody.innerHTML = '';
        if(data.length === 0) {
            tablaBody.innerHTML = '<tr><td colspan="4" style="text-align:center">No hay solicitudes.</td></tr>';
            return;
        }

        data.forEach((sol, index) => {
            const tr = document.createElement('tr');
            tr.dataset.index = index;
            
            // Asignar clase de color según estado
            let badgeClass = 'bg-pendiente';
            if(sol.estado === 'Completado') badgeClass = 'bg-completado';
            if(sol.estado === 'Rechazado') badgeClass = 'bg-rechazado';

            tr.innerHTML = `
                <td>${sol.folio}</td>
                <td>${sol.nombre_documento}</td>
                <td><span class="badge ${badgeClass}">${sol.estado}</span></td>
                <td>${sol.fecha}</td>
            `;
            
            tr.addEventListener('click', () => mostrarDetalles(index));
            tablaBody.appendChild(tr);
        });
    }

    // 2. MOSTRAR DETALLES
    function mostrarDetalles(index) {
        // Cambiar vista de paneles
        panelNueva.classList.add('hidden');
        panelDetalles.classList.remove('hidden');
        placeholderText.classList.add('hidden');
        infoContent.classList.remove('hidden');

        // Resaltar fila
        document.querySelectorAll('tr').forEach(tr => tr.classList.remove('active'));
        tablaBody.children[index].classList.add('active');

        const sol = solicitudesData[index];
        
        detDoc.textContent = sol.nombre_documento;
        detFolio.textContent = sol.folio;
        detEstado.textContent = sol.estado;

        // Mostrar mensaje si existe
        if (sol.mensaje_admin) {
            cajaMensaje.classList.remove('hidden');
            txtMensaje.textContent = sol.mensaje_admin;
        } else {
            cajaMensaje.classList.add('hidden');
        }

        // Botón visualizar (solo si ya hay algo que ver o si es editable)
        // Reutilizamos visualizar.html?id=... pero OJO: visualizar.html espera ID de expediente.
        // Para documentos sueltos (solicitudes), usaremos "revisar.html" del docente en modo solo lectura
        // O mejor aún: reutilizamos revisar.html?folio=... 
        btnVisualizar.classList.remove('hidden');
        btnVisualizar.href = `visualizar.html?id=0&folio=${sol.folio}`; // Truco: pasamos id=0 y folio
        // Nota: Tendremos que ajustar visualizar.js levemente para soportar folio directo si queremos reusarlo,
        // o simplemente usar el revisar.html del admin pero adaptado.
        // POR AHORA: Apuntamos a visualizar.html y le pasamos el folio para que cargue el preview.
        
        // CORRECCIÓN: La página visualizar.html actual está diseñada para Expedientes (carrusel).
        // Para ver una solicitud individual, usaremos una versión simplificada o el mismo revisar.html
        // Vamos a usar revisar.html (el que usa el admin) pero ocultando los botones de firma si es docente.
        btnVisualizar.href = `../Bandeja de solicitudes/revisar.html?folio=${sol.folio}&role=docente`;
    }

    // 3. NUEVA SOLICITUD
    btnNueva.addEventListener('click', async () => {
        panelDetalles.classList.add('hidden');
        panelNueva.classList.remove('hidden');
        
        // Cargar plantillas si está vacío
        if(selectPlantilla.options.length <= 1) {
            const res = await fetch('../../api_get_plantillas.php');
            const plantillas = await res.json();
            selectPlantilla.innerHTML = '<option value="">Seleccione...</option>';
            plantillas.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id_plantilla;
                opt.textContent = p.nombre_plantilla;
                selectPlantilla.appendChild(opt);
            });
        }
    });

    btnCancelarNueva.addEventListener('click', () => {
        panelNueva.classList.add('hidden');
        panelDetalles.classList.remove('hidden');
    });

    formSolicitud.addEventListener('submit', async (e) => {
        e.preventDefault();
        const idPlantilla = selectPlantilla.value;
        
        const formData = new FormData();
        formData.append('id_plantilla', idPlantilla);

        const res = await fetch('../../api_crear_solicitud_documento.php', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if(result.success) {
            alert('Solicitud enviada correctamente.');
            panelNueva.classList.add('hidden');
            panelDetalles.classList.remove('hidden');
            loadSolicitudes(); // Recargar tabla
        } else {
            alert('Error: ' + result.message);
        }
    });

    // Botón Solicitar Todo
    const btnSolicitarTodo = document.getElementById('btn-solicitar-todo');
    if(btnSolicitarTodo) {
        btnSolicitarTodo.addEventListener('click', async () => {
            if(!confirm("¿Deseas solicitar TODOS los documentos disponibles automáticamente?")) return;
            
            try {
                const res = await fetch('../../api_solicitar_todo.php');
                const data = await res.json();
                alert(data.message);
                loadSolicitudes(); // Recargar tabla
            } catch (e) {
                alert("Error al solicitar todo.");
            }
        });
    }

    // Listeners globales
    filtroEstado.addEventListener('change', loadSolicitudes);
    // Iniciar
    loadSolicitudes();   
});