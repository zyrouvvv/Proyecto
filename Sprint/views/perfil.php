<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuarioID = $_SESSION['usuario_id'];$mensaje = "";
$tipoMensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreusuario = trim($_POST['nombreusuario']);
    $email = trim($_POST['email']);
    $desc = trim($_POST['desc']);
    $estado = trim($_POST['estado']);

    
    $nombreFoto =$_POST['foto_actual'] ?? ''; 

    if (isset($_FILES['foto']) &&$_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath =$_FILES['foto']['tmp_name'];
        $fileName =$_FILES['foto']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExtension,$allowedExtensions)) {
            $nuevoNombreFoto = "user_" . $usuarioID . "_" . time() . "." . $fileExtension;
            $uploadFileDir = '../img/';$dest_path = $uploadFileDir .$nuevoNombreFoto;

            if (move_uploaded_file($fileTmpPath,$dest_path)) {
                $nombreFoto =$nuevoNombreFoto;
            } else {
                $mensaje = "Error al mover la imagen a la carpeta img.";
                $tipoMensaje = "red";
            }
        } else {
            $mensaje = "Formato de imagen no permitido. Solo JPG, PNG, GIF o WEBP.";
            $tipoMensaje = "red";
        }
    }

    if (empty($mensaje) || $tipoMensaje === "green") {
        try {
            $sql = "UPDATE usuarios 
                    SET nombreusuario = :nombre, email = :email, `desc` = :descripcion, foto = :foto, estado = :estado 
                    WHERE usuarioID = :id";
            $stmt = $pdo->prepare($sql);
            $resultado =$stmt->execute([
                ':nombre'      => $nombreusuario,
                ':email'       => $email,
                ':descripcion' => $desc,
                ':foto'        => $nombreFoto,
                ':estado'      => $estado,
                ':id'          => $usuarioID
            ]);

            if ($resultado) {
                $_SESSION['usuario_nombre'] =$nombreusuario;
                $mensaje = "¡Perfil actualizado con éxito!";
                $tipoMensaje = "green";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {$mensaje = "El correo electrónico ya pertenece a otra cuenta.";
            } else {
                $mensaje = "Error al actualizar: " . $e->getMessage();
            }
            $tipoMensaje = "red";
        }
    }
}

$stmt =$pdo->prepare("SELECT usuarioID, nombreusuario, email, `desc`, foto, estado, registro FROM usuarios WHERE usuarioID = :id");
$stmt->execute([':id' =>$usuarioID]);
$usuario =$stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="../styles/registro.css">
    <style>
        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 10px auto;
            border: 2px solid #007bff;
        }
    </style>
</head>
<body>

    <div class="registro-container">
        <h2>Mi Perfil</h2>
        <p style="text-align: center;">
            <a href="panel.php">← Volver al Panel</a> | <a href="logout.php">Cerrar Sesión</a>
        </p>

        <?php if (!empty($mensaje)): ?>
            <p style="color: <?php echo $tipoMensaje; ?>; font-weight: bold; text-align: center;">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>

        <!-- Muestra la foto de perfil actual o una por defecto -->
        <?php if (!empty($usuario['foto']) && file_exists('../img/' .$usuario['foto'])): ?>
            <img src="../img/<?php echo htmlspecialchars($usuario['foto']); ?>" alt="Foto de Perfil" class="profile-img">
        <?php else: ?>
            <img src="https://via.placeholder.com/100?text=Usuario" alt="Sin Foto" class="profile-img">
        <?php endif; ?>

        <form method="POST" action="perfil.php" enctype="multipart/form-data">
            <input type="hidden" name="foto_actual" value="<?php echo htmlspecialchars($usuario['foto'] ?? ''); ?>">

            <div class="form-group">
                <label for="foto">Cambiar foto de perfil:</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>

            <div class="form-group">
                <label for="nombreusuario">Nombre de usuario:</label>
                <input type="text" id="nombreusuario" name="nombreusuario" 
                       value="<?php echo htmlspecialchars($usuario['nombreusuario'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="estado">Estado del usuario:</label>
                <select id="estado" name="estado" style="width: 100%; padding: 8px;">
                    <option value="Activo" <?php echo ($usuario['estado'] ?? '') === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="Ocupado" <?php echo ($usuario['estado'] ?? '') === 'Ocupado' ? 'selected' : ''; ?>>Ocupado</option>
                    <option value="Ausente" <?php echo ($usuario['estado'] ?? '') === 'Ausente' ? 'selected' : ''; ?>>Ausente</option>
                    <option value="Inactivo" <?php echo ($usuario['estado'] ?? '') === 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="desc">Descripción / Biografía:</label>
                <textarea id="desc" name="desc" rows="4" style="width: 100%; box-sizing: border-box;"><?php echo htmlspecialchars($usuario['desc'] ?? ''); ?></textarea>
            </div>

            <p><small>Miembro desde: <?php echo htmlspecialchars($usuario['registro'] ?? 'N/A'); ?></small></p>

            <button type="submit">Guardar Cambios</button>
        </form>
    </div>

</body>
</html>