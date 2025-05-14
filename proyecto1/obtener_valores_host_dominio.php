<?php
include 'conexion.php';
$idempresa = $_POST['idempresa'];

$query = "SELECT 
            h.precio AS host, 
            d.precio AS dominio 
          FROM empresa_hosting_dominio ehd 
          LEFT JOIN hosting h ON ehd.idhosting = h.idhosting 
          LEFT JOIN dominio d ON ehd.iddominio = d.iddominio 
          WHERE ehd.idempresa = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idempresa);
$stmt->execute();
$stmt->bind_result($host, $dominio);
$stmt->fetch();
$stmt->close();

echo json_encode([
  "host" => $host,
  "dominio" => $dominio
]);
?>
