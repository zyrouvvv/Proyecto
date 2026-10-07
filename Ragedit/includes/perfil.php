<?php

session_start();
require '../includes/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');

if (empty($nombre) || empty($email)) {
    echo json_encode(['success' => false, 'error' => 'Nombre y correo son obligatorios']);
    exit;
}

$foto_nombre = null;

// Procesar la subida de la foto de perfil
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['foto']['tmp_name'];
    $fileName = $_FILES['foto']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($extensiones_permitidas, $extensiones_permitidas)) {
        // Generar un nombre único para evitar sobreescribir archivos
        $foto_nombre = 'user_' . $usuario_id . '_' . time() . '.' . $fileExtension;
        $uploadFileDir = '../uploads/';

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $dest_path = $uploadFileDir . $foto_nombre;

        if (!move_uploaded_file($fileTmpPath, $dest_path)) {
            echo json_encode(['success' => false, 'error' => 'Error al guardar la imagen en el servidor']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Formato de imagen no permitido. Usa JPG, PNG o WEBP']);
        exit;
    }
}

// Actualizar en la base de datos
try {
    if ($foto_nombre) {
        $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, foto = :foto WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'email' => $email,
            'foto' => $foto_nombre,
            'id' => $usuario_id
        ]);
    } else {
        $sql = "UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'email' => $email,
            'id' => $usuario_id
        ]);
    }

    // Actualizar variable de sesión
    $_SESSION['usuario_nombre'] = $nombre;

    echo json_encode([
        'success' => true,
        'mensaje' => 'Perfil actualizado con éxito',
        'foto' => $foto_nombre ? '../uploads/' . $foto_nombre : null
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>