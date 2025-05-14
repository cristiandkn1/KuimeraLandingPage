<?php
include 'conexion.php';

$id = $_POST['idhosting'];
$nombre = $_POST['nombre'];
$precio = $_POST['precio'];
$duracion = $_POST['duracion'];
$gb = $_POST['gb'];

$stmt = $conn->prepare("UPDATE hosting SET nombre = ?, precio = ?, duracion = ?, gb = ? WHERE idhosting = ?");
$stmt->bind_param("sdiii", $nombre, $precio, $duracion, $gb, $id);

if ($stmt->execute()) {
    header("Location: index.php");
} else {
    echo "Error al actualizar hosting: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
