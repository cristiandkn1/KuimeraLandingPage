<?php
// Datos de conexión al hosting
$host = "localhost";
$usuario = "cku109429_cku109429";
$contrasena = "kuikui2025";
$base_datos = "cku109429_kuimera";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

// Establecer conjunto de caracteres
$conn->set_charset("utf8mb4");
?>