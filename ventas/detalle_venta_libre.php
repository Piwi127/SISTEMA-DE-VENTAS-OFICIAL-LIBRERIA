<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Usuario no autenticado</div>';
    exit();
}

// Verificar que se proporcione el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="alert alert-danger">ID de venta libre no proporcionado</div>';
    exit();
}

$venta_id = (int)$_GET['id'];

try {
    $pdo = getConnection();
    
    // Obtener datos de la venta libre
    $stmt = $pdo->prepare("
        SELECT vl.*, u.nombre as vendedor_nombre
        FROM ventas_libres vl 
        LEFT JOIN usuarios u ON vl.usuario_id = u.id
        WHERE vl.id = ?
    ");
    $stmt->execute([$venta_id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$venta) {
        echo '<div class="alert alert-danger">Venta libre no encontrada</div>';
        exit();
    }
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error de base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary">Información General</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Número de Venta:</strong></td>
                <td><?php echo htmlspecialchars($venta['numero_venta']); ?></td>
            </tr>
            <tr>
                <td><strong>Fecha:</strong></td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($venta['fecha_venta'])); ?></td>
            </tr>
            <tr>
                <td><strong>Vendedor:</strong></td>
                <td><?php echo htmlspecialchars($venta['vendedor_nombre']); ?></td>
            </tr>
            <tr>
                <td><strong>Estado:</strong></td>
                <td>
                    <?php if ($venta['estado'] === 'activa'): ?>
                        <span class="badge bg-success">Activa</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Anulada</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>Método de Pago:</strong></td>
                <td><?php echo ucfirst($venta['metodo_pago']); ?></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="text-primary">Detalles Financieros</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Cantidad:</strong></td>
                <td><?php echo $venta['cantidad']; ?></td>
            </tr>
            <tr>
                <td><strong>Precio Unitario:</strong></td>
                <td>S/ <?php echo number_format($venta['precio_unitario'], 2); ?></td>
            </tr>
            <tr class="table-success">
                <td><strong>Total:</strong></td>
                <td><strong>S/ <?php echo number_format($venta['total'], 2); ?></strong></td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h6 class="text-primary">Motivo de Venta</h6>
        <div class="border p-3 bg-light rounded">
            <?php echo nl2br(htmlspecialchars($venta['motivo_venta'])); ?>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h6 class="text-primary">Descripción Detallada</h6>
        <div class="border p-3 bg-light rounded">
            <?php echo nl2br(htmlspecialchars($venta['descripcion'])); ?>
        </div>
    </div>
</div>

<?php if (!empty($venta['notas'])): ?>
<div class="row mt-3">
    <div class="col-12">
        <h6 class="text-primary">Notas Adicionales</h6>
        <div class="border p-3 bg-light rounded">
            <?php echo nl2br(htmlspecialchars($venta['notas'])); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-center gap-2">
            <a href="generar_boleta_libre_pdf.php?id=<?php echo $venta['id']; ?>" 
               class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>
                Ver Boleta PDF
            </a>
            <a href="generar_boleta_libre_pdf.php?id=<?php echo $venta['id']; ?>&action=download" 
               class="btn btn-success" target="_blank">
                <i class="fas fa-download me-2"></i>
                Descargar Boleta
            </a>
            <?php if ($venta['estado'] === 'activa'): ?>
                <button type="button" class="btn btn-danger" 
                        onclick="anularVentaDesdeDetalle(<?php echo $venta['id']; ?>)">
                    <i class="fas fa-times me-2"></i>
                    Anular Venta
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function anularVentaDesdeDetalle(ventaId) {
    if (confirm('¿Está seguro de que desea anular esta venta libre?')) {
        fetch('../ventas/anular_venta_libre.php', {
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
                // Cerrar modal y recargar página padre
                bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide();
                window.parent.location.reload();
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