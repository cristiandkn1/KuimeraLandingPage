<?php
include 'conexion.php';

$id = $_GET['id'];
$conn->query("UPDATE dominio SET eliminado = 1 WHERE iddominio = $id");
header("Location: index.php");
