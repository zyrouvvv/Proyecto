<?php

require_once '../includes/conexion.php';

$mensaje = "";
$tipoMensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreusuario = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $sql = "INSERT INTO usuarios (nombreusuario, email, contraseña) VALUES (:nombre, :email, :pass)";
        $stmt = $pdo->prepare($sql);
        
        $resultado = $stmt->execute([
            ':nombre' => $nombreusuario,
            ':email'  => $email,
            ':pass'   => $passwordHash
        ]);

        if ($resultado) {
            $mensaje = "¡Usuario registrado exitosamente! <a href='login.php'>Iniciar Sesión</a>";
            $tipoMensaje = "green";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $mensaje = "El correo electrónico ya está registrado.";
        } else {
            $mensaje = "Error al registrar el usuario: " . $e->getMessage();
        }
        $tipoMensaje = "red";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../styles/registro.css">
</head>
<body>

    <div class="registro-container">
        <h2>Registro de Usuario</h2>

        <?php if (!empty($mensaje)): ?>
            <p style="color: <?php echo $tipoMensaje; ?>; font-weight: bold; text-align: center;">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>

        <form id="formularioRegistro" action="registro.php" method="POST">
            <div class="form-group">
                <label for="usuario">Nombre completo:</label>
                <input type="text" id="usuario" name="nombre" placeholder="Nombre completo" required>
            </div>
            
            <div class="form-group">
                <label for="correo">Correo electrónico:</label>
                <input type="email" id="correo" name="email" placeholder="Correo electrónico" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Contraseña" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirmar Contraseña:</label>
                <input type="password" id="confirmPassword" placeholder="Confirmar contraseña" required>
            </div>

            <button type="submit">Registrar</button>
        </form>

        <p id="mensaje"></p>
    </div>

    <script src="../js/registro.js"></script>

</body>
</html>