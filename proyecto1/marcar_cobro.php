<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idempresa = $_POST['idempresa'];
    $tipo_cobro = $_POST['tipo_cobro'];

    if ($tipo_cobro === 'proyecto') {
        // Proyecto mensual
        $stmt = $conn->prepare("SELECT precio_proyecto FROM empresa WHERE idempresa = ?");
        $stmt->bind_param("i", $idempresa);
        $stmt->execute();
        $stmt->bind_result($precio);
        $stmt->fetch();
        $stmt->close();

        $stmtUpdate = $conn->prepare("UPDATE empresa SET fecha_venta = CURDATE(), dias_restantes = 30 WHERE idempresa = ?");
        $stmtUpdate->bind_param("i", $idempresa);
        $mensaje = "Se reiniciaron los 30 días del Proyecto";

    } elseif ($tipo_cobro === 'hosting') {
        // Hosting anual
        $stmt = $conn->prepare("
            SELECT h.precio, h.duracion FROM hosting h
            INNER JOIN empresa_hosting_dominio ehd ON ehd.idhosting = h.idhosting
            WHERE ehd.idempresa = ?");
        $stmt->bind_param("i", $idempresa);
        $stmt->execute();
        $stmt->bind_result($precio, $duracion_anios);
        $stmt->fetch();
        $stmt->close();

        $dias_hosting = $duracion_anios * 365;

        $stmtUpdate = $conn->prepare("UPDATE empresa_hosting_dominio SET fecha_inicio_hosting = CURDATE() WHERE idempresa = ?");
        $stmtUpdate->bind_param("i", $idempresa);
        $mensaje = "Se reiniciaron los días del Hosting por " . $duracion_anios . " año(s)";

    } elseif ($tipo_cobro === 'dominio') {
        // Dominio anual
        $stmt = $conn->prepare("
            SELECT d.precio, d.duracion FROM dominio d
            INNER JOIN empresa_hosting_dominio ehd ON ehd.iddominio = d.iddominio
            WHERE ehd.idempresa = ?");
        $stmt->bind_param("i", $idempresa);
        $stmt->execute();
        $stmt->bind_result($precio, $duracion_anios);
        $stmt->fetch();
        $stmt->close();

        $dias_dominio = $duracion_anios * 365;

        $stmtUpdate = $conn->prepare("UPDATE empresa_hosting_dominio SET fecha_inicio_dominio = CURDATE() WHERE idempresa = ?");
        $stmtUpdate->bind_param("i", $idempresa);
        $mensaje = "Se reiniciaron los días del Dominio por " . $duracion_anios . " año(s)";
    }

    $stmtUpdate->execute();
    $stmtUpdate->close();

    // Insertar cobro en historial
    $insertHistorial = $conn->prepare("INSERT INTO historial_cobros (idempresa, monto, fecha_cobro, observacion) VALUES (?, ?, NOW(), ?)");
    $insertHistorial->bind_param("ids", $idempresa, $precio, $mensaje);
    $insertHistorial->execute();
    $insertHistorial->close();

    $conn->close();

    // Alerta con mensaje dinámico según tipo de cobro
    header("Location: index.php?cobro=ok&mensaje=" . urlencode($mensaje));
exit;
}
?>
