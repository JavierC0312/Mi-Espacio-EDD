let userProfileData = {};

// Variables globales para la firma (para acceder desde distintas funciones)
let canvas, ctx, isDrawing = false;

document.addEventListener("DOMContentLoaded", function() {
    // 1. Cargar datos del perfil
    fetchProfileData();
    
    // 2. Listener para cancelar edición de perfil
    const btnCancel = document.getElementById('btn-cancel');
    if(btnCancel) btnCancel.addEventListener('click', toggleViewMode);

    // ======================================================
    // 3. INICIALIZACIÓN DE LÓGICA DE FIRMA
    // ======================================================
    
    // Referencias al DOM del Modal de Firma
    canvas = document.getElementById('signature-pad');
    const btnLimpiarFirma = document.getElementById('btn-limpiar-firma');
    const btnCancelarFirma = document.getElementById('btn-cancelar-firma'); // Cancelar del dibujo
    const btnGuardarFirma = document.getElementById('btn-guardar-firma');
    
    // Referencias a los botones de la vista "Firma Existente"
    const btnCambiarFirma = document.getElementById('btn-cambiar-firma');
    const btnCancelarDibujo = document.getElementById('btn-cancelar-dibujo');

    // Inicializar Canvas
    if (canvas) {
        ctx = canvas.getContext('2d');
        // Eventos de Ratón
        canvas.addEventListener('mousedown', startPosition);
        canvas.addEventListener('mouseup', endPosition);
        canvas.addEventListener('mousemove', draw);
        // Eventos Táctiles
        canvas.addEventListener('touchstart', (e) => { e.preventDefault(); startPosition(e); });
        canvas.addEventListener('touchend', (e) => { e.preventDefault(); endPosition(); });
        canvas.addEventListener('touchmove', (e) => { e.preventDefault(); draw(e); });
    }

    // Listeners de botones
    if (btnLimpiarFirma) btnLimpiarFirma.addEventListener('click', limpiarCanvas);
    
    // Botón "Cerrar" en la vista de firma existente
    // (Reutilizamos la función cerrarModalFirma si existe el botón en el HTML)
    const btnCerrarModal = document.querySelector('#vista-firma-existente button[onclick="cerrarModalFirma()"]');
    if (btnCerrarModal) {
        btnCerrarModal.onclick = cerrarModalFirma; // Aseguramos que funcione sin onclick en HTML
    }

    // Botón "Cancelar" en la vista de dibujo
    if (btnCancelarFirma) btnCancelarFirma.addEventListener('click', cerrarModalFirma); // Caso viejo
    if (btnCancelarDibujo) {
        btnCancelarDibujo.addEventListener('click', () => {
            // Si ya tiene firma, regresa a la vista previa, si no, cierra
            if (userProfileData.ruta_firma_qr) {
                mostrarVistaExistente();
            } else {
                cerrarModalFirma();
            }
        });
    }

    // Botón "Dibujar nueva firma"
    if (btnCambiarFirma) {
        btnCambiarFirma.addEventListener('click', mostrarVistaDibujo);
    }

    // Botón Guardar
    if (btnGuardarFirma) btnGuardarFirma.addEventListener('click', guardarFirmaYGenerarQR);
});

// --- FUNCIONES DE PERFIL ---

async function fetchProfileData() {
    try {
        const response = await fetch('../api_get_profile.php');
        const data = await response.json();

        if (data.error) {
            window.location.href = '../login/login.html';
        } else {
            userProfileData = data;
            populateProfile(data);
            renderButtons(data.tipo_personal);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function populateProfile(data) {
    const nombreCompleto = `${data.nombre} ${data.ap_paterno} ${data.ap_materno || ''}`;
    document.getElementById('profile-nombre').textContent = nombreCompleto;
    document.getElementById('profile-rol').textContent = data.tipo_personal;
    document.getElementById('profile-correo').textContent = data.correo;
    document.getElementById('profile-depto').textContent = data.nombre_departamento;
    document.getElementById('profile-matricula').textContent = data.matricula;
    
    if(document.getElementById('profile-fecha')) {
        document.getElementById('profile-fecha').textContent = data.fecha_ingreso;
    }

    if(document.getElementById('profile-curp')) 
        document.getElementById('profile-curp').textContent = data.curp;
}

function renderButtons(rol) {
    const container = document.getElementById('dynamic-buttons');
    container.innerHTML = ''; 

    // Botón: Modificar Perfil
    const btnEdit = document.createElement('button');
    btnEdit.className = 'btn';
    btnEdit.textContent = 'Modificar Perfil';
    btnEdit.onclick = toggleEditMode;
    container.appendChild(btnEdit);

    // Botón: Mi Firma
    const btnFirma = document.createElement('button');
    btnFirma.className = 'btn';
    btnFirma.textContent = 'Mi Firma';
    btnFirma.id = 'btn-abrir-firma'; 
    btnFirma.onclick = abrirModalFirma; 
    container.appendChild(btnFirma);

    // Lógica por Rol
    if (rol === 'DOCENTE') {
        const btnExp = document.createElement('a');
        btnExp.href = 'Expedientes y solicitudes/expedientes.html';
        btnExp.className = 'btn';
        btnExp.textContent = 'Mis Expedientes';
        container.appendChild(btnExp);
    } 
    else if (rol === 'DIRECTOR' || rol === 'JEFE_AREA' || rol === 'SUBDIRECTOR') {
        const btnBandeja = document.createElement('a');
        btnBandeja.href = 'Bandeja de solicitudes/bandeja.html'; 
        btnBandeja.className = 'btn';
        btnBandeja.textContent = 'Bandeja de Solicitudes';
        container.appendChild(btnBandeja);
    }
}

function toggleEditMode() {
    document.getElementById('view-mode').classList.add('hidden');
    document.getElementById('edit-mode').classList.remove('hidden');
}

function toggleViewMode() {
    document.getElementById('edit-mode').classList.add('hidden');
    document.getElementById('view-mode').classList.remove('hidden');
}

// --- FUNCIONES DE FIRMA (CANVAS Y VISTAS) ---

function abrirModalFirma() {
    document.getElementById('modal-firma').classList.remove('hidden');
    document.getElementById('modal-backdrop-firma').classList.remove('hidden');
    
    // Decidir qué vista mostrar según si ya tiene firma
    if (userProfileData.ruta_firma_qr) {
        mostrarVistaExistente();
    } else {
        mostrarVistaDibujo();
    }
}

function mostrarVistaExistente() {
    document.getElementById('vista-firma-existente').classList.remove('hidden');
    document.getElementById('vista-nueva-firma').classList.add('hidden');
    
    // Cargar la imagen y configurar descarga
    // Nota: La ruta viene como 'archivos/qr_firmas/...', subimos un nivel
    const rutaImg = "../" + userProfileData.ruta_firma_qr;
    const imgElement = document.getElementById('img-firma-actual');
    const btnDescargar = document.getElementById('btn-descargar-imagen');
    
    // Agregamos un timestamp para evitar caché si acabamos de actualizar
    const timestamp = new Date().getTime();
    imgElement.src = rutaImg + "?t=" + timestamp;
    btnDescargar.href = rutaImg;
}

function mostrarVistaDibujo() {
    document.getElementById('vista-firma-existente').classList.add('hidden');
    document.getElementById('vista-nueva-firma').classList.remove('hidden');
    resizeCanvas();
    limpiarCanvas();
}

function cerrarModalFirma() {
    document.getElementById('modal-firma').classList.add('hidden');
    document.getElementById('modal-backdrop-firma').classList.add('hidden');
    limpiarCanvas();
}

function startPosition(e) {
    isDrawing = true;
    draw(e);
}

function endPosition() {
    isDrawing = false;
    ctx.beginPath(); 
}

function draw(e) {
    if (!isDrawing) return;

    const rect = canvas.getBoundingClientRect();
    let x, y;

    if (e.type.includes('touch')) {
        x = e.touches[0].clientX - rect.left;
        y = e.touches[0].clientY - rect.top;
    } else {
        x = e.clientX - rect.left;
        y = e.clientY - rect.top;
    }

    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#000';

    ctx.lineTo(x, y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(x, y);
}

function limpiarCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function resizeCanvas() {
    // Ajuste simple si fuera necesario
}

async function guardarFirmaYGenerarQR() {
    const dataURL = canvas.toDataURL('image/png');

    if (dataURL.length < 1000) {
        alert("Por favor dibuje su firma antes de guardar.");
        return;
    }

    const formData = new FormData();
    formData.append('firma_base64', dataURL);

    try {
        const response = await fetch('../api_guardar_firma.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            alert('Firma guardada y QR generado exitosamente.');
            
            // ACTUALIZAR DATOS EN MEMORIA
            userProfileData.ruta_firma_qr = result.qr_url;
            
            // Volver a la vista de "Firma Existente"
            mostrarVistaExistente();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión al guardar la firma.');
    }
}

// --- LÓGICA DE FORMULARIO DE EDICIÓN ---
const editForm = document.getElementById('edit-mode');
if (editForm) {
    editForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        const formData = new FormData(editForm);

        try {
            const response = await fetch('../api_update_profile.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                await fetchProfileData();
                toggleViewMode();
            } else {
                alert(result.message || 'Error al guardar.');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
}