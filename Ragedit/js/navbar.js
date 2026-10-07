document.addEventListener('DOMContentLoaded', () => {
    const inputBuscar = document.querySelector('input[name="buscar"]');
    const formBuscar = inputBuscar ? inputBuscar.closest('form') : null;
    let timeout = null;

    if (inputBuscar && formBuscar) {
        // Ejecuta la búsqueda automáticamente 500ms después de que el usuario deja de escribir
        inputBuscar.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if (inputBuscar.value.trim().length >= 2 || inputBuscar.value.trim().length === 0) {
                    formBuscar.submit();
                }
            }, 500);
        });
    }
});