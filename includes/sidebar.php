<?php
// Sidebar centralizado para todo el sistema
// Determinar la ruta base según la ubicación del archivo actual
$current_dir = dirname($_SERVER['PHP_SELF']);
$base_path = '';

// Ajustar rutas según el directorio actual
if (strpos($current_dir, '/ventas') !== false || 
    strpos($current_dir, '/productos') !== false || 
    strpos($current_dir, '/clientes') !== false || 
    strpos($current_dir, '/usuarios') !== false || 
    strpos($current_dir, '/reportes') !== false || 
    strpos($current_dir, '/notas') !== false) {
    $base_path = '../';
}

// Determinar qué enlace está activo
$current_page = basename($_SERVER['PHP_SELF']);
$current_section = basename(dirname($_SERVER['PHP_SELF']));
?>

<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
    <div class="position-fixed pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'index.php' && $current_section != 'ventas' && $current_section != 'productos' && $current_section != 'clientes' && $current_section != 'usuarios' && $current_section != 'reportes' && $current_section != 'notas') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>index.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'nueva_venta.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>ventas/nueva_venta.php">
                    <i class="fas fa-plus-circle"></i> Nueva Venta
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'lista_ventas.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>ventas/lista_ventas.php">
                    <i class="fas fa-list"></i> Lista de Ventas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'venta_libre.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>ventas/venta_libre.php">
                    <i class="fas fa-hand-holding-usd"></i> Nueva Venta Libre
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'lista_ventas_libres.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>ventas/lista_ventas_libres.php">
                    <i class="fas fa-clipboard-list"></i> Lista Ventas Libres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'lista_productos.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>productos/lista_productos.php">
                    <i class="fas fa-book-open"></i> Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'lista_clientes.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>clientes/lista_clientes.php">
                    <i class="fas fa-users"></i> Clientes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'lista_notas.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>notas/lista_notas.php">
                    <i class="fas fa-sticky-note"></i> Notas
                </a>
            </li>
            <?php if (isset($user_role) && $user_role == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'reportes.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>reportes/reportes.php">
                    <i class="fas fa-chart-bar"></i> Reportes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'lista_usuarios.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>usuarios/lista_usuarios.php">
                    <i class="fas fa-user-cog"></i> Usuarios
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>