<?php
// registro.php
// Registro de usuarios. Las reglas de validacion estan en includes/ayudas.php (validarUsuario)

require '../includes/sesion.php';

// Si ya inicio sesion no tiene sentido registrarse de nuevo
if (usuarioActual($pdo) !== null) {
    header("Location: index.php");
    exit;
}

$errores = [];
$nombre = "";
$email = "";
$registrado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    $errores = validarUsuario($pdo, [
        'nombre' => $nombre,
        'email'  => $email,
        'clave'  => $password,
        'clave2' => $confirmar
    ]);

    if (count($errores) === 0) {
        // La contraseña NUNCA se guarda tal cual: se guarda su hash
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $sql = "INSERT INTO usuarios (nombreusuario, email, `contraseña`, estado, rol)
                    VALUES (:nombre, :email, :pass, 'Activo', 'usuario')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email'  => $email,
                ':pass'   => $passwordHash
            ]);
            $registrado = true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errores['email'] = 'Ese correo electrónico ya está registrado.';
            } else {
                $errores['general'] = 'Error al registrar el usuario. Probá de nuevo.';
            }
        }
    }
}

$tituloPagina = "Registro de usuario";
$claseMain = "centrado";
$scripts = $registrado ? [] : ['registro.js'];
require '../includes/cabecera.php';
?>

    <div class="registro-container">
        <h2>Registro de Usuario</h2>

        <?php if ($registrado): ?>
            <p class="mensaje mensaje-ok">¡Usuario registrado exitosamente!</p>
            <p class="volver"><a href="login.php">Iniciar Sesión</a></p>
        <?php else: ?>

            <?php if (!empty($errores['general'])): ?>
                <p class="mensaje mensaje-error"><?php echo h($errores['general']); ?></p>
            <?php endif; ?>

            <form id="formularioRegistro" action="registro.php" method="POST">
                <?php echo campoCsrf(); ?>

                <div class="form-group">
                    <label for="usuario">Nombre de usuario:</label>
                    <input type="text" id="usuario" name="nombre" placeholder="Nombre de usuario" value="<?php echo h($nombre); ?>" required minlength="3" maxlength="50">
                    <?php errorCampo($errores, 'nombre'); ?>
                </div>

                <div class="form-group">
                    <label for="correo">Correo electrónico:</label>
                    <input type="email" id="correo" name="email" placeholder="Correo electrónico" value="<?php echo h($email); ?>" required maxlength="100">
                    <?php errorCampo($errores, 'email'); ?>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres, con letras y números" required minlength="8">
                    <?php errorCampo($errores, 'clave'); ?>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirmar Contraseña:</label>
                    <input type="password" id="confirmPassword" name="confirmar" placeholder="Confirmar contraseña" required>
                    <?php errorCampo($errores, 'clave2'); ?>
                </div>

                <button type="submit">Registrar</button>
            </form>

            <p id="mensaje"></p>
            <p class="volver">¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a></p>
        <?php endif; ?>
    </div>

<?php require '../includes/footer.php'; ?>
