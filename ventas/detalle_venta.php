<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Verificar que se proporcione el ID de la venta
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: lista_ventas.php');
    exit();
}

$venta_id = (int)$_GET['id'];

try {
    // Obtener datos de la venta
    $stmt = $pdo->prepare("
        SELECT v.*, c.nombre as cliente_nombre, c.email as cliente_email, 
               c.telefono as cliente_telefono, c.direccion as cliente_direccion,
               u.nombre as vendedor_nombre
        FROM ventas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id 
        LEFT JOIN usuarios u ON v.usuario_id = u.id
        WHERE v.id = ?
    ");
    $stmt->execute([$venta_id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$venta) {
        header('Location: lista_ventas.php');
        exit();
    }
    
    // Obtener detalles de la venta
    $stmt = $pdo->prepare("
        SELECT dv.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
               p.descripcion as producto_descripcion
        FROM detalle_ventas dv
        JOIN productos p ON dv.producto_id = p.id
        WHERE dv.venta_id = ?
        ORDER BY p.nombre
    ");
    $stmt->execute([$venta_id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

// Calcular totales
$subtotal = $venta['total'] / 1.18; // Quitar IGV
$igv = $venta['total'] - $subtotal;
$total_items = array_sum(array_column($detalles, 'cantidad'));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Venta - Sistema de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-store"></i> Sistema de Ventas
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Dashboard</a>
                <a class="nav-link" href="lista_ventas.php">Ventas</a>
                <a class="nav-link" href="../logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-receipt"></i> Detalle de Venta #<?php echo str_pad($venta['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                    <div>
                        <a href="lista_ventas.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Ventas
                        </a>
                        <a href="imprimir_venta.php?id=<?php echo $venta['id']; ?>" class="btn btn-info" target="_blank">
                            <i class="fas fa-print"></i> Imprimir
                        </a>
                        <a href="generar_boleta_pdf.php?id=<?php echo $venta['id']; ?>" class="btn btn-primary" target="_blank">
                            <i class="fas fa-file-pdf"></i> Boleta PDF
                        </a>
                        <a href="generar_factura_pdf.php?id=<?php echo $venta['id']; ?>" class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-invoice"></i> Factura PDF
                        </a>
                    </div>
                </div>

                <!-- Información de la Venta -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información de la Venta</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>ID de Venta:</strong></td>
                                        <td><?php echo str_pad($venta['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Fecha:</strong></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($venta['fecha'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Vendedor:</strong></td>
                                        <td><?php echo htmlspecialchars($venta['vendedor_nombre']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Items:</strong></td>
                                        <td><span class="badge bg-info"><?php echo $total_items; ?></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-user"></i> Información del Cliente</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nombre:</strong></td>
                                        <td><?php echo htmlspecialchars($venta['cliente_nombre'] ?: 'Cliente General'); ?></td>
                                    </tr>
                                    <?php if ($venta['cliente_email']): ?>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><?php echo htmlspecialchars($venta['cliente_email']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($venta['cliente_telefono']): ?>
                                    <tr>
                                        <td><strong>Teléfono:</strong></td>
                                        <td><?php echo htmlspecialchars($venta['cliente_telefono']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($venta['cliente_direccion']): ?>
                                    <tr>
                                        <td><strong>Dirección:</strong></td>
                                        <td><?php echo htmlspecialchars($venta['cliente_direccion']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos Vendidos -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Productos Vendidos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalles as $detalle): ?>
                                    <tr>
                                        <td>
                                            <code><?php echo htmlspecialchars($detalle['producto_codigo']); ?></code>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($detalle['producto_nombre']); ?></strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($detalle['producto_descripcion'] ?: 'Sin descripción'); ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?php echo $detalle['cantidad']; ?></span>
                                        </td>
                                        <td class="text-end">
                                            <strong>S/ <?php echo number_format($detalle['precio_unitario'], 2); ?></strong>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">S/ <?php echo number_format($detalle['subtotal'], 2); ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Totales -->
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fas fa-calculator"></i> Resumen de Totales</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-end">S/ <?php echo number_format($subtotal, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>IGV (18%):</strong></td>
                                        <td class="text-end">S/ <?php echo number_format($igv, 2); ?></td>
                                    </tr>
                                    <tr class="table-success">
                                        <td><strong>TOTAL A PAGAR:</strong></td>
                                        <td class="text-end"><strong class="fs-5">S/ <?php echo number_format($venta['total'], 2); ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Adicionales -->
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-tools"></i> Acciones Disponibles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="imprimir_venta.php?id=<?php echo $venta['id']; ?>" class="btn btn-info w-100 mb-2" target="_blank">
                                    <i class="fas fa-print"></i><br>
                                    <small>Imprimir Boleta</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="generar_boleta_pdf.php?id=<?php echo $venta['id']; ?>" class="btn btn-primary w-100 mb-2" target="_blank">
                                    <i class="fas fa-file-pdf"></i><br>
                                    <small>Descargar Boleta PDF</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="generar_factura_pdf.php?id=<?php echo $venta['id']; ?>" class="btn btn-danger w-100 mb-2" target="_blank">
                                    <i class="fas fa-file-invoice"></i><br>
                                    <small>Descargar Factura PDF</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="lista_ventas.php" class="btn btn-secondary w-100 mb-2">
                                    <i class="fas fa-list"></i><br>
                                    <small>Lista de Ventas</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>