<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Obtener parámetros de filtro
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Obtener clientes
$clientes = getClientes($search);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Sistema de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-book"></i> Librería Belén
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
                </span>
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-fixed pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../ventas/nueva_venta.php">
                                <i class="fas fa-shopping-cart"></i> Nueva Venta
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../ventas/lista_ventas.php">
                                <i class="fas fa-list"></i> Lista de Ventas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../productos/lista_productos.php">
                                <i class="fas fa-book-open"></i> Productos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="lista_clientes.php">
                                <i class="fas fa-users"></i> Clientes
                            </a>
                        </li>
                        <?php if ($user_role == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../reportes/reportes.php">
                                <i class="fas fa-chart-bar"></i> Reportes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../usuarios/lista_usuarios.php">
                                <i class="fas fa-user-cog"></i> Usuarios
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Clientes</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="nuevo_cliente.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Cliente
                        </a>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Búsqueda de Clientes</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="search" class="form-label">Buscar Cliente</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Buscar por nombre, email o teléfono..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                        <a href="lista_clientes.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de clientes -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Clientes Registrados</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($clientes)): ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i> No se encontraron clientes con los filtros aplicados.
                                <br><br>
                                <a href="nuevo_cliente.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Agregar Primer Cliente
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Dirección</th>
                                            <th>Fecha Registro</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <tr>
                                                <td>
                                                    <strong>#<?php echo str_pad($cliente['id'], 4, '0', STR_PAD_LEFT); ?></strong>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong>
                                                            <?php if (!empty($cliente['apellido'])): ?>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($cliente['apellido']); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($cliente['email'])): ?>
                                                        <a href="mailto:<?php echo htmlspecialchars($cliente['email']); ?>" class="text-decoration-none">
                                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($cliente['email']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No registrado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($cliente['telefono'])): ?>
                                                        <a href="tel:<?php echo htmlspecialchars($cliente['telefono']); ?>" class="text-decoration-none">
                                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($cliente['telefono']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No registrado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($cliente['direccion'])): ?>
                                                        <small><?php echo htmlspecialchars(substr($cliente['direccion'], 0, 50)); ?><?php echo strlen($cliente['direccion']) > 50 ? '...' : ''; ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">No registrada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?php echo formatearFecha($cliente['fecha_registro']); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($cliente['activo']): ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="ver_cliente.php?id=<?php echo $cliente['id']; ?>" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           data-bs-toggle="tooltip" title="Ver Detalle">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           data-bs-toggle="tooltip" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="../ventas/nueva_venta.php?cliente=<?php echo $cliente['id']; ?>" 
                                                           class="btn btn-sm btn-outline-success" 
                                                           data-bs-toggle="tooltip" title="Nueva Venta">
                                                            <i class="fas fa-shopping-cart"></i>
                                                        </a>
                                                        <?php if ($user_role == 'admin'): ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-<?php echo $cliente['activo'] ? 'warning' : 'success'; ?>" 
                                                                    onclick="toggleCliente(<?php echo $cliente['id']; ?>, <?php echo $cliente['activo'] ? 'false' : 'true'; ?>)"
                                                                    data-bs-toggle="tooltip" 
                                                                    title="<?php echo $cliente['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                                                <i class="fas fa-<?php echo $cliente['activo'] ? 'user-slash' : 'user-check'; ?>"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Clientes
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count($clientes); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Clientes Activos
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count(array_filter($clientes, function($c) { return $c['activo']; })); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Con Email
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count(array_filter($clientes, function($c) { return !empty($c['email']); })); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Con Teléfono
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count(array_filter($clientes, function($c) { return !empty($c['telefono']); })); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-phone fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Función para activar/desactivar cliente
        function toggleCliente(clienteId, nuevoEstado) {
            const accion = nuevoEstado ? 'activar' : 'desactivar';
            if (confirm(`¿Está seguro de que desea ${accion} este cliente?`)) {
                // Aquí iría la petición AJAX para cambiar el estado
                showAlert(`Funcionalidad de ${accion} cliente en desarrollo`, 'info');
            }
        }
        
        // Permitir buscar con Enter
        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
    
    <style>
        .avatar-sm {
            width: 40px;
            height: 40px;
            font-size: 14px;
        }
    </style>
</body>
</html>