document.addEventListener("DOMContentLoaded", function() {

    const track = document.getElementById('carousel-track');
    const arrowLeft = document.getElementById('arrow-left');
    const arrowRight = document.getElementById('arrow-right');
    const docNombre = document.getElementById('doc-nombre');
    const docCount = document.getElementById('doc-count');
    const expPeriodo = document.getElementById('exp-periodo');
    const btnReportar = document.getElementById('btn-reportar');
    const modalReporte = document.getElementById('modal-reporte');
    const backdropReporte = document.getElementById('modal-backdrop-reporte');
    const btnCancelarReporte = document.getElementById('btn-cancelar-reporte');
    const formReporte = document.getElementById('form-reporte');
    const txtMensaje = document.getElementById('txt-mensaje-reporte');

    let currentIndex = 0;
    let totalSlides = 0;
    let documentsData = [];

    const urlParams = new URLSearchParams(window.location.search);
    const idExpediente = urlParams.get('id');

    if (!idExpediente) {
        docNombre.textContent = "Error: No se especificó un expediente.";
        return;
    }

    async function loadVisualData() {
        try {
            const response = await fetch(`../../api_get_visualizar_detalle.php?id_expediente=${idExpediente}`);
            const data = await response.json();
            if (data.error) { throw new Error(data.error); }

            documentsData = data.documentos;
            totalSlides = data.documentos_count;
            expPeriodo.textContent = data.expediente_periodo;
            docCount.textContent = data.documentos_count;

            buildCarousel();
        } catch (error) {
            console.error('Error al cargar datos:', error);
            docNombre.textContent = error.message;
        }
    }

    function buildCarousel() {
        track.innerHTML = ''; 
        
        if (totalSlides === 0) {
            track.innerHTML = '<p>Este expediente no tiene documentos para visualizar.</p>';
            updateArrows();
            return;
        }

        documentsData.forEach(doc => {
            const slide = document.createElement('div');
            slide.className = 'carousel-slide';
            
            const iframe = document.createElement('iframe');
            iframe.src = `/MI_ESPACIO_EDD/${doc.ruta}`; 
            
            slide.appendChild(iframe);
            track.appendChild(slide);
        });

        updateCarouselView();
    }

    function updateCarouselView() {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        if (documentsData[currentIndex]) {
            docNombre.textContent = documentsData[currentIndex].nombre;
            // LÓGICA NUEVA: Mostrar botón solo si es un documento generado por el sistema (tiene folio)
            if (documentsData[currentIndex].folio) {
                btnReportar.classList.remove('hidden');
                btnReportar.dataset.folio = documentsData[currentIndex].folio; // Guardamos el folio en el botón
            } else {
                btnReportar.classList.add('hidden');
            }
        }
        updateArrows();
    }

    function updateArrows() {
        arrowLeft.classList.toggle('hidden', currentIndex === 0);
        arrowRight.classList.toggle('hidden', currentIndex >= totalSlides - 1);
    }

    // Listeners del Reporte
    btnReportar.addEventListener('click', () => {
        backdropReporte.classList.remove('hidden');
        modalReporte.classList.remove('hidden');
    });

    function cerrarModalReporte() {
        backdropReporte.classList.add('hidden');
        modalReporte.classList.add('hidden');
        txtMensaje.value = '';
    }
    btnCancelarReporte.addEventListener('click', cerrarModalReporte);
    backdropReporte.addEventListener('click', cerrarModalReporte);

    formReporte.addEventListener('submit', async (e) => {
        e.preventDefault();
        const folio = btnReportar.dataset.folio;
        const mensaje = txtMensaje.value;

        if(!mensaje) return alert("Escriba un mensaje");

        const formData = new FormData();
        formData.append('folio', folio);
        formData.append('mensaje', mensaje);

        const res = await fetch('../../api_reportar_documento.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if(data.success) {
            alert(data.message);
            cerrarModalReporte();
        } else {
            alert(data.message);
        }
    });

    arrowRight.addEventListener('click', () => {
        if (currentIndex < totalSlides - 1) {
            currentIndex++;
            updateCarouselView();
        }
    });

    arrowLeft.addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateCarouselView();
        }
    });

    loadVisualData();
});