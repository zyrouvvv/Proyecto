<?php
// cabecera.php
// Parte de ARRIBA de todas las paginas: el <head> y el navbar. Se cierra con footer.php.
//
// Antes de incluirlo, cada pagina puede definir:
//   $tituloPagina -> texto del <title>
//   $claseMain    -> clase extra para <main> (por ejemplo "centrado" en login y registro)

require_once __DIR__ . '/sesion.php';

$tituloPagina = $tituloPagina ?? 'Inicio';
$claseMain = $claseMain ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($tituloPagina); ?> | Ragedit</title>
    <link rel="stylesheet" href="../styles/general.css">
    <link rel="stylesheet" href="../styles/registro.css">
</head>
<body>
<?php require __DIR__ . '/navbar.php'; ?>

<main class="contenido <?php echo h($claseMain); ?>">
