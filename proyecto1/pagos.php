<?php
session_start();
if (!isset($_SESSION['idusuario'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// Nombre del archivo actual para marcar activo en el navbar
$paginaActual = basename(__FILE__);

// 1. Consulta del historial de pagos
$sql = "
    SELECT 
        hc.idcobro,
        hc.idempresa,
        e.nombre_empresa,
        hc.monto,
        hc.fecha_cobro,
        hc.observacion,
        h.precio AS precio_hosting,
        d.precio AS precio_dominio
    FROM historial_cobros hc
    JOIN empresa e ON hc.idempresa = e.idempresa
    LEFT JOIN empresa_hosting_dominio ehd ON e.idempresa = ehd.idempresa
    LEFT JOIN hosting h ON ehd.idhosting = h.idhosting
    LEFT JOIN dominio d ON ehd.iddominio = d.iddominio
    WHERE hc.eliminado = 0
    ORDER BY hc.fecha_cobro DESC
";
$resultado = $conn->query($sql) or die("Error en la consulta SQL: " . $conn->error);

// 2. Cálculo de totales iniciales
$total_bruto = 0;
$total_hosting = 0;
$total_dominio = 0;

$empresasPagos = $conn->query("
    SELECT 
        hc.idempresa, 
        SUM(hc.monto) AS total_pagado, 
        h.precio AS precio_hosting, 
        d.precio AS precio_dominio
    FROM historial_cobros hc
    INNER JOIN empresa e ON hc.idempresa = e.idempresa
    LEFT JOIN empresa_hosting_dominio ehd ON ehd.idempresa = e.idempresa
    LEFT JOIN hosting h ON ehd.idhosting = h.idhosting
    LEFT JOIN dominio d ON ehd.iddominio = d.iddominio
    WHERE e.eliminado = 0
    GROUP BY hc.idempresa
");

while ($e = $empresasPagos->fetch_assoc()) {
    $total_bruto += $e['total_pagado'];
    $total_hosting += $e['precio_hosting'] ?? 0;
    $total_dominio += $e['precio_dominio'] ?? 0;
}

$total_neto = $total_bruto - $total_hosting - $total_dominio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Empresas</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.min.css" rel="stylesheet" />

    <style>
        body {
            padding-top: 70px;
            background-color: #121212;
            color: #f1f1f1;
        }

        .navbar-brand i {
            margin-right: 5px;
        }

        .container {
            color: #e0e0e0;
        }

        .modal-content {
            background-color: #1f1f1f;
            color: #f1f1f1;
        }

        .form-control, .form-select {
            background-color: #2a2a2a;
            color: #f1f1f1;
            border: 1px solid #444;
        }

        .form-control:focus, .form-select:focus {
            background-color: #333;
            color: #fff;
        }

        label.form-label {
            color: #ccc;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004ca1;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #565e64;
            border-color: #4e555b;
        }
    </style>
</head>
<body>

<?php
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<?php
include 'navbar.php';
?>
<!-- Estilo Neón -->
<style>

/* Tabla oscura con encabezado naranjo */
.table-dark thead th {
    background-color: #1a1a1a !important;
    color: orange !important;
    border-color: #444;
}

/* Estilo Select2 oscuro con texto naranjo */
.select2-container .select2-selection--single {
    background-color: #121212 !important;
    border: 1px solid #555 !important;
    color: orange !important;
    height: 38px;
    display: flex;
    align-items: center;
}

.select2-selection__rendered {
    color: orange !important;
    padding-left: 10px;
}

.select2-selection__arrow {
    height: 36px !important;
}

.select2-dropdown {
    background-color: #1a1a1a !important;
    border: 1px solid #444 !important;
    color: orange !important;
}

.select2-results__option {
    background-color: #1a1a1a !important;
    color: orange !important;
}

.select2-results__option--highlighted {
    background-color: #333 !important;
    color: white !important;
}

</style>


<!-- Filtro por empresa -->
<div class="container mb-4">
  <label class="mb-2" style="color: orange;"><i class="fas fa-building"></i> Filtrar por Empresa</label>
  <select id="filtroEmpresa" class="form-select bg-dark border-secondary" style="width: 100%; color: white;">
  <option value="all">Todas las Empresas</option>
    <?php
    $empresas = $conn->query("SELECT DISTINCT e.idempresa, e.nombre_empresa FROM empresa e INNER JOIN historial_cobros hc ON hc.idempresa = e.idempresa WHERE e.eliminado = 0");
    while($emp = $empresas->fetch_assoc()):
    ?>
      <option value="<?= $emp['idempresa'] ?>"><?= $emp['nombre_empresa'] ?></option>
    <?php endwhile; ?>
  </select>
</div>

<!-- Total pagado -->
<div class="container mb-4">
  <h5 style="color: orange;" id="totalPagado">
    Cargando totales...
  </h5>
</div>


<div class="container mt-5 pt-4">
  <h3 class="text-center text-white mt-4">
    <i class="fas fa-money-bill-wave"></i> Historial de Pagos
  </h3>

  <div class="table-responsive mt-4">
    <table class="table table-dark table-bordered table-hover text-white" id="tablaPagos">
      <thead class="bg-dark text-orange">
        <tr>
          <th>Empresa</th>
          <th>Proyecto</th>
          <th>Dominio</th>
          <th>Hosting</th>
          <th>Monto Pagado</th>
          <th>Fecha de Pago</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($pago = $resultado->fetch_assoc()): ?>
          <tr 
            data-empresa="<?= $pago['idempresa'] ?>"
            data-host="<?= $pago['precio_hosting'] ?? 0 ?>"
            data-dominio="<?= $pago['precio_dominio'] ?? 0 ?>"
            data-monto="<?= $pago['monto'] ?>"
          >
            <td><?= htmlspecialchars($pago['nombre_empresa']) ?></td>

            <!-- Proyecto -->
            <td>
              <?= ucfirst($pago['observacion'] ?? 'Proyecto') ?><br>
              <small class="text-info fw-bold">
                $<?= number_format($pago['monto'], 0, ',', '.') ?>
              </small>
            </td>

            <!-- Dominio -->
            <td>
              <?php if (!empty($pago['precio_dominio'])): ?>
                Dominio<br>
                <small class="text-info fw-bold">
                  $<?= number_format($pago['precio_dominio'], 0, ',', '.') ?>
                </small>
              <?php else: ?>
                <span class="text-warning">No aplica</span>
              <?php endif; ?>
            </td>

            <!-- Hosting -->
            <td>
              <?php if (!empty($pago['precio_hosting'])): ?>
                Hosting<br>
                <small class="text-info fw-bold">
                  $<?= number_format($pago['precio_hosting'], 0, ',', '.') ?>
                </small>
              <?php else: ?>
                <span class="text-warning">No aplica</span>
              <?php endif; ?>
            </td>

            <!-- Monto usado por el JS -->
            <td>$<?= number_format($pago['monto'], 0, ',', '.') ?></td>
            <td><?= date('d-m-Y', strtotime($pago['fecha_cobro'])) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>



































    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>

 <!-- Opcional: DataTables -->
<script>
$(document).ready(function() {
    $('#tablaPagos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-CL.json'
        },
        order: [[2, 'desc']]
    });
});
</script>
<script>
$(document).ready(function () {
  $('#filtroEmpresa').select2();

  function formatearCLP(valor) {
    return '$' + valor.toLocaleString('es-CL');
  }

  function actualizarTotales() {
    let empresaSeleccionada = $('#filtroEmpresa').val();
    let total = 0;
    let host = 0;
    let dominio = 0;

    $('#tablaPagos tbody tr').each(function () {
      let idEmpresa = $(this).data('empresa').toString();
      let mostrar = (empresaSeleccionada === "all" || empresaSeleccionada === idEmpresa);
      $(this).toggle(mostrar);

      if (mostrar) {
        let monto = parseFloat($(this).data('monto')) || 0;
        total += monto;

        // Solo para una empresa seleccionada, toma los valores de host/dominio una sola vez
        if (empresaSeleccionada !== "all" && host === 0 && dominio === 0) {
          host = parseFloat($(this).data('host')) || 0;
          dominio = parseFloat($(this).data('dominio')) || 0;
        }
      }
    });

    if (empresaSeleccionada === "all") {
      host = <?= $total_hosting ?>;
      dominio = <?= $total_dominio ?>;
    }

    let neto = total - host - dominio;

    $('#totalPagado').html(
      `${formatearCLP(total)} - Host ${formatearCLP(host)} - Dominio ${formatearCLP(dominio)} = Total ${formatearCLP(neto)} netos`
    );
  }

  actualizarTotales();
  $('#filtroEmpresa').on('change', actualizarTotales);
});
</script>


</body>
</html>
