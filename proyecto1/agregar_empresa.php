<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_empresa    = $_POST['nombre_empresa'];
    $nombre_encargado  = $_POST['nombre_encargado'];
    $numero            = $_POST['numero'];
    $correo            = $_POST['correo'];
    $proyecto_vendido  = $_POST['proyecto_vendido'];
    $precio_proyecto   = $_POST['precio_proyecto'];
    $dias_restantes    = $_POST['dias_restantes'];
    $fecha_venta       = $_POST['fecha_venta'];
    $idhosting         = $_POST['idhosting'];
    $iddominio         = $_POST['iddominio'];

    // 1. Insertar empresa (ahora con correo)
    $sql = "INSERT INTO empresa (
                nombre_empresa, nombre_encargado, numero, correo, 
                proyecto_vendido, precio_proyecto, dias_restantes, fecha_venta
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssdis", 
        $nombre_empresa, 
        $nombre_encargado, 
        $numero, 
        $correo,
        $proyecto_vendido, 
        $precio_proyecto, 
        $dias_restantes, 
        $fecha_venta
    );

    if ($stmt->execute()) {
        $idempresa = $conn->insert_id;

        // 2. Insertar relación con hosting y dominio
        $relacion = "INSERT INTO empresa_hosting_dominio (
                        idempresa, idhosting, iddominio, 
                        fecha_inicio_hosting, fecha_inicio_dominio
                    ) VALUES (?, ?, ?, CURDATE(), CURDATE())";

        $stmt2 = $conn->prepare($relacion);
        $stmt2->bind_param("iii", $idempresa, $idhosting, $iddominio);
        $stmt2->execute();
        $stmt2->close();

        // 3. Obtener precios del dominio y hosting
        $total_hosting = 0;
        $total_dominio = 0;

        if (!empty($idhosting)) {
            $stmtHost = $conn->prepare("SELECT precio FROM hosting WHERE idhosting = ?");
            $stmtHost->bind_param("i", $idhosting);
            $stmtHost->execute();
            $stmtHost->bind_result($total_hosting);
            $stmtHost->fetch();
            $stmtHost->close();
        }

        if (!empty($iddominio)) {
            $stmtDom = $conn->prepare("SELECT precio FROM dominio WHERE iddominio = ?");
            $stmtDom->bind_param("i", $iddominio);
            $stmtDom->execute();
            $stmtDom->bind_result($total_dominio);
            $stmtDom->fetch();
            $stmtDom->close();
        }

        // 4. Calcular total inicial (proyecto + hosting + dominio)
        $monto_total = $precio_proyecto + $total_hosting + $total_dominio;

        // 5. Insertar cobro inicial en historial_cobros
        $observacion = 'Cobro inicial (incluye instalación de Hosting y Dominio)';
        $insertCobro = $conn->prepare("
            INSERT INTO historial_cobros (idempresa, fecha_cobro, monto, observacion) 
            VALUES (?, NOW(), ?, ?)
        ");
        $insertCobro->bind_param("ids", $idempresa, $monto_total, $observacion);
        $insertCobro->execute();
        $insertCobro->close();

        // 6. Redirigir
        echo "<script>window.location.href='index.php';</script>";

    } else {
        echo "Error al insertar empresa: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
