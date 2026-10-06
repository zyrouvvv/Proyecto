document.addEventListener('DOMContentLoaded', () => {
    const commentForms = document.querySelectorAll('.form-comentario');

    commentForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const publicacionId = form.dataset.publicacionId;
            const input = form.querySelector('input[name="comentario"]');
            const texto = input.value.trim();

            if (!texto) return;

            const formData = new FormData();
            formData.append('publicacion_id', publicacionId);
            formData.append('comentario', texto);

            try {
                const response = await fetch('agregar_comentario.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    const contenedorComentarios = document.getElementById(`comentarios-list-${publicacionId}`);
                    
                    // Crear nuevo elemento de comentario
                    const nuevoComentario = document.createElement('div');
                    nuevoComentario.classList.add('comentario-item');
                    nuevoComentario.innerHTML = `
                        <strong>${escapeHTML(result.usuario)}:</strong>
                        <span>${escapeHTML(result.comentario)}</span>
                        <small style="color: #888; font-size: 11px;">Hace un momento</small>
                    `;

                    contenedorComentarios.appendChild(nuevoComentario);
                    input.value = ''; // Limpiar input
                } else {
                    alert(result.error || 'Ocurrió un error al publicar el comentario.');
                }
            } catch (error) {
                console.error('Error al procesar la solicitud:', error);
            }
        });
    });
});

// Función aux para evitar Inyección XSS en el cliente
function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, 
        tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag)
    );
}