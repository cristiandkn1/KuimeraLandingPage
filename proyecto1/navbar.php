<?php
$paginaActual = basename($_SERVER['PHP_SELF']); // Detecta la página activa
?>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fas fa-building"></i> Empresas</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $paginaActual === 'index.php' ? 'active-neon' : '' ?>" href="index.php">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaActual === 'proyectos.php' ? 'active-neon' : '' ?>" href="proyectos.php">
                        <i class="fas fa-briefcase"></i> Proyectos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaActual === 'pagos.php' ? 'active-neon' : '' ?>" href="pagos.php">
                        <i class="fas fa-money-bill-wave"></i> Pagos
                    </a>
                </li>
            </ul>

            <!-- Usuario y botón de logout -->
            <span class="navbar-text text-white me-3">
                <i class="fas fa-user-circle"></i> <?= $_SESSION['correo'] ?? 'Admin' ?>
            </span>
            <a href="logout.php" class="btn btn-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </div>
</nav>

<!-- Estilo Neón -->
<style>
.nav-link.active-neon {
    color: orange !important;
    font-weight: bold;
    text-shadow: 0 0 2px orange, 0 0 4px orange, 0 0 6px orange;
}
</style>
