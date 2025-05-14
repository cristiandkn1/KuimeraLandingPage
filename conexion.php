<?php
// Datos de conexión
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "kuimera";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

// Establecer conjunto de caracteres
$conn->set_charset("utf8mb4");
?>
