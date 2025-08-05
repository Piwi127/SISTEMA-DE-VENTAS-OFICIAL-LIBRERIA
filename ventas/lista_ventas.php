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
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$cliente_search = isset($_GET['cliente']) ? $_GET['cliente'] : '';

// Obtener ventas
$pdo = getConnection();
$sql = "
    SELECT v.id, v.total, v.fecha, v.estado, c.nombre as cliente_nombre, u.nombre as usuario_nombre
    FROM ventas v 
    LEFT JOIN clientes c ON v.cliente_id = c.id 
    LEFT JOIN usuarios u ON v.usuario_id = u.id 
    WHERE 1=1
";

$params = [];

if (!empty($fecha_inicio)) {
    $sql .= " AND DATE(v.fecha) >= ?";
    $params[] = $fecha_inicio;
}

if (!empty($fecha_fin)) {
    $sql .= " AND DATE(v.fecha) <= ?";
    $params[] = $fecha_fin;
}

if (!empty($cliente_search)) {
    $sql .= " AND c.nombre LIKE ?";
    $params[] = "%$cliente_search%";
}

$sql .= " ORDER BY v.fecha DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Ventas - Sistema de Ventas</title>
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
            <?php include '../includes/sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Lista de Ventas</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="nueva_venta.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva Venta
                        </a>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Filtros de Búsqueda</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                           value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                           value="<?php echo htmlspecialchars($fecha_fin); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="cliente" class="form-label">Cliente</label>
                                    <input type="text" class="form-control" id="cliente" name="cliente" 
                                           placeholder="Buscar por nombre de cliente..." 
                                           value="<?php echo htmlspecialchars($cliente_search); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                        <a href="lista_ventas.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de ventas -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Ventas Registradas</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($ventas)): ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i> No se encontraron ventas con los filtros aplicados.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Total</th>
                                            <th>Fecha</th>
                                            <th>Vendedor</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ventas as $venta): ?>
                                            <tr>
                                                <td>
                                                    <strong>#<?php echo str_pad($venta['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($venta['cliente_nombre'] ?? 'Cliente eliminado'); ?>
                                                </td>
                                                <td>
                                                    <span class="h6 text-success">
                                                        S/ <?php echo number_format($venta['total'], 2); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo formatearFecha($venta['fecha']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($venta['usuario_nombre'] ?? 'Usuario eliminado'); ?>
                                                </td>
                                                <td>
                                                    <?php if ($venta['estado'] === 'cancelada'): ?>
                                                        <span class="badge bg-danger">Cancelada</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Activa</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="detalle_venta.php?id=<?php echo $venta['id']; ?>" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           data-bs-toggle="tooltip" title="Ver Detalle">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="imprimir_venta.php?id=<?php echo $venta['id']; ?>" 
                                                           class="btn btn-sm btn-outline-secondary" 
                                                           data-bs-toggle="tooltip" title="Imprimir" target="_blank">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                        <?php if ($user_role == 'admin' && $venta['estado'] !== 'cancelada'): ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-danger" 
                                                                    data-bs-toggle="tooltip" title="Anular Venta"
                                                                    onclick="anularVenta(<?php echo $venta['id']; ?>)">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <?php 
                                        $ventasActivas = array_filter($ventas, function($venta) { return $venta['estado'] !== 'cancelada'; });
                                        $ventasAnuladas = array_filter($ventas, function($venta) { return $venta['estado'] === 'cancelada'; });
                                        $totalActivas = array_sum(array_column($ventasActivas, 'total'));
                                        $countActivas = count($ventasActivas);
                                        $countAnuladas = count($ventasAnuladas);
                                        ?>
                                        <tr class="table-info">
                                            <td colspan="3"><strong>Total de ventas activas:</strong></td>
                                            <td><strong>S/ <?php echo number_format($totalActivas, 2); ?></strong></td>
                                            <td colspan="3">
                                                <strong>
                                                    <?php echo $countActivas; ?> ventas
                                                    <?php if ($countAnuladas > 0): ?>
                                                        - <?php echo $countAnuladas; ?> ventas anuladas
                                                    <?php endif; ?>
                                                </strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Ventas Hoy
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php
                                            $ventas_hoy = array_filter($ventas, function($v) {
                                                return date('Y-m-d', strtotime($v['fecha'])) == date('Y-m-d') && $v['estado'] !== 'cancelada';
                                            });
                                            echo count($ventas_hoy);
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Activas
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            S/ <?php echo number_format($totalActivas, 2); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Promedio por Venta Activa
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            S/ <?php echo $countActivas > 0 ? number_format($totalActivas / $countActivas, 2) : '0.00'; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
        
        // Función para anular venta (solo admin)
        function anularVenta(ventaId) {
            if (confirm('¿Está seguro de que desea anular esta venta? Esta acción restaurará el stock de los productos y no se puede deshacer.')) {
                // Mostrar indicador de carga
                const button = event.target.closest('button');
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;
                
                // Realizar petición AJAX
                fetch('anular_venta.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'venta_id=' + ventaId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        // Recargar la página después de 2 segundos
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showAlert(data.message, 'error');
                        // Restaurar el botón
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error al procesar la solicitud', 'error');
                    // Restaurar el botón
                    button.innerHTML = originalContent;
                    button.disabled = false;
                });
            }
        }
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>