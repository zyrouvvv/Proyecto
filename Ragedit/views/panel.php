<?php

require '../includes/sesion.php';
$admin = exigirAdmin($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    if (($_POST['accion'] ?? '') === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);

        $stmt = $pdo->prepare("SELECT usuarioID, foto FROM usuarios WHERE usuarioID = :id");
        $stmt->execute([':id' => $id]);
        $objetivo = $stmt->fetch();

        if (!$objetivo) {
            flash('error', 'El usuario no existe.');
        } elseif ($id === (int)$admin['usuarioID']) {
            flash('error', 'No podés eliminar tu propia cuenta desde el panel.');
        } else {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE usuarioID = :id");
            $stmt->execute([':id' => $id]);
            borrarFoto($objetivo['foto']);
            flash('ok', 'Usuario eliminado.');
        }
    }

    $q = trim($_POST['q'] ?? '');
    header("Location: panel.php" . ($q !== '' ? '?q=' . urlencode($q) : ''));
    exit;
}

$q = mb_substr(trim($_GET['q'] ?? ''), 0, 100);

if ($q !== '') {
    $comodin = '%' . addcslashes($q, '%_\\') . '%';
    $stmt = $pdo->prepare("SELECT usuarioID, nombreusuario, email, foto, estado, rol, registro
                           FROM usuarios
                           WHERE nombreusuario LIKE :q1 OR email LIKE :q2
                           ORDER BY usuarioID");
    $stmt->execute([':q1' => $comodin, ':q2' => $comodin]);
} else {
    $stmt = $pdo->query("SELECT usuarioID, nombreusuario, email, foto, estado, rol, registro FROM usuarios ORDER BY usuarioID");
}
$usuarios = $stmt->fetchAll();

$tituloPagina = "Panel de administración";
$scripts = ['panel.js'];
require '../includes/cabecera.php';
?>

    <div class="barra-titulo">
        <h1>Panel de administración</h1>
        <a class="boton" href="crear_usuario.php">+ Nuevo usuario</a>
    </div>

    <?php mostrarFlash(); ?>

    <h2>Lista de usuarios (CRUD)</h2>

    <form class="filtros" method="GET" action="panel.php" style="margin-bottom:16px;">
        <input type="search" name="q" placeholder="Buscar por nombre o correo..." value="<?php echo h($q); ?>" maxlength="100">
        <button class="boton" type="submit">Buscar</button>
        <?php if ($q !== ''): ?>
            <a class="boton boton-secundario" href="panel.php">Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Rol</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($usuarios) === 0): ?>
                    <tr><td colspan="8" class="vacio">No hay usuarios que coincidan.</td></tr>
                <?php endif; ?>

                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?php echo (int)$u['usuarioID']; ?></td>
                    <td><img src="<?php echo h(urlFoto($u['foto'])); ?>" alt=""></td>
                    <td><?php echo h($u['nombreusuario']); ?></td>
                    <td><?php echo h($u['email']); ?></td>
                    <td><?php echo h($u['estado']); ?></td>
                    <td class="<?php echo $u['rol'] === 'admin' ? 'rol-admin' : ''; ?>"><?php echo h($u['rol']); ?></td>
                    <td><?php echo h(formatearFecha($u['registro'])); ?></td>
                    <td>
                        <div class="tabla-acciones">
                            <a class="boton boton-chico" href="editar.php?id=<?php echo (int)$u['usuarioID']; ?>">Editar</a>
                            <?php if ((int)$u['usuarioID'] !== (int)$admin['usuarioID']): ?>
                                <form class="formulario-eliminar" method="POST" action="panel.php">
                                    <?php echo campoCsrf(); ?>
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?php echo (int)$u['usuarioID']; ?>">
                                    <input type="hidden" name="q" value="<?php echo h($q); ?>">
                                    <button type="submit" class="boton boton-peligro boton-chico">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php require '../includes/footer.php'; ?>
