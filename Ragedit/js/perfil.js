document.addEventListener('DOMContentLoaded', () => {
    $formPerfil = document.getElementById('form-perfil');
    $inputFoto = document.getElementById('input-foto');
    $imgPreview = document.getElementById('preview-foto');
    $mensajeEstado = document.getElementById('mensaje-perfil');

    // Previsualización instantánea de la imagen al seleccionarla
    if ($inputFoto && $imgPreview) {
        $inputFoto.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    $imgPreview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Envío del formulario sin recargar la página
    if ($formPerfil) {
        $formPerfil.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData($formPerfil);

            try {
                const response = await fetch('../views/actualizar_perfil.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    $mensajeEstado.style.color = 'green';
                    $mensajeEstado.textContent = result.mensaje;

                    // Si se actualizó la foto, aseguramos refrescar la vista
                    if (result.foto) {
                        $imgPreview.src = result.foto + '?t=' + new Date().getTime();
                    }
                } else {
                    $mensajeEstado.style.color = 'red';
                    $mensajeEstado.textContent = result.error || 'Ocurrió un error al guardar los cambios';
                }
            } catch (error) {
                console.error('Error al actualizar el perfil:', error);
                $mensajeEstado.style.color = 'red';
                $mensajeEstado.textContent = 'Error de conexión con el servidor.';
            }
        });
    }
});