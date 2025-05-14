<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idempresa         = $_POST['idempresa'];
    $nombre_empresa    = $_POST['nombre_empresa'];
    $nombre_encargado  = $_POST['nombre_encargado'];
    $numero            = $_POST['numero'];
    $correo            = $_POST['correo']; // <- nuevo campo
    $proyecto_vendido  = $_POST['proyecto_vendido'];
    $precio_proyecto   = $_POST['precio_proyecto'];
    $dias_restantes    = $_POST['dias_restantes'];
    $fecha_venta       = $_POST['fecha_venta'];
    $idhosting         = $_POST['idhosting'];
    $iddominio         = $_POST['iddominio'];

    // Actualizar datos empresa (ahora con correo)
    $sql = "UPDATE empresa 
            SET nombre_empresa = ?, 
                nombre_encargado = ?, 
                numero = ?, 
                correo = ?, 
                proyecto_vendido = ?, 
                precio_proyecto = ?, 
                dias_restantes = ?, 
                fecha_venta = ?
            WHERE idempresa = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssdisi", 
        $nombre_empresa, 
        $nombre_encargado, 
        $numero, 
        $correo,
        $proyecto_vendido, 
        $precio_proyecto, 
        $dias_restantes, 
        $fecha_venta, 
        $idempresa
    );
    $stmt->execute();
    $stmt->close();

    // Verificar y actualizar relación con hosting y dominio
    $check = $conn->prepare("SELECT idempresa FROM empresa_hosting_dominio WHERE idempresa = ?");
    $check->bind_param("i", $idempresa);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $updateRelacion = $conn->prepare("UPDATE empresa_hosting_dominio SET idhosting = ?, iddominio = ? WHERE idempresa = ?");
        $updateRelacion->bind_param("iii", $idhosting, $iddominio, $idempresa);
        $updateRelacion->execute();
        $updateRelacion->close();
    } else {
        $insertRelacion = $conn->prepare("INSERT INTO empresa_hosting_dominio (idempresa, idhosting, iddominio) VALUES (?, ?, ?)");
        $insertRelacion->bind_param("iii", $idempresa, $idhosting, $iddominio);
        $insertRelacion->execute();
        $insertRelacion->close();
    }

    echo "<script>window.location.href='index.php';</script>";
    $check->close();
    $conn->close();
}
?>
