<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$pdo = getConnection();

// Obtener estadísticas para reportes
$stats = getDashboardStats();

// Ventas por mes (últimos 6 meses)
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(fecha, '%Y-%m') as mes,
        COUNT(*) as total_ventas,
        SUM(total) as total_ingresos
    FROM ventas 
    WHERE fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(fecha, '%Y-%m')
    ORDER BY mes DESC
");
$ventas_mensuales = $stmt->fetchAll();

// Productos más vendidos
$stmt = $pdo->query("
    SELECT 
        p.nombre,
        p.codigo,
        SUM(dv.cantidad) as total_vendido,
        SUM(dv.subtotal) as ingresos_producto
    FROM detalle_ventas dv
    JOIN productos p ON dv.producto_id = p.id
    JOIN ventas v ON dv.venta_id = v.id
    WHERE v.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY p.id
    ORDER BY total_vendido DESC
    LIMIT 10
");
$productos_top = $stmt->fetchAll();

// Clientes con más compras
$stmt = $pdo->query("
    SELECT 
        c.nombre,
        c.apellido,
        c.email,
        COUNT(v.id) as total_compras,
        SUM(v.total) as total_gastado
    FROM clientes c
    JOIN ventas v ON c.id = v.cliente_id
    WHERE v.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY c.id
    ORDER BY total_compras DESC
    LIMIT 10
");
$clientes_top = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Librería Belén</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="container mt-4">
                <div class="row">
                    <div class="col-12">
                <h2><i class="fas fa-chart-bar"></i> Reportes y Estadísticas</h2>
                <hr>
            </div>
        </div>

        <!-- Resumen General -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Ventas Hoy</h6>
                                <h4>S/ <?php echo number_format($stats['ventas_hoy'], 2); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-dollar-sign fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Ventas del Mes</h6>
                                <h4>S/ <?php echo number_format($stats['ventas_mes'], 2); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Productos</h6>
                                <h4><?php echo $stats['total_productos']; ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-boxes fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Clientes</h6>
                                <h4><?php echo $stats['total_clientes']; ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventas Mensuales -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar-alt"></i> Ventas por Mes (Últimos 6 meses)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Total Ventas</th>
                                        <th>Ingresos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ventas_mensuales as $venta): ?>
                                    <tr>
                                        <td><?php echo date('F Y', strtotime($venta['mes'] . '-01')); ?></td>
                                        <td><?php echo $venta['total_ventas']; ?></td>
                                        <td>S/ <?php echo number_format($venta['total_ingresos'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos más vendidos -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-trophy"></i> Productos Más Vendidos (Último mes)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Vendidos</th>
                                        <th>Ingresos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productos_top as $producto): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo $producto['codigo']; ?></small>
                                        </td>
                                        <td><?php echo $producto['total_vendido']; ?></td>
                                        <td>S/ <?php echo number_format($producto['ingresos_producto'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mejores clientes -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-star"></i> Mejores Clientes (Último mes)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Compras</th>
                                        <th>Total Gastado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes_top as $cliente): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($cliente['email']); ?></small>
                                        </td>
                                        <td><?php echo $cliente['total_compras']; ?></td>
                                        <td>S/ <?php echo number_format($cliente['total_gastado'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-download"></i> Exportar Reportes</h5>
                    </div>
                    <div class="card-body">
                        <p>Próximamente: Exportación de reportes en PDF y Excel</p>
                        <button class="btn btn-primary" disabled>
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </button>
                        <button class="btn btn-success" disabled>
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>