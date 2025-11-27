document.addEventListener("DOMContentLoaded", function() {
    
    const urlParams = new URLSearchParams(window.location.search);
    const folio = urlParams.get('folio');
    const role = urlParams.get('role'); // Leer el rol de la URL
    const iframe = document.getElementById('doc-iframe');
    
    // Referencias
    const btnEditarLink = document.getElementById('btn-editar-link');
    const modalBackdrop = document.getElementById('modal-backdrop');
    const modalEditar = document.getElementById('modal-editar');
    const btnCancelarModal = document.getElementById('btn-cancelar-modal');
    
    const fileInput = document.getElementById('file-upload');
    const fileNameDisplay = document.getElementById('file-name-display');
    const btnEnviarCorreccion = document.getElementById('btn-enviar-correccion');
    const txtMensajeAdmin = document.getElementById('mensaje-admin');
    
    // Referencias para cambiar el Modal dinámicamente
    const modalTitle = document.querySelector('#modal-editar h3'); 
    const modalDesc = document.querySelector('#modal-editar p');   
    
    const btnFirmar = document.getElementById('btn-firmar');
    const btnCancelar = document.querySelector('.btn-cancelar'); // Referencia al botón de Cancelar/Regresar

    // Variable de estado
    let hayReporteActivo = false; 

    // ======================================================
    // MODIFICACIÓN IMPORTANTE: LÓGICA DE ROLES
    // ======================================================
    // CASO 1: DOCENTE SOLO LECTURA (Viendo sus solicitudes)
    if (role === 'docente') {
        const headerUser = document.querySelector('.header-revisar span strong');
        if(headerUser) headerUser.textContent = "Docente (Vista)";
        if(btnEditarLink) btnEditarLink.parentElement.style.display = 'none';
        if(btnFirmar) btnFirmar.style.display = 'none'; // Ocultar firma
        
        if(btnCancelar) {
            btnCancelar.textContent = "Regresar a Mis Solicitudes";
            btnCancelar.href = "../Expedientes y solicitudes/solicitudes.html"; 
        }
    } 
    // CASO 2: DOCENTE O JURADO FIRMANTE (Nueva Pestaña)
    else if (role === 'firmante') {
        const headerUser = document.querySelector('.header-revisar span strong');
        if(headerUser) headerUser.textContent = "Firmante";

        // Ocultar edición (Solo firma, no edita documento ajeno)
        if(btnEditarLink) btnEditarLink.parentElement.style.display = 'none';
        
        // MOSTRAR botón de firma
        if(btnFirmar) {
            btnFirmar.style.display = 'inline-block';
            btnFirmar.textContent = "✍️ Estampar mi Firma";
        }

        // LÓGICA DE REGRESO CORRECTA
        if(btnCancelar) {
            btnCancelar.textContent = "Regresar a Mis Firmas";
            // Ruta para volver a la bandeja de firmas
            btnCancelar.href = "../Expedientes y solicitudes/firmas.html"; 
        }
    }

    // 1. Cargar vista previa
    async function loadDocumentPreview() {
        if (!folio) return;
        try {
            const infoResponse = await fetch(`../../api_get_document_info.php?folio=${folio}`);
            const info = await infoResponse.json();

            if (info.error) { console.error(info.error); return; }

            // Mostrar alerta si hay reporte activo
            if (info.estado === 'Reportado') {
                hayReporteActivo = true; 
                
                // Mostrar alerta visual del mensaje del docente
                if (info.mensaje_docente) {
                    const container = document.querySelector('.container-revisar');
                    const header = document.querySelector('.header-revisar');
                    if (!document.getElementById('alerta-reporte')) {
                        const alerta = document.createElement('div');
                        alerta.id = 'alerta-reporte';
                        alerta.style.cssText = "background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #f5c6cb; text-align: left;";
                        alerta.innerHTML = `<strong>⚠️ REPORTE DEL DOCENTE:</strong> <br> ${info.mensaje_docente}`;
                        header.parentNode.insertBefore(alerta, header.nextSibling);
                    }
                }
                if(btnFirmar) btnFirmar.textContent = "Subir Corrección y Finalizar";
            }

            let docSource = '';
            if (info.ruta_pdf) {
                docSource = `../../${info.ruta_pdf}`;
                if (info.estado === 'Completado' && btnFirmar) {
                    btnFirmar.disabled = true;
                    btnFirmar.textContent = 'Documento Completado';
                    btnFirmar.style.backgroundColor = "#ccc";
                    if(btnEditarLink) btnEditarLink.style.display = 'none';
                }
            } else {
                docSource = `../../api_ver_documento.php?folio=${folio}`;
            }
            iframe.src = docSource;

        } catch (error) { console.error('Error:', error); }
    }
    
    loadDocumentPreview();

    // 2. ABRIR MODAL (Solo si existe el enlace)
    if(btnEditarLink) {
        btnEditarLink.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Adaptamos el Modal según el contexto
            if (hayReporteActivo) {
                modalTitle.textContent = "Atender Reporte";
                modalDesc.textContent = "Descargue el editable, corrija el error reportado y explique los cambios.";
                txtMensajeAdmin.style.display = "block"; 
                txtMensajeAdmin.placeholder = "Explica al docente qué correcciones hiciste...";
                txtMensajeAdmin.value = ""; 
            } else {
                modalTitle.textContent = "Editar Documento";
                modalDesc.textContent = "Si encontró un error, descargue el editable y suba la versión correcta.";
                txtMensajeAdmin.style.display = "none"; 
                txtMensajeAdmin.value = "Corrección interna realizada por el administrador."; 
            }

            modalBackdrop.classList.remove('hidden');
            modalEditar.classList.remove('hidden');
            
            window.location.href = `../../api_descargar_editable.php?folio=${folio}`;
        });
    }

    // 3. Cerrar Modal
    function cerrarModal() {
        modalBackdrop.classList.add('hidden');
        modalEditar.classList.add('hidden');
        fileInput.value = ''; 
        fileNameDisplay.textContent = '';
        btnEnviarCorreccion.style.display = 'none'; 
    }
    if(btnCancelarModal) btnCancelarModal.addEventListener('click', cerrarModal);

    // 4. Al seleccionar archivo
    if(fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                if (file.type !== 'application/pdf') {
                    alert("Error: Solo se permiten archivos PDF.");
                    this.value = '';
                    fileNameDisplay.textContent = '';
                    btnEnviarCorreccion.style.display = 'none';
                    return;
                }
                fileNameDisplay.textContent = "Listo para subir: " + file.name;
                btnEnviarCorreccion.style.display = 'block'; 
            }
        });
    }

    // 5. CLIC EN "ENVIAR CORRECCIÓN"
    if(btnEnviarCorreccion) {
        btnEnviarCorreccion.addEventListener('click', async function() {
            if (!fileInput.files[0]) return;

            const formData = new FormData();
            formData.append('folio', folio);
            formData.append('archivo_editado', fileInput.files[0]);
            formData.append('mensaje_admin', txtMensajeAdmin.value); 

            try {
                const response = await fetch('../../api_subir_correcion.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Corrección enviada exitosamente.');
                    cerrarModal();
                    iframe.src = `../../${result.new_path}?t=${new Date().getTime()}`; 
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error de conexión');
            }
        });
    }

    // 6. Firmar
    if(btnFirmar) {
        btnFirmar.addEventListener('click', async () => {
            const esCorreccion = btnFirmar.textContent.includes("Corrección");
            const msgConfirm = esCorreccion 
                ? "¿Confirmas que has revisado la corrección y deseas finalizar el reporte?"
                : "¿Estás seguro de firmar y aprobar este documento?";

            if(confirm(msgConfirm)) {
                const formData = new FormData();
                formData.append('folio', folio);
                
                try {
                    const response = await fetch('../../api_firmar_documento.php', { method: 'POST', body: formData });
                    const text = await response.text(); // Obtenemos texto primero para depurar si falla el JSON
                    
                    try {
                        const result = JSON.parse(text);
                        
                        if (result.success) {
                            // --- MODIFICACIÓN APLICADA: REDIRECCIÓN INTELIGENTE ---
                            alert("✅ ¡Firma registrada con éxito!\n\n" + (result.message || "El documento se ha actualizado."));
                            
                            // Verificamos el rol en la URL para saber a dónde regresar
                            const role = new URLSearchParams(window.location.search).get('role');
                            
                            if (role === 'firmante') {
                                // Si es docente firmando, vuelve a su lista de firmas
                                window.location.href = '../Expedientes y solicitudes/firmas.html';
                            } else {
                                // Si es admin aprobando, vuelve a la bandeja
                                window.location.href = 'bandeja.html'; 
                            }
                            // ------------------------------------------------------

                        } else {
                            alert("Error del servidor: " + result.message);
                        }
                    } catch (e) {
                        console.error("Respuesta no válida del servidor:", text);
                        alert("Hubo un error técnico al firmar. Revisa la consola.");
                    }
                } catch (error) {
                    alert("Error de conexión. Inténtalo de nuevo.");
                }
            }
        });
    }
});