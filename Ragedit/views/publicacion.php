<?php


require '../includes/sesion.php';
$usuario = usuarioActual($pdo);
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT p.publicacionID, p.titulo, p.contenido, p.fechacreacion, u.nombreusuario, u.foto, c.nombre AS categoria
                       FROM publicaciones p
                       JOIN usuarios u ON u.usuarioID = p.usuarioID
                       JOIN categoria c ON c.categoriaID = p.categoriaID
                       WHERE p.publicacionID = :id");
$stmt->execute([':id' => $id]);
$publicacion = $stmt->fetch();

if (!$publicacion) {
    http_response_code(404);
    $tituloPagina = "Publicación no encontrada";
    require '../includes/cabecera.php';
    echo '<div class="tarjeta"><p class="vacio">La publicación no existe o fue eliminada.</p>';
    echo '<p class="volver"><a href="index.php">Volver al inicio</a></p></div>';
    require '../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $usuario = exigirLogin($pdo); 
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'comentar') {
        $texto = trim($_POST['contenido'] ?? '');
        if ($texto === '') {
            flash('error', 'El comentario no puede estar vacío.');
        } elseif (mb_strlen($texto) > 500) {
            flash('error', 'El comentario puede tener hasta 500 caracteres.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO comentarios (publicacionID, usuarioID, contenido) VALUES (:p, :u, :c)");
            $stmt->execute([':p' => $id, ':u' => $usuario['usuarioID'], ':c' => $texto]);
            flash('ok', 'Comentario publicado.');
        }

    } elseif ($accion === 'eliminar_comentario') {
        $comentarioID = (int)($_POST['comentarioID'] ?? 0);
        $stmt = $pdo->prepare("SELECT usuarioID FROM comentarios WHERE comentarioID = :c AND publicacionID = :p");
        $stmt->execute([':c' => $comentarioID, ':p' => $id]);
        $comentario = $stmt->fetch();

        if ($comentario && ((int)$comentario['usuarioID'] === (int)$usuario['usuarioID'] || $usuario['rol'] === 'admin')) {
            $stmt = $pdo->prepare("DELETE FROM comentarios WHERE comentarioID = :c");
            $stmt->execute([':c' => $comentarioID]);
            flash('ok', 'Comentario eliminado.');
        } else {
            flash('error', 'No podés eliminar ese comentario.');
        }

    } elseif ($accion === 'calificar') {
        $tipo = $_POST['tipo'] ?? '';
        if ($tipo === 'me_gusta' || $tipo === 'no_me_gusta') {

            $pdo->beginTransaction(); 
            try {
                $stmt = $pdo->prepare("SELECT likeID, tipo FROM `like` WHERE usuarioID = :u AND publicacionID = :p");
                $stmt->execute([':u' => $usuario['usuarioID'], ':p' => $id]);
                $anterior = $stmt->fetch();

                if ($anterior) {
                    $stmt = $pdo->prepare("DELETE FROM `like` WHERE likeID = :l");
                    $stmt->execute([':l' => $anterior['likeID']]);
                }
                if (!$anterior || $anterior['tipo'] !== $tipo) {
                    $stmt = $pdo->prepare("INSERT INTO `like` (usuarioID, publicacionID, tipo) VALUES (:u, :p, :t)");
                    $stmt->execute([':u' => $usuario['usuarioID'], ':p' => $id, ':t' => $tipo]);
                }
                $pdo->commit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                flash('error', 'No se pudo registrar tu calificación.');
            }
        }
    }

    header("Location: publicacion.php?id=" . $id . "#comentarios");
    exit;
}

$cantidades = ['me_gusta' => 0, 'no_me_gusta' => 0];
$stmt = $pdo->prepare("SELECT tipo, COUNT(*) AS cantidad FROM `like` WHERE publicacionID = :p GROUP BY tipo");
$stmt->execute([':p' => $id]);
foreach ($stmt->fetchAll() as $fila) {
    $cantidades[$fila['tipo']] = (int)$fila['cantidad'];
}

$miVoto = null;
if ($usuario !== null) {
    $stmt = $pdo->prepare("SELECT tipo FROM `like` WHERE usuarioID = :u AND publicacionID = :p");
    $stmt->execute([':u' => $usuario['usuarioID'], ':p' => $id]);
    $fila = $stmt->fetch();
    $miVoto = $fila ? $fila['tipo'] : null;
}

$stmt = $pdo->prepare("SELECT c.comentarioID, c.contenido, c.fechacreacion, c.usuarioID, u.nombreusuario, u.foto
                       FROM comentarios c
                       JOIN usuarios u ON u.usuarioID = c.usuarioID
                       WHERE c.publicacionID = :p
                       ORDER BY c.fechacreacion ASC, c.comentarioID ASC");
$stmt->execute([':p' => $id]);
$comentarios = $stmt->fetchAll();

$tituloPagina = $publicacion['titulo'];
$scripts = ['comentarios.js'];
require '../includes/cabecera.php';
?>

    <p><a href="index.php">&laquo; Volver a las publicaciones</a></p>

    <?php mostrarFlash(); ?>

    <article class="tarjeta">
        <h1><?php echo h($publicacion['titulo']); ?></h1>
        <div class="publicacion-meta">
            <span class="etiqueta"><?php echo h($publicacion['categoria']); ?></span>
            por <strong><?php echo h($publicacion['nombreusuario']); ?></strong> &middot; <?php echo h(formatearFecha($publicacion['fechacreacion'])); ?>
        </div>

        <div class="publicacion-contenido"><?php echo nl2br(h($publicacion['contenido'])); ?></div>

        <div class="calificacion">
            <?php if ($usuario !== null): ?>
                <form method="POST" action="publicacion.php?id=<?php echo $id; ?>">
                    <?php echo campoCsrf(); ?>
                    <input type="hidden" name="accion" value="calificar">
                    <input type="hidden" name="tipo" value="me_gusta">
                    <button type="submit" class="voto <?php echo $miVoto === 'me_gusta' ? 'activo-positivo' : ''; ?>">👍 Me gusta (<?php echo $cantidades['me_gusta']; ?>)</button>
                </form>
                <form method="POST" action="publicacion.php?id=<?php echo $id; ?>">
                    <?php echo campoCsrf(); ?>
                    <input type="hidden" name="accion" value="calificar">
                    <input type="hidden" name="tipo" value="no_me_gusta">
                    <button type="submit" class="voto <?php echo $miVoto === 'no_me_gusta' ? 'activo-negativo' : ''; ?>">👎 No me gusta (<?php echo $cantidades['no_me_gusta']; ?>)</button>
                </form>
            <?php else: ?>
                <span>👍 <?php echo $cantidades['me_gusta']; ?></span>
                <span>👎 <?php echo $cantidades['no_me_gusta']; ?></span>
                <span><a href="login.php?volver=<?php echo urlencode('publicacion.php?id=' . $id); ?>">Iniciá sesión</a> para calificar.</span>
            <?php endif; ?>
        </div>
    </article>

    <section class="tarjeta" id="comentarios">
        <h2><?php echo count($comentarios); ?> comentario<?php echo count($comentarios) === 1 ? '' : 's'; ?></h2>

        <?php if (count($comentarios) === 0): ?>
            <p class="vacio">Todavía no hay comentarios. ¡Sé el primero!</p>
        <?php endif; ?>

        <?php foreach ($comentarios as $c): ?>
            <div class="comentario">
                <div class="comentario-cabecera">
                    <img src="<?php echo h(urlFoto($c['foto'])); ?>" alt="">
                    <strong><?php echo h($c['nombreusuario']); ?></strong>
                    <span><?php echo h(formatearFecha($c['fechacreacion'])); ?></span>

                    <?php if ($usuario !== null && ((int)$c['usuarioID'] === (int)$usuario['usuarioID'] || $usuario['rol'] === 'admin')): ?>
                        <form method="POST" action="publicacion.php?id=<?php echo $id; ?>" style="margin:0 0 0 auto;">
                            <?php echo campoCsrf(); ?>
                            <input type="hidden" name="accion" value="eliminar_comentario">
                            <input type="hidden" name="comentarioID" value="<?php echo (int)$c['comentarioID']; ?>">
                            <button type="submit" class="boton boton-peligro boton-chico">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </div>
                <p class="comentario-texto"><?php echo nl2br(h($c['contenido'])); ?></p>
            </div>
        <?php endforeach; ?>

        <?php if ($usuario !== null): ?>
            <form class="formulario-comentario" method="POST" action="publicacion.php?id=<?php echo $id; ?>">
                <?php echo campoCsrf(); ?>
                <input type="hidden" name="accion" value="comentar">
                <textarea id="contenidoComentario" name="contenido" rows="3" maxlength="500" placeholder="Escribí un comentario..." required></textarea>
                <div class="contador-caracteres" id="contadorCaracteres"></div>
                <button type="submit" class="boton">Comentar</button>
            </form>
        <?php else: ?>
            <p><a href="login.php?volver=<?php echo urlencode('publicacion.php?id=' . $id); ?>">Iniciá sesión</a> para comentar.</p>
        <?php endif; ?>
    </section>

<?php require '../includes/footer.php'; ?>
