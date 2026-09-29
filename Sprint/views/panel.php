<?php
session_start();
require '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE usuarioID = :id");
    $stmt->execute(['id' => $id]);
    header("Location: panel.php");
    exit;
}

$stmt = $pdo->query("SELECT usuarioID, nombreusuario, email FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
</head>
<body>
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?> | <a href="perfil.php">Mi Perfil</a> | <a href="logout.php">Cerrar Sesión</a></h2>

    <h3>Lista de Usuarios (CRUD)</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?php echo $u['usuarioID']; ?></td>
            <td><?php echo htmlspecialchars($u['nombreusuario']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td>
                <a href="editar.php?id=<?php echo $u['usuarioID']; ?>">Editar</a> | 
                <a href="panel.php?eliminar=<?php echo $u['usuarioID']; ?>" onclick="return confirm('¿Seguro de eliminar?');">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>