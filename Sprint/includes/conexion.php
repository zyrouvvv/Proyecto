<?php
// conexion.php
// Conexion a la base de datos con PDO (la misma del sprint 1).
// Si tu MySQL tiene otra clave o la base se llama distinto, se cambia aca.

$host = "localhost";
$db = "testr";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Hora de Argentina: asi la base de datos y PHP muestran la misma hora
    $pdo->exec("SET time_zone = '-03:00'");
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
