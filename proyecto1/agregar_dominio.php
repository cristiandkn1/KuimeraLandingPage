<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $duracion = $_POST['duracion'];

    $stmt = $conn->prepare("INSERT INTO dominio (nombre, precio, duracion, fecha_inicio) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sdi", $nombre, $precio, $duracion);

    if ($stmt->execute()) {
        header("Location: index.php");
    } else {
        echo "Error al agregar dominio: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
