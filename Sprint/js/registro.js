const formulario = document.getElementById('formularioRegistro');
const mensaje = document.getElementById('mensaje');

formulario.addEventListener('submit', function(evento) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (password !== confirmPassword) {
        evento.preventDefault(); 
        mensaje.style.color = "red";
        mensaje.innerText = "Error: Las contraseñas no coinciden.";
    } else {
        mensaje.innerText = "";
    }
});