<?php
include 'conexion.php';

$id = $_POST['iddominio'];
$nombre = $_POST['nombre'];
$precio = $_POST['precio'];
$duracion = $_POST['duracion'];

$stmt = $conn->prepare("UPDATE dominio SET nombre = ?, precio = ?, duracion = ? WHERE iddominio = ?");
$stmt->bind_param("sdii", $nombre, $precio, $duracion, $id);

if ($stmt->execute()) {
    header("Location: index.php");
} else {
    echo "Error al actualizar dominio: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
