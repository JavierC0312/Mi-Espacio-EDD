// Espera a que todo el contenido HTML se cargue
document.addEventListener("DOMContentLoaded", function() {

    // --- LÓGICA DE MOSTRAR/OCULTAR CONTRASEÑA ---
    const togglePassword = document.getElementById('toggle-password');
    const password = document.getElementById('password');

    if (togglePassword) { // Comprobamos que el elemento exista
        togglePassword.addEventListener('click', function () {
            // Cambia el tipo de input (password/text)
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Cambia el ícono del ojo
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    // --- LÓGICA DE LOGIN ---
    
    // 1. Seleccionar el formulario y el párrafo de error
    const loginForm = document.getElementById("login-form");
    
    const errorMessage = document.getElementById("error-message");

    if (loginForm) { // Comprobamos que el formulario exista
        
        // 2. Escuchar el evento "submit" del formulario
        loginForm.addEventListener("submit", function(event) {
            
            // 3. Prevenir el envío tradicional
            event.preventDefault(); 

            // Limpiar errores previos
            if (errorMessage) { errorMessage.textContent = ''; }

            // 4. Recopilar los datos del formulario
            const formData = new FormData(loginForm);

            // 5. Enviar los datos usando fetch
            fetch('../api_login.php', {
                method: 'POST',
                body: formData 
            })
            .then(response => response.json()) // Esperamos una respuesta JSON
            .then(data => {
                // 6. Procesar la respuesta del servidor
                if (data.success) {
                    // ¡Éxito! Redirigir al dashboard
                    window.location.href = '../dashboard/dashboard.html';
                } else {
                    // Falla: Mostrar el mensaje de error
                    if (errorMessage) {
                        errorMessage.textContent = data.message;
                    } else {
                        alert(data.message); // Plan B si no existe el párrafo
                    }
                }
            })
            .catch(error => {
                // 7. Capturar errores de conexión
                console.error('Error en fetch:', error);
                if (errorMessage) {
                    errorMessage.textContent = 'Error de conexión. Inténtalo más tarde.';
                }
            });
        });
    }
});