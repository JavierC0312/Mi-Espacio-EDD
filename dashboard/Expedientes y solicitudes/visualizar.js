document.addEventListener("DOMContentLoaded", function() {

    const track = document.getElementById('carousel-track');
    const arrowLeft = document.getElementById('arrow-left');
    const arrowRight = document.getElementById('arrow-right');
    const docNombre = document.getElementById('doc-nombre');
    const docCount = document.getElementById('doc-count');
    const expPeriodo = document.getElementById('exp-periodo');

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
        }
        updateArrows();
    }

    function updateArrows() {
        arrowLeft.classList.toggle('hidden', currentIndex === 0);
        arrowRight.classList.toggle('hidden', currentIndex >= totalSlides - 1);
    }

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