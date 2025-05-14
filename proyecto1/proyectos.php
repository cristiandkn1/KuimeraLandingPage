<?php
session_start();
if (!isset($_SESSION['idusuario'])) {
    header("Location: login.php");
    exit;
}
include 'conexion.php';
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

<h2 style="margin-left: 100px; margin-top: 40px;">En trabajo⚠️</h2>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>

 
</body>
</html>
