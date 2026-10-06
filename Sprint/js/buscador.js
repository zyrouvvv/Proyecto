document.addEventListener('DOMContentLoaded', () => {
    const inputBuscar = document.getElementById('input-buscador');
    const contenedorResultados = document.getElementById('resultados-busqueda');
    let timeoutId = null;

    if (!inputBuscar || !contenedorResultados) return;

    // Escuchar la escritura del usuario
    inputBuscar.addEventListener('input', (e) => {
        const termino = e.target.value.trim();

        clearTimeout(timeoutId);

        // Limpiar si el término tiene menos de 2 caracteres
        if (termino.length < 2) {
            contenedorResultados.innerHTML = '';
            contenedorResultados.classList.remove('activo');
            return;
        }

        // Debounce: Espera 300ms a que el usuario termine de escribir
        timeoutId = setTimeout(() => {
            realizarBusqueda(termino);
        }, 300);
    });

    // Función que consulta el backend PHP vía Fetch API
    async function realizarBusqueda(query) {
        try {
            const response = await fetch(`actualizar_perfil.php/../buscar.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.success) {
                renderizarResultados(data.resultados);
            }
        } catch (error) {
            console.error('Error al realizar la búsqueda:', error);
        }
    }

    // Renderizar lista de resultados dinámicamente
    function renderizarResultados(lista) {
        contenedorResultados.innerHTML = '';

        if (lista.length === 0) {
            contenedorResultados.innerHTML = `<div class="sin-resultados">No se encontraron resultados</div>`;
        } else {
            const ul = document.createElement('ul');
            ul.className = 'lista-resultados';

            lista.forEach(item => {
                const li = document.createElement('li');
                li.className = 'item-resultado';
                li.innerHTML = `
                    <a href="publicacion.php?id=${item.id}" class="enlace-resultado">
                        <strong class="titulo-resultado">${escapeHTML(item.titulo)}</strong>
                        <p class="resumen-resultado">${escapeHTML(item.resumen)}</p>
                        <small class="meta-resultado">Por ${escapeHTML(item.autor)} - ${item.fecha}</small>
                    </a>
                `;
                ul.appendChild(li);
            });

            contenedorResultados.appendChild(ul);
        }

        contenedorResultados.classList.add('activo');
    }

    // Ocultar la lista al hacer clic fuera del buscador
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.buscador-container')) {
            contenedorResultados.innerHTML = '';
            contenedorResultados.classList.remove('activo');
        }
    });

    // Sanitizador contra ataques XSS
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
});