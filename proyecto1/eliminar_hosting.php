<?php
include 'conexion.php';

$id = $_GET['id'];
$conn->query("UPDATE hosting SET eliminado = 1 WHERE idhosting = $id");
header("Location: index.php");
