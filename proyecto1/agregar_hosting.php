<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $duracion = $_POST['duracion'];
    $gb = $_POST['gb'];

    $stmt = $conn->prepare("INSERT INTO hosting (nombre, precio, duracion, gb, fecha_inicio) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("sdii", $nombre, $precio, $duracion, $gb);

    if ($stmt->execute()) {
        header("Location: index.php");
    } else {
        echo "Error al agregar hosting: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
