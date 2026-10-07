<?php

require '../includes/sesion.php';
$usuarioSesion = exigirLogin($pdo);
$usuarioID = (int)$usuarioSesion['usuarioID'];

$stmt = $pdo->prepare("SELECT usuarioID, nombreusuario, email, `desc`, foto, estado, registro FROM usuarios WHERE usuarioID = :id");
$stmt->execute([':id' => $usuarioID]);
$usuario = $stmt->fetch();

$valores = [
    'nombreusuario' => $usuario['nombreusuario'],
    'email'         => $usuario['email'],
    'desc'          => $usuario['desc'] ?? '',
    'estado'        => $usuario['estado'] ?? 'Activo'
];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $valores['nombreusuario'] = trim($_POST['nombreusuario'] ?? '');
    $valores['email'] = trim($_POST['email'] ?? '');
    $valores['desc'] = trim($_POST['desc'] ?? '');
    $valores['estado'] = $_POST['estado'] ?? '';

    $errores = validarUsuario($pdo, [
        'nombre' => $valores['nombreusuario'],
        'email'  => $valores['email'],
        'estado' => $valores['estado']
    ], $usuarioID, false);

    if (mb_strlen($valores['desc']) > 500) {
        $errores['desc'] = 'La descripción puede tener hasta 500 caracteres.';
    }

    list($fotoNueva, $errorFoto) = guardarFotoPerfil($_FILES['foto'] ?? null, $usuarioID);
    if ($errorFoto !== '') {
        $errores['foto'] = $errorFoto;
    }

    if (count($errores) > 0) {
        if ($fotoNueva !== '') {
            borrarFoto($fotoNueva);
        }
    } else {
        $nombreFoto = $fotoNueva !== '' ? $fotoNueva : $usuario['foto'];

        try {
            $sql = "UPDATE usuarios
                    SET nombreusuario = :nombre, email = :email, `desc` = :descripcion, foto = :foto, estado = :estado
                    WHERE usuarioID = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre'      => $valores['nombreusuario'],
                ':email'       => $valores['email'],
                ':descripcion' => $valores['desc'],
                ':foto'        => $nombreFoto,
                ':estado'      => $valores['estado'],
                ':id'          => $usuarioID
            ]);

            if ($fotoNueva !== '') {
                borrarFoto($usuario['foto']); 
            }
            $_SESSION['usuario_nombre'] = $valores['nombreusuario'];

            flash('ok', '¡Perfil actualizado con éxito!');
            header("Location: perfil.php");
            exit;
        } catch (PDOException $e) {
            if ($fotoNueva !== '') {
                borrarFoto($fotoNueva);
            }
            if ($e->getCode() == 23000) {
                $errores['email'] = 'Ese correo electrónico ya pertenece a otra cuenta.';
            } else {
                $errores['general'] = 'Error al actualizar el perfil. Probá de nuevo.';
            }
        }
    }
}

$tituloPagina = "Mi perfil";
$claseMain = "centrado";
require '../includes/cabecera.php';
?>

    <div class="registro-container ancho">
        <h2>Mi Perfil</h2>

        <?php mostrarFlash(); ?>

        <?php if (!empty($errores['general'])): ?>
            <p class="mensaje mensaje-error"><?php echo h($errores['general']); ?></p>
        <?php endif; ?>

        <img src="<?php echo h(urlFoto($usuario['foto'])); ?>" alt="Foto de perfil" class="profile-img">

        <form method="POST" action="perfil.php" enctype="multipart/form-data">
            <?php echo campoCsrf(); ?>

            <div class="form-group">
                <label for="foto">Cambiar foto de perfil (JPG, PNG, GIF o WEBP, hasta 2 MB):</label>
                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/gif,image/webp">
                <?php errorCampo($errores, 'foto'); ?>
            </div>

            <div class="form-group">
                <label for="nombreusuario">Nombre de usuario:</label>
                <input type="text" id="nombreusuario" name="nombreusuario" value="<?php echo h($valores['nombreusuario']); ?>" required maxlength="50">
                <?php errorCampo($errores, 'nombre'); ?>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email" id="email" name="email" value="<?php echo h($valores['email']); ?>" required maxlength="100">
                <?php errorCampo($errores, 'email'); ?>
            </div>

            <div class="form-group">
                <label for="estado">Estado del usuario:</label>
                <select id="estado" name="estado">
                    <?php foreach (ESTADOS as $estado): ?>
                        <option value="<?php echo h($estado); ?>" <?php echo $valores['estado'] === $estado ? 'selected' : ''; ?>><?php echo h($estado); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php errorCampo($errores, 'estado'); ?>
            </div>

            <div class="form-group">
                <label for="desc">Descripción / Biografía:</label>
                <textarea id="desc" name="desc" rows="4" maxlength="500"><?php echo h($valores['desc']); ?></textarea>
                <?php errorCampo($errores, 'desc'); ?>
            </div>

            <p><small>Miembro desde: <?php echo h(formatearFecha($usuario['registro'])); ?></small></p>

            <button type="submit">Guardar Cambios</button>
        </form>

        <p class="volver"><a href="index.php">&laquo; Volver al inicio</a></p>
    </div>

<?php require '../includes/footer.php'; ?>
