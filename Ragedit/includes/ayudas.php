<?php


const ESTADOS = ['Activo', 'Ocupado', 'Ausente', 'Inactivo'];
const ROLES = ['usuario', 'admin'];


function h($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function formatearFecha($fecha) {
    if (empty($fecha)) {
        return '-';
    }
    return date('d/m/Y H:i', strtotime($fecha));
}

function urlFoto($foto) {
    if (!empty($foto) && is_file(__DIR__ . '/../img/' . basename($foto))) {
        return '../img/' . rawurlencode(basename($foto));
    }
    return '../img/avatar.svg';
}


function flash($tipo, $texto) {
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

function mostrarFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<p class="mensaje mensaje-' . h($f['tipo']) . '">' . h($f['texto']) . '</p>';
    }
}

function errorCampo($errores, $campo) {
    if (!empty($errores[$campo])) {
        echo '<span class="error-campo">' . h($errores[$campo]) . '</span>';
    }
}


function validarUsuario($pdo, $datos, $excepto = 0, $pedirClave = true) {
    $errores = [];
    $nombre = $datos['nombre'] ?? '';
    $email = $datos['email'] ?? '';
    $clave = $datos['clave'] ?? '';

    if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 50) {
        $errores['nombre'] = 'El nombre debe tener entre 3 y 50 caracteres.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        $errores['email'] = 'Ingresa un correo electrónico válido.';
    } else {
        $stmt = $pdo->prepare("SELECT 1 FROM usuarios WHERE email = :email AND usuarioID <> :id");
        $stmt->execute([':email' => $email, ':id' => $excepto]);
        if ($stmt->fetch()) {
            $errores['email'] = 'Ese correo electrónico ya está registrado.';
        }
    }

    if ($pedirClave || $clave !== '') {
        if (strlen($clave) < 8 || !preg_match('/[A-Za-z]/', $clave) || !preg_match('/[0-9]/', $clave)) {
            $errores['clave'] = 'La contraseña debe tener al menos 8 caracteres, con letras y números.';
        } elseif (isset($datos['clave2']) && $clave !== $datos['clave2']) {
            $errores['clave2'] = 'Las contraseñas no coinciden.';
        }
    }

    if (isset($datos['estado']) && !in_array($datos['estado'], ESTADOS, true)) {
        $errores['estado'] = 'Elegí un estado de la lista.';
    }
    if (isset($datos['rol']) && !in_array($datos['rol'], ROLES, true)) {
        $errores['rol'] = 'Elegí un rol de la lista.';
    }

    return $errores;
}


function guardarFotoPerfil($archivo, $usuarioID) {
    if ($archivo === null || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['', ''];
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['', 'No se pudo subir la imagen (código de error ' . $archivo['error'] . ').'];
    }
    if ($archivo['size'] > 2 * 1024 * 1024) {
        return ['', 'La imagen pesa más de 2 MB.'];
    }

    $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $info = @getimagesize($archivo['tmp_name']);
    if ($info === false || !isset($tipos[$info['mime']])) {
        return ['', 'Formato de imagen no permitido. Solo JPG, PNG, GIF o WEBP.'];
    }

    $carpeta = __DIR__ . '/../img/';
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0775, true);
    }

    $nombre = 'user_' . (int)$usuarioID . '_' . time() . '.' . $tipos[$info['mime']];
    if (!move_uploaded_file($archivo['tmp_name'], $carpeta . $nombre)) {
        return ['', 'No se pudo guardar la imagen en la carpeta img (revisá los permisos).'];
    }
    return [$nombre, ''];
}


function borrarFoto($nombre) {
    if (strpos((string)$nombre, 'user_') !== 0) {
        return;
    }
    $ruta = __DIR__ . '/../img/' . basename($nombre);
    if (is_file($ruta)) {
        unlink($ruta);
    }
}
