<?php
// login.php
// Inicio de sesion con correo y contraseña.

require '../includes/sesion.php';

// "volver" es la pagina a la que queria entrar la persona antes de que la mandemos al login.
// Solo se aceptan paginas de este mismo sitio (evita que nos redirijan a otra web).
$volver = $_GET['volver'] ?? $_POST['volver'] ?? '';
if (!preg_match('/^[a-z_]+\.php(\?[A-Za-z0-9_=&%.-]*)?$/', $volver) || preg_match('/^(login|registro|logout)\.php/', $volver)) {
    $volver = 'index.php';
}

// Si ya inicio sesion, no hace falta mostrar el login
if (usuarioActual($pdo) !== null) {
    header("Location: " . $volver);
    exit;
}

$error = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT usuarioID, nombreusuario, email, `contraseña` AS clave FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['clave'])) {
        iniciarSesion($usuario['usuarioID'], $usuario['nombreusuario']);
        header("Location: " . $volver);
        exit;
    } else {
        // el mismo mensaje para "no existe el correo" y "contraseña mal": asi no se sabe cual de los dos fallo
        $error = "Correo o contraseña incorrectos.";
    }
}

$tituloPagina = "Iniciar sesión";
$claseMain = "centrado";
require '../includes/cabecera.php';
?>

    <div class="registro-container">
        <h2>Iniciar Sesión</h2>

        <?php mostrarFlash(); ?>

        <?php if ($error !== ""): ?>
            <p class="mensaje mensaje-error"><?php echo h($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?php echo campoCsrf(); ?>
            <input type="hidden" name="volver" value="<?php echo h($volver); ?>">

            <div class="form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email" id="email" name="email" placeholder="Correo electrónico" value="<?php echo h($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit">Entrar</button>
        </form>

        <p class="volver">¿No tenés cuenta? <a href="registro.php">Registrate</a></p>
    </div>

<?php require '../includes/footer.php'; ?>
