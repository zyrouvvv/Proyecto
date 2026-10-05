<?php
// index.php
// Pagina de inicio: lista de publicaciones con BUSCADOR y filtro por categoria.
//   index.php?q=texto            busca en titulo, contenido, autor y categoria
//   index.php?categoria=3        solo las de esa categoria
//   index.php?pagina=2           pagina 2 de los resultados (de a 10)

require '../includes/sesion.php';
$usuario = usuarioActual($pdo);

$porPagina = 10;
$q = mb_substr(trim($_GET['q'] ?? ''), 0, 100);
$categoriaID = (int)($_GET['categoria'] ?? 0);
$pagina = max(1, (int)($_GET['pagina'] ?? 1));

// Categorias para el desplegable de filtro
$categorias = $pdo->query("SELECT categoriaID, nombre FROM categoria ORDER BY nombre")->fetchAll();

// ---------- Armamos el WHERE segun los filtros ----------
// Los valores van como parametros (:q1, :categoria...) y no pegados al texto de la consulta:
// asi nadie puede meter SQL a traves del buscador (inyeccion SQL).
$condiciones = [];
$valores = [];

if ($q !== '') {
    $comodin = '%' . addcslashes($q, '%_\\') . '%'; // % y _ se buscan como letras comunes
    $condiciones[] = "(p.titulo LIKE :q1 OR p.contenido LIKE :q2 OR u.nombreusuario LIKE :q3 OR c.nombre LIKE :q4)";
    $valores[':q1'] = $comodin;
    $valores[':q2'] = $comodin;
    $valores[':q3'] = $comodin;
    $valores[':q4'] = $comodin;
}
if ($categoriaID > 0) {
    $condiciones[] = "p.categoriaID = :categoria";
    $valores[':categoria'] = $categoriaID;
}
$where = count($condiciones) > 0 ? "WHERE " . implode(" AND ", $condiciones) : "";

$desde = "FROM publicaciones p
          JOIN usuarios u ON u.usuarioID = p.usuarioID
          JOIN categoria c ON c.categoriaID = p.categoriaID
          $where";

// ---------- Cuantos resultados hay (para la paginacion) ----------
$stmt = $pdo->prepare("SELECT COUNT(*) $desde");
$stmt->execute($valores);
$total = (int)$stmt->fetchColumn();
$totalPaginas = max(1, (int)ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);
$salto = ($pagina - 1) * $porPagina;

// ---------- Las publicaciones de esta pagina ----------
$sql = "SELECT p.publicacionID, p.titulo, p.contenido, p.fechacreacion, u.nombreusuario, c.nombre AS categoria,
               (SELECT COUNT(*) FROM `like` l WHERE l.publicacionID = p.publicacionID AND l.tipo = 'me_gusta') AS me_gusta,
               (SELECT COUNT(*) FROM `like` l WHERE l.publicacionID = p.publicacionID AND l.tipo = 'no_me_gusta') AS no_me_gusta,
               (SELECT COUNT(*) FROM comentarios cm WHERE cm.publicacionID = p.publicacionID) AS cant_comentarios
        $desde
        ORDER BY p.fechacreacion DESC, p.publicacionID DESC
        LIMIT $porPagina OFFSET $salto";   // $porPagina y $salto son numeros que calculamos nosotros
$stmt = $pdo->prepare($sql);
$stmt->execute($valores);
$publicaciones = $stmt->fetchAll();

// ---------- Historial de busquedas (tabla historialbusqueda) ----------
// Si alguien con sesion busca algo, se guarda (salvo que sea la misma busqueda que la ultima)
$busquedasRecientes = [];
if ($usuario !== null) {
    if ($q !== '' && !isset($_GET['pagina'])) {
        $stmt = $pdo->prepare("SELECT busqueda FROM historialbusqueda WHERE usuarioID = :u ORDER BY busquedaID DESC LIMIT 1");
        $stmt->execute([':u' => $usuario['usuarioID']]);
        $ultima = $stmt->fetch();
        if (!$ultima || $ultima['busqueda'] !== $q) {
            $stmt = $pdo->prepare("INSERT INTO historialbusqueda (usuarioID, busqueda) VALUES (:u, :b)");
            $stmt->execute([':u' => $usuario['usuarioID'], ':b' => $q]);
        }
    }

    $stmt = $pdo->prepare("SELECT busqueda, MAX(busquedaID) AS ultimo
                           FROM historialbusqueda WHERE usuarioID = :u
                           GROUP BY busqueda ORDER BY ultimo DESC LIMIT 5");
    $stmt->execute([':u' => $usuario['usuarioID']]);
    $busquedasRecientes = $stmt->fetchAll();
}

// Arma el link a otra pagina de resultados conservando la busqueda y la categoria
function urlPagina($numero, $q, $categoriaID) {
    $parametros = ['pagina' => $numero];
    if ($q !== '') {
        $parametros['q'] = $q;
    }
    if ($categoriaID > 0) {
        $parametros['categoria'] = $categoriaID;
    }
    return 'index.php?' . http_build_query($parametros);
}

$tituloPagina = "Inicio";
require '../includes/cabecera.php';
?>

    <div class="barra-titulo">
        <h1>Publicaciones</h1>
        <?php if ($usuario !== null): ?>
            <a class="boton" href="crear_publicacion.php">+ Nueva publicación</a>
        <?php endif; ?>
    </div>

    <?php mostrarFlash(); ?>

    <div class="tarjeta">
        <form class="filtros" method="GET" action="index.php">
            <input type="search" name="q" placeholder="Buscar por título, contenido, autor o juego..." value="<?php echo h($q); ?>" maxlength="100">
            <select name="categoria" aria-label="Filtrar por juego">
                <option value="0">Todos los juegos</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo (int)$cat['categoriaID']; ?>" <?php echo $categoriaID === (int)$cat['categoriaID'] ? 'selected' : ''; ?>>
                        <?php echo h($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="boton" type="submit">Buscar</button>
            <?php if ($q !== '' || $categoriaID > 0): ?>
                <a class="boton boton-secundario" href="index.php">Limpiar</a>
            <?php endif; ?>
        </form>

        <?php if (count($busquedasRecientes) > 0): ?>
            <p class="busquedas-recientes">
                Tus últimas búsquedas:
                <?php foreach ($busquedasRecientes as $b): ?>
                    <a href="index.php?q=<?php echo urlencode($b['busqueda']); ?>"><?php echo h($b['busqueda']); ?></a>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if ($q !== '' || $categoriaID > 0): ?>
        <p><?php echo $total; ?> resultado<?php echo $total === 1 ? '' : 's'; ?><?php echo $q !== '' ? ' para "' . h($q) . '"' : ''; ?></p>
    <?php endif; ?>

    <?php if (count($publicaciones) === 0): ?>
        <div class="tarjeta"><p class="vacio">No encontramos publicaciones.</p></div>
    <?php endif; ?>

    <?php foreach ($publicaciones as $p): ?>
        <article class="tarjeta publicacion-item">
            <h3><a href="publicacion.php?id=<?php echo (int)$p['publicacionID']; ?>"><?php echo h($p['titulo']); ?></a></h3>
            <div class="publicacion-meta">
                <span class="etiqueta"><?php echo h($p['categoria']); ?></span>
                por <strong><?php echo h($p['nombreusuario']); ?></strong> &middot; <?php echo h(formatearFecha($p['fechacreacion'])); ?>
            </div>
            <p class="publicacion-resumen"><?php echo h(mb_strimwidth($p['contenido'], 0, 220, '...')); ?></p>
            <div class="publicacion-numeros">
                <span>👍 <?php echo (int)$p['me_gusta']; ?></span>
                <span>👎 <?php echo (int)$p['no_me_gusta']; ?></span>
                <span>💬 <?php echo (int)$p['cant_comentarios']; ?> comentario<?php echo (int)$p['cant_comentarios'] === 1 ? '' : 's'; ?></span>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if ($totalPaginas > 1): ?>
        <div class="paginacion">
            <?php if ($pagina > 1): ?>
                <a class="boton boton-secundario" href="<?php echo h(urlPagina($pagina - 1, $q, $categoriaID)); ?>">&laquo; Anterior</a>
            <?php endif; ?>
            <span>Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?></span>
            <?php if ($pagina < $totalPaginas): ?>
                <a class="boton boton-secundario" href="<?php echo h(urlPagina($pagina + 1, $q, $categoriaID)); ?>">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php require '../includes/footer.php'; ?>
