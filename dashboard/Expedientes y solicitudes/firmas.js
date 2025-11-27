document.addEventListener("DOMContentLoaded", function() {
    const tablaBody = document.getElementById('tabla-firmas');

    async function loadFirmas() {
        tablaBody.innerHTML = '<tr><td colspan="5" style="text-align:center">Cargando...</td></tr>';
        
        try {
            const res = await fetch('../../api_get_firmas_pendientes.php');
            const data = await res.json();

            if (data.error) {
                tablaBody.innerHTML = `<tr><td colspan="5" style="color:red;">${data.error}</td></tr>`;
                return;
            }

            if (data.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="5" style="text-align:center">No tienes firmas pendientes.</td></tr>';
                return;
            }

            tablaBody.innerHTML = '';
            data.forEach(doc => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${doc.folio}</td>
                    <td>${doc.documento}</td>
                    <td>${doc.solicitante}</td>
                    <td>${doc.fecha}</td>
                    <td>
                        <a href="../Bandeja de solicitudes/revisar.html?folio=${doc.folio}&role=firmante" 
                           class="btn-new" style="text-decoration:none; font-size:0.9rem; background-color:#6C3483;">
                           <i class="fas fa-pen-nib"></i> Firmar
                        </a>
                    </td>
                `;
                tablaBody.appendChild(tr);
            });

        } catch (error) {
            console.error(error);
            tablaBody.innerHTML = '<tr><td colspan="5" style="color:red;">Error de conexión</td></tr>';
        }
    }

    loadFirmas();
});