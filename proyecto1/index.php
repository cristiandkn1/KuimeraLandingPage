<?php
session_start();
if (!isset($_SESSION['idusuario'])) {
    header("Location: login.php");
    exit;
}
include 'conexion.php';
$hostings_res = $conn->query("SELECT idhosting, nombre, precio, duracion FROM hosting WHERE eliminado = 0 ORDER BY nombre");
$dominios_res = $conn->query("SELECT iddominio, nombre, precio, duracion FROM dominio WHERE eliminado = 0 ORDER BY nombre");

$hostings = [];
while ($h = $hostings_res->fetch_assoc()) {
    $hostings[] = $h;
}

$dominios = [];
while ($d = $dominios_res->fetch_assoc()) {
    $dominios[] = $d;
}



// Consulta para el dashboard
$sql = "
SELECT 
    e.idempresa, 
    e.nombre_empresa, 
    e.nombre_encargado, 
    e.numero,
    e.proyecto_vendido, 
    e.precio_proyecto,
    DATEDIFF(DATE_ADD(e.fecha_venta, INTERVAL e.dias_restantes DAY), CURDATE()) AS dias_restantes,
    e.fecha_venta,

    -- Hosting
    h.idhosting, 
    h.nombre AS nombre_hosting, 
    h.precio AS precio_hosting, 
    h.duracion AS duracion_hosting,
    DATEDIFF(DATE_ADD(ehd.fecha_inicio_hosting, INTERVAL h.duracion YEAR), CURDATE()) AS dias_restantes_hosting,

    -- Dominio
    d.iddominio, 
    d.nombre AS nombre_dominio, 
    d.precio AS precio_dominio, 
    d.duracion AS duracion_dominio,
    DATEDIFF(DATE_ADD(ehd.fecha_inicio_dominio, INTERVAL d.duracion YEAR), CURDATE()) AS dias_restantes_dominio

FROM empresa e
LEFT JOIN empresa_hosting_dominio ehd ON e.idempresa = ehd.idempresa
LEFT JOIN hosting h ON ehd.idhosting = h.idhosting
LEFT JOIN dominio d ON ehd.iddominio = d.iddominio
WHERE e.eliminado = 0
ORDER BY e.fecha_venta DESC;
";

$resultado = $conn->query($sql);
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

<div class="text-center my-4 d-flex flex-wrap justify-content-center gap-2">
  <!-- Botón Agregar Empresa -->
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarEmpresa">
    <i class="fas fa-plus"></i> Agregar Empresa
  </button>
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalDominios">
    <i class="fas fa-globe"></i> Dominios
  </button>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalHostings">
    <i class="fas fa-server"></i> Hostings
  </button>
</div>






















<h4 class="text-center mt-5">Empresas Registradas</h4>
<div class="d-flex justify-content-center mt-3">
    <div class="table-responsive" style="max-width:1600px;width:100%;padding:0 15px;">
        <table class="table table-dark table-bordered table-hover" id="tablaEmpresas">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Encargado</th>
                    <th>Número</th>
                    <th>Proyecto</th>
                    <th>Precio Proyecto</th>
                    <th>Días Restantes</th>
                    <th>Fecha Venta</th>
                    <th>Hosting</th>
                    <th>Dominio</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $resultado->fetch_assoc()): ?>
                <?php
                    $dias_empresa = $row['dias_restantes'];
                    $dias_hosting = $row['dias_restantes_hosting'];
                    $dias_dominio = $row['dias_restantes_dominio'];

                    $clase_fila = ($dias_empresa <= 0) ? 'table-danger' : (($dias_empresa <= 3) ? 'table-warning' : '');

                    $texto_estado = ($dias_empresa <= 0)
                        ? '<span class="text-danger fw-bold">VENCIDO</span>'
                        : (($dias_empresa <= 3) ? '<span class="text-warning fw-bold">Por vencer</span>' : $dias_empresa . ' días');

                    $numeroWsp = preg_replace('/\D/', '', $row['numero']);
                    $numeroWsp = (str_starts_with($numeroWsp, '56')) ? $numeroWsp : '56' . ltrim($numeroWsp, '0');

                    $mensaje = urlencode("Hola {$row['nombre_encargado']}, tu servicio '{$row['proyecto_vendido']}' está por vencer o ha vencido.");
                ?>
                <tr class="<?= $clase_fila ?>">
<td>
  <?= htmlspecialchars($row['nombre_empresa']) ?>
  <button 
    type="button"
    class="btn btn-sm btn-info ms-2 btnVerInfoEmpresa"
    data-id="<?= $row['idempresa'] ?>"
    data-bs-toggle="modal"
    data-bs-target="#modalInfoEmpresa"
  >
    <i class="fas fa-info-circle"></i>
  </button>
</td>
                    <td><?= $row['nombre_encargado'] ?></td>
                    <td><?= $row['numero'] ?></td>
                    <td><?= ucfirst($row['proyecto_vendido']) ?></td>
                    <td>$<?= number_format($row['precio_proyecto'], 0, ',', '.') ?></td>
<td>
  <?= $texto_estado ?><br>
  <?php if ($dias_empresa > 0): ?>
    <small class="text-success">Días Restantes Para El Cobro</small>
  <?php else: ?>
  <small class="text-danger">ya vencido, debe cobrarse</small>
<?php endif; ?>
</td>                    <td><?= date('d-m-Y', strtotime($row['fecha_venta'])) ?></td>
                    <td>
                        <?php if ($row['nombre_hosting']): ?>
                            <?= $row['nombre_hosting'] ?><br>
                            <small class="<?= ($dias_hosting <= 0) ? 'text-danger' : 'text-info' ?>">
                                <?= $dias_hosting ?> días - $<?= number_format($row['precio_hosting'],0,',','.') ?>
                            </small>
                        <?php else: ?>
                            <span class="text-warning">Sin asignar</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['nombre_dominio']): ?>
                            <?= $row['nombre_dominio'] ?><br>
                            <small class="<?= ($dias_dominio <= 0) ? 'text-danger' : 'text-info' ?>">
                                <?= $dias_dominio ?> días - $<?= number_format($row['precio_dominio'],0,',','.') ?>
                            </small>
                        <?php else: ?>
                            <span class="text-warning">Sin asignar</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <!-- Editar -->
                        <button class="btn btn-sm btn-warning btnEditarEmpresa"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditarEmpresa"
                            data-id="<?= $row['idempresa'] ?>"
                            data-nombre_empresa="<?= htmlspecialchars($row['nombre_empresa']) ?>"
                            data-nombre_encargado="<?= htmlspecialchars($row['nombre_encargado']) ?>"
                            data-numero="<?= $row['numero'] ?>"
                            data-proyecto_vendido="<?= $row['proyecto_vendido'] ?>"
                            data-precio="<?= $row['precio_proyecto'] ?>"
                            data-dias="<?= $row['dias_restantes'] ?>"
                            data-fecha="<?= $row['fecha_venta'] ?>"
                            data-idhosting="<?= $row['idhosting'] ?? '' ?>"
                            data-iddominio="<?= $row['iddominio'] ?? '' ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>

                        <!-- Cobro Proyecto -->
                        <?php if ($dias_empresa <= 0): ?>
                        <form class="mt-2" action="marcar_cobro.php" method="POST">
                            <input type="hidden" name="idempresa" value="<?= $row['idempresa'] ?>">
                            <input type="hidden" name="tipo_cobro" value="proyecto">
                            <button class="btn btn-info btn-sm" onclick="return confirmarCobro('Proyecto mensual');">
                                <i class="fas fa-check-circle"></i> Cobrar Proyecto
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Cobro Hosting -->
                        <?php if ($dias_hosting <= 0 && $row['nombre_hosting']): ?>
                        <form class="mt-2" action="marcar_cobro.php" method="POST">
                            <input type="hidden" name="idempresa" value="<?= $row['idempresa'] ?>">
                            <input type="hidden" name="tipo_cobro" value="hosting">
                            <button class="btn btn-primary btn-sm" onclick="return confirmarCobro('Hosting anual');">
                                <i class="fas fa-server"></i> Cobrar 
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Cobro Dominio -->
                        <?php if ($dias_dominio <= 0 && $row['nombre_dominio']): ?>
                        <form class="mt-2" action="marcar_cobro.php" method="POST">
                            <input type="hidden" name="idempresa" value="<?= $row['idempresa'] ?>">
                            <input type="hidden" name="tipo_cobro" value="dominio">
                            <button class="btn btn-success btn-sm" onclick="return confirmarCobro('Dominio anual');">
                                <i class="fas fa-globe"></i> Cobrar
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Avisar WhatsApp -->
                        <?php if ($dias_empresa <= 0 || $dias_hosting <= 0 || $dias_dominio <= 0): ?>
                        <a class="btn btn-sm btn-success mt-2" href="https://wa.me/<?= $numeroWsp ?>?text=<?= $mensaje ?>" target="_blank">
                            <i class="fab fa-whatsapp"></i> Avisar
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>





























<div class="modal fade" id="modalDominios" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-globe"></i> Dominios</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Tabla Dominios -->
        <table class="table table-dark table-bordered table-hover text-white" id="tablaDominios">
  <thead class="table-secondary text-dark">
            <tr>
              <th>Nombre</th>
              <th>Precio</th>
              <th>Duración (años)</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $dominios = $conn->query("SELECT * FROM dominio WHERE eliminado = 0");
            while ($dom = $dominios->fetch_assoc()):
            ?>
            <tr>
              <td><?= $dom['nombre'] ?></td>
              <td>$<?= number_format($dom['precio'], 0, ',', '.') ?></td>
              <td><?= $dom['duracion'] ?></td>
              <td>
                <button class="btn btn-warning btn-sm btnEditarDominio"
                        data-id="<?= $dom['iddominio'] ?>"
                        data-nombre="<?= $dom['nombre'] ?>"
                        data-precio="<?= $dom['precio'] ?>"
                        data-duracion="<?= $dom['duracion'] ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditarDominio">
                  <i class="fas fa-edit"></i>
                </button>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>

        <!-- Botón para agregar nuevo dominio -->
        <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#modalAgregarDominio">
          <i class="fas fa-plus"></i> Nuevo Dominio
        </button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modalHostings" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-server"></i> Hostings</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Tabla Hostings -->
        <table class="table table-dark table-bordered table-hover text-white" id="tablaHostings">
  <thead class="table-secondary text-dark">
    <tr>
      <th>Nombre</th>
      <th>Precio</th>
      <th style="width: 70px;">Duración (años)</th>
      <th>GB</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $hostings = $conn->query("SELECT * FROM hosting WHERE eliminado = 0");
    while ($host = $hostings->fetch_assoc()):
    ?>
    <tr>
      <td><?= $host['nombre'] ?></td>
      <td>$<?= number_format($host['precio'], 0, ',', '.') ?></td>
      <td><?= $host['duracion'] ?></td>
      <td><?= $host['gb'] ?> GB</td>
      <td>
        <button class="btn btn-warning btn-sm btnEditarHosting"
                data-id="<?= $host['idhosting'] ?>"
                data-nombre="<?= $host['nombre'] ?>"
                data-precio="<?= $host['precio'] ?>"
                data-duracion="<?= $host['duracion'] ?>"
                data-gb="<?= $host['gb'] ?>"
                data-bs-toggle="modal"
                data-bs-target="#modalEditarHosting">
          <i class="fas fa-edit"></i>
        </button>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>


        <!-- Botón para agregar nuevo hosting -->
        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalAgregarHosting">
          <i class="fas fa-plus"></i> Nuevo Hosting
        </button>
      </div>
    </div>
  </div>
</div>





<!-- Modal Agregar Empresa -->
<div class="modal fade" id="modalAgregarEmpresa" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="/proyecto1/agregar_empresa.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel"><i class="fas fa-building"></i> Agregar Empresa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Nombre Empresa</label>
          <input type="text" name="nombre_empresa" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Nombre Encargado</label>
          <input type="text" name="nombre_encargado" class="form-control" required>
        </div>
        <div class="mb-2">
        <label class="form-label">Número de Contacto</label>
        <input type="text" name="numero" id="numero" class="form-control" required placeholder="+56 9 9996 1702">
        </div>

         <!-- Nuevo campo de correo -->
        <div class="mb-2">
          <label class="form-label">Correo Electrónico</label>
          <input type="email" name="correo" class="form-control" placeholder="empresa@correo.com" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Proyecto Vendido</label>
          <select name="proyecto_vendido" class="form-select" required>
            <option value="">Seleccione...</option>
            <option value="Smartventas">Smartventas</option>
            <option value="MTSys">MTSys</option>
            <option value="GymManager">GymManager</option>
            </select>
        </div>
        <div class="mb-2">
        <label class="form-label">Hosting Asociado</label>
        <select name="idhosting" class="form-select" required>
    <option value="">Seleccione un hosting...</option>
    <?php foreach ($hostings as $h): ?>
    <option value="<?= $h['idhosting'] ?>">
        <?= $h['nombre'] ?> - $<?= number_format($h['precio'], 0, ',', '.') ?> CLP - <?= $h['duracion'] ?> año(s)
    </option>
    <?php endforeach; ?>
</select>
        </div>

        <div class="mb-2">
  <label class="form-label">Dominio Asociado</label>
  <select name="iddominio" class="form-select" required>
  <option value="">Seleccione un dominio...</option>
  <?php foreach ($dominios as $d): ?>
    <option value="<?= $d['iddominio'] ?>">
      <?= $d['nombre'] ?> - $<?= number_format($d['precio'], 0, ',', '.') ?> CLP - <?= $d['duracion'] ?> año(s)
    </option>
  <?php endforeach; ?>
</select>
</div>
        <div class="mb-2">
          <label class="form-label">Precio Proyecto (CLP)</label>
          <input type="number" name="precio_proyecto" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Días Restantes</label>
          <input type="number" name="dias_restantes" class="form-control" value="30" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Fecha Venta</label>
          <input type="date" name="fecha_venta" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Empresa</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>



<!-- Modal Editar Empresa -->
<div class="modal fade" id="modalEditarEmpresa" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="editar_empresa.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarLabel"><i class="fas fa-edit"></i> Editar Empresa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="idempresa" id="edit_idempresa">
        
        <div class="mb-2">
          <label class="form-label">Nombre Empresa</label>
          <input type="text" name="nombre_empresa" id="edit_nombre_empresa" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Nombre Encargado</label>
          <input type="text" name="nombre_encargado" id="edit_nombre_encargado" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Número de Contacto</label>
          <input type="text" name="numero" id="edit_numero" class="form-control" required>
        </div>

         <div class="mb-2">
          <label class="form-label">Correo Electrónico</label>
          <input type="email" name="correo" id="edit_correo" class="form-control" required>
        </div>
        
        <!-- Proyecto Vendido -->
        <div class="mb-2">
          <label class="form-label">Proyecto Vendido</label>
          <select name="proyecto_vendido" id="edit_proyecto_vendido" class="form-select" required>
            <option value="">Seleccione...</option>
            <option value="Smartventas">Smartventas</option>
            <option value="MTSys">MTSys</option>
            <option value="GymManager">GymManager</option>
          </select>
        </div>
        
        <!-- Hosting Asociado -->
        <div class="mb-2">
          <label class="form-label">Hosting Asociado</label>
          <select name="idhosting" id="edit_idhosting" class="form-select" required>
            <option value="">Seleccione un hosting...</option>
            <?php foreach ($hostings as $h): ?>
              <option value="<?= $h['idhosting'] ?>">
                <?= $h['nombre'] ?> - $<?= number_format($h['precio'], 0, ',', '.') ?> CLP - <?= $h['duracion'] ?> año(s)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Dominio Asociado -->
        <div class="mb-2">
          <label class="form-label">Dominio Asociado</label>
          <select name="iddominio" id="edit_iddominio" class="form-select" required>
            <option value="">Seleccione un dominio...</option>
            <?php foreach ($dominios as $d): ?>
              <option value="<?= $d['iddominio'] ?>">
                <?= $d['nombre'] ?> - $<?= number_format($d['precio'], 0, ',', '.') ?> CLP - <?= $d['duracion'] ?> año(s)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Precio Proyecto (CLP)</label>
          <input type="number" name="precio_proyecto" id="edit_precio" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Días Restantes</label>
          <input type="number" name="dias_restantes" id="edit_dias" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Fecha Venta</label>
          <input type="date" name="fecha_venta" id="edit_fecha" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-danger" id="btnEliminarEmpresa">
          <i class="fas fa-trash"></i> Eliminar
        </button>

        <div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Empresa</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalAgregarHosting" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="/proyecto1/agregar_hosting.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-server"></i> Agregar Hosting</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Nombre del Hosting</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Precio (CLP)</label>
          <input type="number" name="precio" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Duración (años)</label>
          <input type="number" name="duracion" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Capacidad (GB)</label>
          <input type="number" name="gb" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>
<div class="modal fade" id="modalEditarHosting" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="editar_hosting.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-server"></i> Editar Hosting</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="idhosting" id="edit_idhosting">
        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" id="edit_nombre_hosting" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Precio</label>
          <input type="number" name="precio" id="edit_precio_hosting" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Duración (años)</label>
          <input type="number" name="duracion" id="edit_duracion_hosting" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Capacidad (GB)</label>
          <input type="number" name="gb" id="edit_gb_hosting" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-danger" id="btnEliminarHosting"><i class="fas fa-trash"></i> Eliminar</button>
        <div>
          <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="modal fade" id="modalEditarDominio" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="editar_dominio.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-globe"></i> Editar Dominio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="iddominio" id="edit_iddominio">
        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" id="edit_nombre_dominio" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Precio</label>
          <input type="number" name="precio" id="edit_precio_dominio" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Duración (años)</label>
          <input type="number" name="duracion" id="edit_duracion_dominio" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-danger" id="btnEliminarDominio"><i class="fas fa-trash"></i> Eliminar</button>
        <div>
          <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>




<style>
  #modalInfoEmpresa .list-group-item {
    background-color: #212529; /* gris oscuro */
    border-color: #343a40;
    color: #fff;
  }
  #modalInfoEmpresa .modal-body,
#modalInfoEmpresa .list-group-item {
  color: #ffa726;
}

</style>
<!-- Modal Información Empresa - Modo Noche -->
<div class="modal fade" id="modalInfoEmpresa" tabindex="-1" aria-labelledby="modalInfoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title" id="modalInfoLabel"><i class="fas fa-building me-2"></i> Información de la Empresa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="contenidoInfoEmpresa">
        <div class="text-center text-muted">Cargando datos...</div>
      </div>
    </div>
  </div>
</div>











    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>

    <script>
  document.querySelectorAll('.btnEditarEmpresa').forEach(btn => {
    btn.addEventListener('click', () => {
      // Asignar los valores de los campos al modal
      document.getElementById('edit_idempresa').value = btn.dataset.id;
      document.getElementById('edit_nombre_empresa').value = btn.dataset.nombre_empresa;
      document.getElementById('edit_nombre_encargado').value = btn.dataset.nombre_encargado;
      document.getElementById('edit_numero').value = btn.dataset.numero;
      document.getElementById('edit_precio').value = btn.dataset.precio;
      document.getElementById('edit_dias').value = btn.dataset.dias;
      document.getElementById('edit_fecha').value = btn.dataset.fecha;

      // Asignar los valores de los selects (Proyecto Vendido, Hosting, Dominio)
      document.getElementById('edit_proyecto_vendido').value = btn.dataset.proyecto_vendido;
      document.getElementById('edit_idhosting').value = btn.dataset.idhosting;
      document.getElementById('edit_iddominio').value = btn.dataset.iddominio;
    });
  });
</script>



    <script>
$(document).ready(function() {
    $('#tablaEmpresas').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'

        }
    });
});
</script>
<script>
document.getElementById("numero").addEventListener("input", function () {
    let valor = this.value.replace(/\D/g, ""); // quitar no numéricos

    // Limpiar +569 y otros inicios no deseados
    if (valor.startsWith("569")) {
        valor = valor.substring(3);
    } else if (valor.startsWith("09")) {
        valor = valor.substring(1);
    }

    // Formato: +56 9 XXXX XXXX
    if (valor.length > 8) valor = valor.substring(0, 8);

    let formateado = "+56 9 ";

    if (valor.length >= 4) {
        formateado += valor.substring(0, 4) + " " + valor.substring(4);
    } else {
        formateado += valor;
    }

    this.value = formateado.trim();
});
</script>
<script>
  document.getElementById('btnEliminarEmpresa').addEventListener('click', function () {
    const id = document.getElementById('edit_idempresa').value;

    Swal.fire({
      title: '¿Eliminar empresa?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d33'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = 'eliminar_empresa.php?id=' + id;
      }
    });
  });
</script>
<script>
  // Hosting
  document.querySelectorAll('.btnEditarHosting').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('edit_idhosting').value = btn.dataset.id;
      document.getElementById('edit_nombre_hosting').value = btn.dataset.nombre;
      document.getElementById('edit_precio_hosting').value = btn.dataset.precio;
      document.getElementById('edit_duracion_hosting').value = btn.dataset.duracion;
      document.getElementById('edit_gb_hosting').value = btn.dataset.gb;

    });
  });

  document.getElementById('btnEliminarHosting').addEventListener('click', () => {
    const id = document.getElementById('edit_idhosting').value;
    if (confirm("¿Deseas eliminar este hosting?")) {
      window.location.href = 'eliminar_hosting.php?id=' + id;
    }
  });

  // Dominio
  document.querySelectorAll('.btnEditarDominio').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('edit_iddominio').value = btn.dataset.id;
      document.getElementById('edit_nombre_dominio').value = btn.dataset.nombre;
      document.getElementById('edit_precio_dominio').value = btn.dataset.precio;
      document.getElementById('edit_duracion_dominio').value = btn.dataset.duracion;
    });
  });

  document.getElementById('btnEliminarDominio').addEventListener('click', () => {
    const id = document.getElementById('edit_iddominio').value;
    if (confirm("¿Deseas eliminar este dominio?")) {
      window.location.href = 'eliminar_dominio.php?id=' + id;
    }
  });
</script>


<script>
function confirmarCobro(tipo) {
    return Swal.fire({
        title: 'Confirmar cobro',
        text: '¿Estás seguro que deseas realizar el cobro de ' + tipo + '?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cobrar',
        cancelButtonText: 'Cancelar'
    }).then(result => result.isConfirmed);
}
</script>
<?php if (isset($_GET['cobro']) && $_GET['cobro'] === 'ok'): ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Cobro registrado',
      text: '<?= htmlspecialchars($_GET['mensaje']) ?>',
      timer: 2500,
      showConfirmButton: false
    });
  </script>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btnVerInfoEmpresa').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;

      const contenedor = document.getElementById('contenidoInfoEmpresa');
      contenedor.innerHTML = '<div class="text-center text-muted">Cargando datos...</div>';

      fetch('datos_empresa.php?id=' + id)
        .then(res => res.text())
        .then(html => {
          contenedor.innerHTML = html;
        })
        .catch(err => {
          contenedor.innerHTML = '<div class="text-danger">Error al cargar los datos</div>';
          console.error(err);
        });
    });
  });
});
</script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.querySelector('[data-bs-target="#modalAgregarEmpresa"]');
    if (btn) {
      btn.addEventListener('click', () => {
        console.log("🟢 Se hizo clic en el botón de abrir modal");
      });
    } else {
      console.log("🔴 No se encontró el botón con data-bs-target");
    }
  });
</script>


</body>
</html>
