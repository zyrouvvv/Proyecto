<?php

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
    <p class="footer-copyright">&copy; <?php echo date('Y'); ?> Ragedit</p>
</footer>

<script src="../js/navbar.js"></script>
<?php foreach (($scripts ?? []) as $script): ?>
<script src="../js/<?php echo h($script); ?>"></script>
<?php endforeach; ?>
</body>
</html>
