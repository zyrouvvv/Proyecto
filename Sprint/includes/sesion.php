<?php
// sesion.php
// Arranca la sesion y tiene las funciones para saber quien inicio sesion.
// Todas las vistas empiezan con:  require '../includes/sesion.php';
// (este archivo ya trae la conexion a la base de datos y las ayudas)

if (session_status() === PHP_SESSION_NONE) {
    // httponly: el JavaScript no puede leer la cookie. samesite: no se manda desde otros sitios.
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/ayudas.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');

// Devuelve los datos del usuario que inicio sesion (usuarioID, nombreusuario, foto, rol) o null si no hay nadie.
// Se lee de la base de datos en cada pedido: si borraron al usuario, la sesion deja de valer sola.
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
    session_regenerate_id(true); // cambia el id de la sesion (seguridad)
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

// Para las paginas que piden sesion. Si no hay, manda al login y despues vuelve a la pagina que se queria ver.
// Devuelve los datos del usuario.
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

// Para las paginas solo de administradores
function exigirAdmin($pdo) {
    $usuario = exigirLogin($pdo);
    if ($usuario['rol'] !== 'admin') {
        http_response_code(403);
        die('Solo los administradores pueden entrar a esta página. <a href="index.php">Volver al inicio</a>');
    }
    return $usuario;
}

// ---------- Proteccion CSRF ----------
// Todos los formularios llevan un campo escondido con un codigo secreto de la sesion.
// Si el codigo no coincide, el pedido no viene de nuestro formulario y se rechaza.
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
