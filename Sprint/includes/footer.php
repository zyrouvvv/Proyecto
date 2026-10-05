<?php
// footer.php
// Cierra el <main> que abrio cabecera.php, muestra el pie de pagina y carga los scripts.
//
// Antes de incluirlo, una pagina puede definir:
//   $scripts -> lista de archivos de /js que necesita, ej: ['panel.js']
?>
</main>

<footer class="footer">
    <div class="footer-contenido">
        <div>
            <strong>Ragedit</strong>
            <p>Tienda de videojuegos con foros de la comunidad.</p>
        </div>
        <div>
            <strong>Navegación</strong>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="crear_publicacion.php">Nueva publicación</a></li>
                <li><a href="perfil.php">Mi perfil</a></li>
            </ul>
        </div>
        <div>
            <strong>Cuenta</strong>
            <ul>
                <li><a href="login.php">Iniciar sesión</a></li>
                <li><a href="registro.php">Registrarse</a></li>
            </ul>
        </div>
    </div>
    <p class="footer-copyright">&copy; <?php echo date('Y'); ?> Ragedit &middot; Proyecto &middot; Sprint 2</p>
</footer>

<script src="../js/navbar.js"></script>
<?php foreach (($scripts ?? []) as $script): ?>
<script src="../js/<?php echo h($script); ?>"></script>
<?php endforeach; ?>
</body>
</html>
