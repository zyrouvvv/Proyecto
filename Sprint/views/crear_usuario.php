<?php
// crear_usuario.php
// CRUD DE USUARIOS - Crear: el administrador registra un usuario nuevo desde el panel.
// Usa las mismas reglas de validacion que el registro publico (validarUsuario en includes/ayudas.php).

require '../includes/sesion.php';
$admin = exigirAdmin($pdo);

$errores = [];
$valores = ['nombre' => '', 'email' => '', 'estado' => 'Activo', 'rol' => 'usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $valores['nombre'] = trim($_POST['nombre'] ?? '');
    $valores['email'] = trim($_POST['email'] ?? '');
    $valores['estado'] = $_POST['estado'] ?? '';
    $valores['rol'] = $_POST['rol'] ?? '';
    $clave = $_POST['clave'] ?? '';

    $errores = validarUsuario($pdo, [
        'nombre' => $valores['nombre'],
        'email'  => $valores['email'],
        'clave'  => $clave,
        'clave2' => $_POST['clave2'] ?? '',
        'estado' => $valores['estado'],
        'rol'    => $valores['rol']
    ]);

    if (count($errores) === 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombreusuario, email, `contraseña`, estado, rol)
                                   VALUES (:nombre, :email, :pass, :estado, :rol)");
            $stmt->execute([
                ':nombre' => $valores['nombre'],
                ':email'  => $valores['email'],
                ':pass'   => password_hash($clave, PASSWORD_BCRYPT),
                ':estado' => $valores['estado'],
                ':rol'    => $valores['rol']
            ]);

            flash('ok', 'Usuario creado.');
            header("Location: panel.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errores['email'] = 'Ese correo electrónico ya está registrado.';
            } else {
                $errores['general'] = 'Error al crear el usuario. Probá de nuevo.';
            }
        }
    }
}

$modo = 'crear';
$accionForm = 'crear_usuario.php';
$tituloPagina = "Nuevo usuario";
$claseMain = "centrado";
require '../includes/cabecera.php';
?>

    <div class="registro-container ancho">
        <h2>Nuevo usuario</h2>

        <?php if (!empty($errores['general'])): ?>
            <p class="mensaje mensaje-error"><?php echo h($errores['general']); ?></p>
        <?php endif; ?>

        <?php require '../includes/form_usuario.php'; ?>

        <p class="volver"><a href="panel.php">&laquo; Volver al panel</a></p>
    </div>

<?php require '../includes/footer.php'; ?>
