<?php


if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/ayudas.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');


function usuarioActual($pdo) {
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT usuarioID, nombreusuario, foto, rol FROM usuarios WHERE usuarioID = :id");
    $stmt->execute([':id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    if (!$usuario) {
        unset($_SESSION['usuario_id'], $_SESSION['usuario_nombre']);
        return null;
    }
    return $usuario;
}

function iniciarSesion($usuarioID, $nombreusuario) {
    session_regenerate_id(true); 
    $_SESSION['usuario_id'] = (int)$usuarioID;
    $_SESSION['usuario_nombre'] = $nombreusuario;
}

function cerrarSesion() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $c = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $c['path'], $c['domain'], $c['secure'], $c['httponly']);
    }
    session_destroy();
}


function exigirLogin($pdo) {
    $usuario = usuarioActual($pdo);
    if ($usuario === null) {
        $volver = basename($_SERVER['SCRIPT_NAME']);
        if (!empty($_SERVER['QUERY_STRING'])) {
            $volver .= '?' . $_SERVER['QUERY_STRING'];
        }
        header('Location: login.php?volver=' . urlencode($volver));
        exit;
    }
    return $usuario;
}


function exigirAdmin($pdo) {
    $usuario = exigirLogin($pdo);
    if ($usuario['rol'] !== 'admin') {
        http_response_code(403);
        die('Solo los administradores pueden entrar a esta página. <a href="index.php">Volver al inicio</a>');
    }
    return $usuario;
}


function campoCsrf() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '">';
}

function verificarCsrf() {
    $enviado = $_POST['csrf'] ?? '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $enviado)) {
        http_response_code(403);
        die('Pedido no válido (token de seguridad incorrecto). Volvé atrás y probá de nuevo.');
    }
}
