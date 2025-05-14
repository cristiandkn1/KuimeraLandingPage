<?php
include 'conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Iniciar transacción para garantizar que ambos cambios ocurran juntos
    $conn->begin_transaction();

    try {
        // Marcar la empresa como eliminada
        $stmt1 = $conn->prepare("UPDATE empresa SET eliminado = 1 WHERE idempresa = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();

        // Marcar los cobros como eliminados
        $stmt2 = $conn->prepare("UPDATE historial_cobros SET eliminado = 1 WHERE idempresa = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();

        // Confirmar los cambios
        $conn->commit();
        header("Location: index.php");

    } catch (Exception $e) {
        // Si algo falla, deshacer todo
        $conn->rollback();
        echo "❌ Error al eliminar: " . $e->getMessage();
    }

    // Cerrar conexiones
    if (isset($stmt1)) $stmt1->close();
    if (isset($stmt2)) $stmt2->close();
    $conn->close();
}
?>
