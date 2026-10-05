<?php
// crear_publicacion.php
// REGISTRO DE PUBLICACIONES: formulario para crear una publicacion nueva (hay que tener sesion).

require '../includes/sesion.php';
$usuario = exigirLogin($pdo);

$categorias = $pdo->query("SELECT categoriaID, nombre FROM categoria ORDER BY nombre")->fetchAll();
$idsValidos = array_map('intval', array_column($categorias, 'categoriaID'));

$errores = [];
$titulo = "";
$contenido = "";
$categoriaID = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $categoriaID = (int)($_POST['categoria'] ?? 0);

    if ($titulo === '' || mb_strlen($titulo) > 150) {
        $errores['titulo'] = 'Escribí un título de hasta 150 caracteres.';
    }
    if (!in_array($categoriaID, $idsValidos, true)) {
        $errores['categoria'] = 'Elegí un juego de la lista.';
    }
    if ($contenido === '' || mb_strlen($contenido) > 5000) {
        $errores['contenido'] = 'Escribí el contenido (hasta 5000 caracteres).';
    }

    if (count($errores) === 0) {
        $stmt = $pdo->prepare("INSERT INTO publicaciones (usuarioID, categoriaID, titulo, contenido)
                               VALUES (:usuario, :categoria, :titulo, :contenido)");
        $stmt->execute([
            ':usuario'   => $usuario['usuarioID'],
            ':categoria' => $categoriaID,
            ':titulo'    => $titulo,
            ':contenido' => $contenido
        ]);

        flash('ok', 'Publicación creada.');
        header("Location: publicacion.php?id=" . $pdo->lastInsertId());
        exit;
    }
}

$tituloPagina = "Nueva publicación";
$claseMain = "centrado";
require '../includes/cabecera.php';
?>

    <div class="registro-container ancho">
        <h2>Nueva publicación</h2>

        <?php if (count($categorias) === 0): ?>
            <p class="mensaje mensaje-error">Todavía no hay juegos cargados en la tabla categoria.</p>
        <?php endif; ?>

        <form method="POST" action="crear_publicacion.php">
            <?php echo campoCsrf(); ?>

            <div class="form-group">
                <label for="titulo">Título:</label>
                <input type="text" id="titulo" name="titulo" value="<?php echo h($titulo); ?>" maxlength="150" required>
                <?php errorCampo($errores, 'titulo'); ?>
            </div>

            <div class="form-group">
                <label for="categoria">Juego:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Seleccioná un juego</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo (int)$cat['categoriaID']; ?>" <?php echo $categoriaID === (int)$cat['categoriaID'] ? 'selected' : ''; ?>>
                            <?php echo h($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php errorCampo($errores, 'categoria'); ?>
            </div>

            <div class="form-group">
                <label for="contenido">Contenido:</label>
                <textarea id="contenido" name="contenido" rows="8" maxlength="5000" required><?php echo h($contenido); ?></textarea>
                <?php errorCampo($errores, 'contenido'); ?>
            </div>

            <button type="submit">Publicar</button>
        </form>

        <p class="volver"><a href="index.php">Cancelar</a></p>
    </div>

<?php require '../includes/footer.php'; ?>
