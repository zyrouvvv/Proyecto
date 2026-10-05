<?php
// logout.php
// Cierra la sesion y vuelve al login. (En el sprint 1 el panel lo enlazaba pero el archivo no existia.)

require '../includes/sesion.php';

cerrarSesion();
header("Location: login.php");
exit;
