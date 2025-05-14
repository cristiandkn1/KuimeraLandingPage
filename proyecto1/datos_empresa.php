<?php
include 'conexion.php';

$id = intval($_GET['id'] ?? 0);

$sql = $conn->prepare("SELECT * FROM empresa WHERE idempresa = ?");
$sql->bind_param("i", $id);
$sql->execute();
$result = $sql->get_result();
$empresa = $result->fetch_assoc();

if (!$empresa) {
    echo "<div class='text-danger'>Empresa no encontrada.</div>";
    exit;
}
?>

<ul class="list-group list-group-flush">
  <li class="list-group-item"><strong>Empresa:</strong> <?= htmlspecialchars($empresa['nombre_empresa']) ?></li>
  <li class="list-group-item"><strong>Encargado:</strong> <?= htmlspecialchars($empresa['nombre_encargado']) ?></li>
  <li class="list-group-item"><strong>Teléfono:</strong> <?= htmlspecialchars($empresa['numero']) ?></li>
  <li class="list-group-item"><strong>Correo:</strong> <?= htmlspecialchars($empresa['correo']) ?></li>
  <li class="list-group-item"><strong>Proyecto:</strong> <?= htmlspecialchars($empresa['proyecto_vendido']) ?></li>
  <li class="list-group-item"><strong>Precio Proyecto:</strong> $<?= number_format($empresa['precio_proyecto'], 0, ',', '.') ?></li>
  <li class="list-group-item"><strong>Fecha de Venta:</strong> <?= date('d-m-Y', strtotime($empresa['fecha_venta'])) ?></li>
</ul>
