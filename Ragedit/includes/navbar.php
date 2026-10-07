<?php
$usuarioNav = usuarioActual($pdo);
$textoBusqueda = $_GET['q'] ?? '';
?>
<header class="navbar">
    <div class="navbar-contenido">
        <a class="navbar-logo" href="index.php">Ragedit</a>

        <button class="navbar-boton" id="botonMenu" type="button" aria-label="Abrir menú" aria-expanded="false">&#9776;</button>

        <nav class="navbar-menu" id="menuPrincipal">
            <form class="navbar-buscador" action="index.php" method="GET" role="search">
                <input type="search" name="q" placeholder="Buscar publicaciones..." value="<?php echo h($textoBusqueda); ?>" maxlength="100" aria-label="Buscar publicaciones">
                <button type="submit">Buscar</button>
            </form>

            <a href="index.php">Inicio</a>

            <?php if ($usuarioNav !== null): ?>
                <a href="crear_publicacion.php">Nueva publicación</a>
                <?php if ($usuarioNav['rol'] === 'admin'): ?>
                    <a href="panel.php">Panel</a>
                <?php endif; ?>
                <a class="navbar-usuario" href="perfil.php">
                    <img src="<?php echo h(urlFoto($usuarioNav['foto'])); ?>" alt="" class="navbar-foto">
                    <span><?php echo h($usuarioNav['nombreusuario']); ?></span>
                </a>
                <a href="logout.php">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a>
                <a class="navbar-destacado" href="registro.php">Registrarse</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
