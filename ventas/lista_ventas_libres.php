<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Lista de Ventas Libres';
include '../includes/header.php';
include '../includes/navbar.php';

// Parámetros de paginación y filtros
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$estado = $_GET['estado'] ?? '';
$buscar = $_GET['buscar'] ?? '';

try {
    $pdo = getConnection();
    
    // Construir consulta con filtros
    $where_conditions = [];
    $params = [];
    
    if (!empty($fecha_inicio)) {
        $where_conditions[] = "DATE(vl.fecha_venta) >= ?";
        $params[] = $fecha_inicio;
    }
    
    if (!empty($fecha_fin)) {
        $where_conditions[] = "DATE(vl.fecha_venta) <= ?";
        $params[] = $fecha_fin;
    }
    
    if (!empty($estado)) {
        $where_conditions[] = "vl.estado = ?";
        $params[] = $estado;
    }
    
    if (!empty($buscar)) {
        $where_conditions[] = "(vl.numero_venta LIKE ? OR vl.motivo_venta LIKE ? OR vl.descripcion LIKE ? OR u.nombre LIKE ?)";
        $search_term = "%$buscar%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Contar total de registros
    $count_sql = "SELECT COUNT(*) FROM ventas_libres vl 
                  LEFT JOIN usuarios u ON vl.usuario_id = u.id 
                  $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);
    
    // Obtener registros de la página actual
    $sql = "SELECT vl.*, u.nombre as vendedor_nombre 
            FROM ventas_libres vl 
            LEFT JOIN usuarios u ON vl.usuario_id = u.id 
            $where_clause 
            ORDER BY vl.fecha_venta DESC 
            LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular estadísticas
    $stats_sql = "SELECT 
                    COUNT(*) as total_ventas,
                    SUM(CASE WHEN estado = 'activa' THEN total ELSE 0 END) as total_ingresos,
                    SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as ventas_activas,
                    SUM(CASE WHEN estado = 'anulada' THEN 1 ELSE 0 END) as ventas_anuladas
                  FROM ventas_libres vl 
                  LEFT JOIN usuarios u ON vl.usuario_id = u.id 
                  $where_clause";
    
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute($params);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = 'Error al obtener las ventas libres: ' . $e->getMessage();
}
?>

<div class="container-fluid mt-4">
    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['total_ventas']; ?></h4>
                            <p class="mb-0">Total Ventas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x"></i>
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
                            <h4 class="mb-0">S/ <?php echo number_format($stats['total_ingresos'], 2); ?></h4>
                            <p class="mb-0">Total Ingresos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
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
                            <h4 class="mb-0"><?php echo $stats['ventas_activas']; ?></h4>
                            <p class="mb-0">Ventas Activas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
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
                            <h4 class="mb-0"><?php echo $stats['ventas_anuladas']; ?></h4>
                            <p class="mb-0">Ventas Anuladas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>
                Filtros de Búsqueda
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="activa" <?php echo $estado === 'activa' ? 'selected' : ''; ?>>Activa</option>
                        <option value="anulada" <?php echo $estado === 'anulada' ? 'selected' : ''; ?>>Anulada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="buscar" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="buscar" name="buscar" 
                           placeholder="Número, motivo, descripción..." value="<?php echo htmlspecialchars($buscar); ?>">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de ventas -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Lista de Ventas Libres
            </h5>
            <a href="venta_libre.php" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>
                Nueva Venta Libre
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($ventas)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron ventas libres</h5>
                    <p class="text-muted">Intente ajustar los filtros de búsqueda o crear una nueva venta libre.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Motivo</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                                <th>Vendedor</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($venta['numero_venta']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                              title="<?php echo htmlspecialchars($venta['motivo_venta']); ?>">
                                            <?php echo htmlspecialchars(substr($venta['motivo_venta'], 0, 50)); ?>
                                            <?php echo strlen($venta['motivo_venta']) > 50 ? '...' : ''; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $venta['cantidad']; ?></td>
                                    <td>
                                        <strong>S/ <?php echo number_format($venta['total'], 2); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($venta['vendedor_nombre']); ?></td>
                                    <td>
                                        <?php if ($venta['estado'] === 'activa'): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Anulada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="verDetalle(<?php echo $venta['id']; ?>)" 
                                                    title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="generar_boleta_libre_pdf.php?id=<?php echo $venta['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               target="_blank" title="Ver boleta">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <?php if ($venta['estado'] === 'activa'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="anularVenta(<?php echo $venta['id']; ?>)" 
                                                        title="Anular venta">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Paginación">
                        <ul class="pagination justify-content-center mt-4">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query($_GET); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($_GET); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query($_GET); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Detalle de Venta Libre
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Ver detalle de venta
function verDetalle(ventaId) {
    fetch(`detalle_venta_libre.php?id=${ventaId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('detalleContent').innerHTML = data;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar el detalle de la venta');
        });
}

// Anular venta
function anularVenta(ventaId) {
    if (confirm('¿Está seguro de que desea anular esta venta libre?')) {
        fetch('anular_venta_libre.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ venta_id: ventaId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Venta anulada exitosamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al anular la venta');
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>