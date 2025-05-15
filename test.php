<?php
$conexion = new mysqli("localhost", "cku109429_cku109429", "kuikui2025", "cku109429_kuimera");

if ($conexion->connect_error) {
    die("❌ Error al conectar: " . $conexion->connect_error);
}

echo "✅ Conexión exitosa a la base de datos.";
?>
