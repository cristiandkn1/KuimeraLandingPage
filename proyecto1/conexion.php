<?php
$host = "localhost";
$usuario = "root";
$clave = ""; // Cambia si tienes clave en MySQL
$bd = "misproyectos"; // Cambia esto por el nombre real de tu base de datos

$conn = new mysqli($host, $usuario, $clave, $bd);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
