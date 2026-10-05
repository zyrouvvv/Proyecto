<?php
// form_usuario.php
// Formulario que comparten crear_usuario.php y editar.php (asi no se escribe dos veces).
// Variables que tiene que definir la pagina que lo incluye:
//   $modo       -> 'crear' o 'editar'
//   $accionForm -> a donde se manda el formulario
//   $valores    -> ['nombre', 'email', 'estado', 'rol']
//   $errores    -> errores por campo (puede estar vacio)
?>
<form method="POST" action="<?php echo h($accionForm); ?>">
    <?php echo campoCsrf(); ?>

    <div class="form-group">
        <label for="nombre">Nombre de usuario:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo h($valores['nombre']); ?>" required minlength="3" maxlength="50">
        <?php errorCampo($errores, 'nombre'); ?>
    </div>

    <div class="form-group">
        <label for="email">Correo electrónico:</label>
        <input type="email" id="email" name="email" value="<?php echo h($valores['email']); ?>" required maxlength="100">
        <?php errorCampo($errores, 'email'); ?>
    </div>

    <div class="form-group">
        <label for="clave"><?php echo $modo === 'crear' ? 'Contraseña:' : 'Nueva contraseña (dejala vacía para no cambiarla):'; ?></label>
        <input type="password" id="clave" name="clave" placeholder="Mínimo 8 caracteres, con letras y números" <?php echo $modo === 'crear' ? 'required' : ''; ?> minlength="8" autocomplete="new-password">
        <?php errorCampo($errores, 'clave'); ?>
    </div>

    <div class="form-group">
        <label for="clave2">Repetir contraseña:</label>
        <input type="password" id="clave2" name="clave2" <?php echo $modo === 'crear' ? 'required' : ''; ?> autocomplete="new-password">
        <?php errorCampo($errores, 'clave2'); ?>
    </div>

    <div class="form-group">
        <label for="estado">Estado:</label>
        <select id="estado" name="estado">
            <?php foreach (ESTADOS as $estado): ?>
                <option value="<?php echo h($estado); ?>" <?php echo $valores['estado'] === $estado ? 'selected' : ''; ?>><?php echo h($estado); ?></option>
            <?php endforeach; ?>
        </select>
        <?php errorCampo($errores, 'estado'); ?>
    </div>

    <div class="form-group">
        <label for="rol">Rol:</label>
        <select id="rol" name="rol">
            <?php foreach (ROLES as $rol): ?>
                <option value="<?php echo h($rol); ?>" <?php echo $valores['rol'] === $rol ? 'selected' : ''; ?>><?php echo h($rol); ?></option>
            <?php endforeach; ?>
        </select>
        <?php errorCampo($errores, 'rol'); ?>
    </div>

    <button type="submit"><?php echo $modo === 'crear' ? 'Crear usuario' : 'Guardar cambios'; ?></button>
</form>
