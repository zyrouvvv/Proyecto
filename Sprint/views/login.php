<?php
session_start();
require '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT usuarioID, nombreusuario, email, contraseña FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['contraseña'])) {
        $_SESSION['usuario_id'] = $usuario['usuarioID'];
        $_SESSION['usuario_nombre'] = $usuario['nombreusuario'];
        header("Location: panel.php");
        exit;
    } else {
        $error = "Correo o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="../styles/registro.css">
</head>
<body>
    <div class="registro-container">
        <h2>Iniciar Sesión</h2>
        <?php if (isset($error)) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email" id="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>