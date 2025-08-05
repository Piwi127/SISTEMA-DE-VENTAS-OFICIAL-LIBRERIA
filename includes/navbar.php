<?php
// This is a placeholder for the navigation bar content.
// You can add your HTML for the navbar here.
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Sistema de Ventas</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="../index.php">Inicio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../productos/lista_productos.php">Productos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../clientes/lista_clientes.php">Clientes</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="ventasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Ventas
                </a>
                <ul class="dropdown-menu" aria-labelledby="ventasDropdown">
                    <li><a class="dropdown-item" href="../ventas/nueva_venta.php"><i class="fas fa-plus me-2"></i>Nueva Venta</a></li>
                    <li><a class="dropdown-item" href="../ventas/lista_ventas.php"><i class="fas fa-list me-2"></i>Lista de Ventas</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../ventas/venta_libre.php"><i class="fas fa-cash-register me-2"></i>Nueva Venta Libre</a></li>
                    <li><a class="dropdown-item" href="../ventas/lista_ventas_libres.php"><i class="fas fa-clipboard-list me-2"></i>Lista Ventas Libres</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../usuarios/lista_usuarios.php">Usuarios</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../reportes/reportes.php">Reportes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../notas/lista_notas.php">Notas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">Cerrar Sesión</a>
            </li>
        </ul>
    </div>
</nav>