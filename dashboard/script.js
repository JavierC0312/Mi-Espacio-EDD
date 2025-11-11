// Variable global para guardar los datos del perfil
let userProfileData = {};

// Se ejecuta cuando el HTML se ha cargado
document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Obtener datos del perfil al cargar la página
    fetchProfileData();

    // 2. Configurar los escuchadores de botones
    document.getElementById('btn-edit').addEventListener('click', toggleEditMode);
    document.getElementById('btn-cancel').addEventListener('click', toggleViewMode);
    document.getElementById('edit-mode').addEventListener('submit', saveProfileChanges);
});

// --- OBTENER DATOS ---
async function fetchProfileData() {
    try {
        const response = await fetch('../api_get_profile.php');
        if (!response.ok) throw new Error('Error en la respuesta del servidor.');

        const data = await response.json();

        if (data.error) {
            window.location.href = '../login/login.html'; // Redirigir si hay error
        } else {
            userProfileData = data; // Guardamos los datos globalmente
            populateProfile(data);  // Llenamos los campos de VISTA
        }
    } catch (error) {
        console.error('Error al obtener los datos del perfil:', error);
    }
}

// -- RELLENAR DATOS DE VISTA ---
function populateProfile(data) {
    const nombreCompleto = `${data.nombre || ''} ${data.ap_paterno || ''} ${data.ap_materno || ''}`;
    document.getElementById('profile-nombre').textContent = nombreCompleto.trim();
    document.getElementById('profile-correo').textContent = data.correo || 'No disponible';
    document.getElementById('profile-fecha').textContent = data.fecha_ingreso || 'No disponible';
    document.getElementById('profile-matricula').textContent = data.matricula || 'No disponible';
    document.getElementById('profile-curp').textContent = data.curp || 'No disponible';
    document.getElementById('profile-depto').textContent = data.nombre_departamento || 'No asignado';
}

// --- CAMBIAR A MODO EDICIÓN ---
function toggleEditMode() {
    // Rellenar el formulario de edición con los datos actuales
    document.getElementById('edit-nombre').value = userProfileData.nombre || '';
    document.getElementById('edit-ap-paterno').value = userProfileData.ap_paterno || '';
    document.getElementById('edit-ap-materno').value = userProfileData.ap_materno || '';
    document.getElementById('edit-correo').value = userProfileData.correo || '';
    document.getElementById('edit-curp').value = userProfileData.curp || '';
    
    // Rellenar campos estáticos (no editables)
    document.getElementById('static-matricula').textContent = userProfileData.matricula || 'No disponible';
    document.getElementById('static-depto').textContent = userProfileData.nombre_departamento || 'No asignado';
    
    // Ocultar vista y mostrar formulario
    document.getElementById('view-mode').classList.add('hidden');
    document.getElementById('edit-mode').classList.remove('hidden');
    document.getElementById('error-message').textContent = ''; // Limpiar errores
}

// --- CAMBIAR A MODO VISTA  ---
function toggleViewMode() {
    // Ocultar formulario y mostrar vista
    document.getElementById('edit-mode').classList.add('hidden');
    document.getElementById('view-mode').classList.remove('hidden');
}

// --- GUARDAR CAMBIOS ---
async function saveProfileChanges(event) {
    event.preventDefault(); // Evitar que el formulario se envíe de forma normal
    
    const form = event.target;
    const formData = new FormData(form);
    const errorMessage = document.getElementById('error-message');

    try {
        const response = await fetch('../api_update_profile.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Si se guardó con éxito:
            // 1. Volvemos a pedir los datos frescos del servidor
            await fetchProfileData();
            // 2. Cambiamos de vuelta al modo de vista
            toggleViewMode();
        } else {
            // Si el PHP devolvió un error
            errorMessage.textContent = result.message || 'Error al guardar.';
        }
    } catch (error) {
        console.error('Error al guardar los cambios:', error);
        errorMessage.textContent = 'Error de conexión. Inténtalo de nuevo.';
    }
}