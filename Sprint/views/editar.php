<?php
// editar.php?id=N
// CRUD DE USUARIOS - Editar: el administrador cambia el nombre, correo, estado y rol de un usuario,
// y opcionalmente le pone una contraseña nueva. (En el sprint 1 el panel enlazaba este archivo pero no existia.)

require '../includes/sesion.php';
$admin = exigirAdmin($pdo);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT usuarioID, nombreusuario, email, estado, rol FROM usuarios WHERE usuarioID = :id");
$stmt->execute([':id' => $id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    flash('error', 'El usuario no existe.');
    header("Location: panel.php");
    exit;
}

$errores = [];
$valores = [
    'nombre' => $usuario['nombreusuario'],
    'email'  => $usuario['email'],
    'estado' => $usuario['estado'] ?? 'Activo',
    'rol'    => $usuario['rol']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $valores['nombre'] = trim($_POST['nombre'] ?? '');
    $valores['email'] = trim($_POST['email'] ?? '');
    $valores['estado'] = $_POST['estado'] ?? '';
    $valores['rol'] = $_POST['rol'] ?? '';
    $clave = $_POST['clave'] ?? '';

    // la contraseña es opcional al editar ($pedirClave = false) y el correo no puede repetirse con otro usuario
    $errores = validarUsuario($pdo, [
        'nombre' => $valores['nombre'],
        'email'  => $valores['email'],
        'clave'  => $clave,
        'clave2' => $_POST['clave2'] ?? '',
        'estado' => $valores['estado'],
        'rol'    => $valores['rol']
    ], $id, false);

    // un administrador no puede quitarse a si mismo el rol (asi nunca queda el sistema sin administradores)
    if ($id === (int)$admin['usuarioID'] && $valores['rol'] !== 'admin') {
        $errores['rol'] = 'No podés quitarte a vos mismo el rol de administrador.';
    }

    if (count($errores) === 0) {
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombreusuario = :nombre, email = :email, estado = :estado, rol = :rol
                                   WHERE usuarioID = :id");
            $stmt->execute([
                ':nombre' => $valores['nombre'],
                ':email'  => $valores['email'],
                ':estado' => $valores['estado'],
                ':rol'    => $valores['rol'],
                ':id'     => $id
            ]);

            if ($clave !== '') {
                $stmt = $pdo->prepare("UPDATE usuarios SET `contraseña` = :pass WHERE usuarioID = :id");
                $stmt->execute([':pass' => password_hash($clave, PASSWORD_BCRYPT), ':id' => $id]);
            }

            if ($id === (int)$admin['usuarioID']) {
                $_SESSION['usuario_nombre'] = $valores['nombre'];
            }

            flash('ok', 'Cambios guardados.');
            header("Location: panel.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errores['email'] = 'Ese correo electrónico ya está registrado.';
            } else {
                $errores['general'] = 'Error al guardar los cambios. Probá de nuevo.';
            }
        }
    }
}

$modo = 'editar';
$accionForm = 'editar.php?id=' . $id;
$tituloPagina = "Editar usuario";
$claseMain = "centrado";
require '../includes/cabecera.php';
?>

    <div class="registro-container ancho">
        <h2>Editar usuario #<?php echo $id; ?></h2>

        <?php if (!empty($errores['general'])): ?>
            <p class="mensaje mensaje-error"><?php echo h($errores['general']); ?></p>
        <?php endif; ?>

        <?php require '../includes/form_usuario.php'; ?>

        <p class="volver"><a href="panel.php">&laquo; Volver al panel</a></p>
    </div>

<?php require '../includes/footer.php'; ?>
