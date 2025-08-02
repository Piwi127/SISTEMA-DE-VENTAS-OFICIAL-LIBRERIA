<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Verificar que se recibió el ID de venta
if (!isset($_POST['venta_id']) || empty($_POST['venta_id'])) {
    die('ID de venta no válido');
}

$venta_id = (int)$_POST['venta_id'];

try {
    $pdo = getConnection();
    
    // Obtener datos de la venta
    $stmt = $pdo->prepare("
        SELECT v.*, c.nombre as cliente_nombre, c.email as cliente_email, 
               c.telefono as cliente_telefono, c.direccion as cliente_direccion,
               u.nombre as vendedor_nombre
        FROM ventas v 
        JOIN clientes c ON v.cliente_id = c.id 
        JOIN usuarios u ON v.usuario_id = u.id
        WHERE v.id = ?
    ");
    $stmt->execute([$venta_id]);
    $venta = $stmt->fetch();
    
    if (!$venta) {
        die('Venta no encontrada');
    }
    
    // Obtener detalles de la venta
    $stmt = $pdo->prepare("
        SELECT dv.*, p.nombre as producto_nombre, p.codigo as producto_codigo
        FROM detalle_ventas dv 
        JOIN productos p ON dv.producto_id = p.id 
        WHERE dv.venta_id = ?
        ORDER BY p.nombre
    ");
    $stmt->execute([$venta_id]);
    $detalles = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die('Error en la base de datos: ' . $e->getMessage());
}

// Configurar headers para PDF
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Venta - <?php echo str_pad($venta['id'], 8, '0', STR_PAD_LEFT); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .document-type {
            font-size: 18px;
            font-weight: bold;
            background-color: #3498db;
            color: white;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-title {
            font-weight: bold;
            background-color: #ecf0f1;
            padding: 8px;
            border-left: 4px solid #3498db;
            margin-bottom: 10px;
        }
        .info-content {
            padding: 0 10px;
        }
        .two-column {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .column {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 2px solid #3498db;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .total-final {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            border-top: 2px solid #3498db;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">LIBRERÍA BELÉN</div>
        <div>RUC: 20123456789</div>
        <div>Dirección: Av. Principal 123, Lima, Perú</div>
        <div>Teléfono: (01) 234-5678 | Email: info@libreriabelen.com</div>
        <div class="document-type">BOLETA DE VENTA ELECTRÓNICA</div>
        <div><strong>N° <?php echo str_pad($venta['id'], 8, '0', STR_PAD_LEFT); ?></strong></div>
    </div>

    <div class="two-column">
        <div class="column">
            <div class="info-section">
                <div class="info-title">DATOS DEL CLIENTE</div>
                <div class="info-content">
                    <strong>Nombre:</strong> <?php echo htmlspecialchars($venta['cliente_nombre']); ?><br>
                    <?php if ($venta['cliente_email']): ?>
                    <strong>Email:</strong> <?php echo htmlspecialchars($venta['cliente_email']); ?><br>
                    <?php endif; ?>
                    <?php if ($venta['cliente_telefono']): ?>
                    <strong>Teléfono:</strong> <?php echo htmlspecialchars($venta['cliente_telefono']); ?><br>
                    <?php endif; ?>
                    <?php if ($venta['cliente_direccion']): ?>
                    <strong>Dirección:</strong> <?php echo htmlspecialchars($venta['cliente_direccion']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="column">
            <div class="info-section">
                <div class="info-title">DATOS DE LA VENTA</div>
                <div class="info-content">
                    <strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s', strtotime($venta['fecha_venta'])); ?><br>
                    <strong>Vendedor:</strong> <?php echo htmlspecialchars($venta['vendedor_nombre']); ?><br>
                    <strong>Moneda:</strong> Soles (PEN)<br>
                    <strong>Tipo:</strong> Boleta de Venta
                </div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-title">DETALLE DE PRODUCTOS</div>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal_total = 0;
                foreach ($detalles as $detalle): 
                    $subtotal_total += $detalle['subtotal'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['producto_codigo']); ?></td>
                    <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                    <td class="text-center"><?php echo $detalle['cantidad']; ?></td>
                    <td class="text-right">S/ <?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                    <td class="text-right">S/ <?php echo number_format($detalle['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="total-section">
        <div class="info-title">RESUMEN DE TOTALES</div>
        <?php 
        $subtotal_sin_igv = $venta['total'] / 1.18;
        $igv_monto = $venta['total'] - $subtotal_sin_igv;
        ?>
        <div class="total-row">
            <span>Subtotal (Base Imponible):</span>
            <span>S/ <?php echo number_format($subtotal_sin_igv, 2); ?></span>
        </div>
        <div class="total-row">
            <span>IGV (18%):</span>
            <span>S/ <?php echo number_format($igv_monto, 2); ?></span>
        </div>
        <div class="total-row total-final">
            <span>TOTAL A PAGAR:</span>
            <span>S/ <?php echo number_format($venta['total'], 2); ?></span>
        </div>
    </div>

    <div class="footer">
        <p><strong>¡Gracias por su compra!</strong></p>
        <p>Esta boleta ha sido generada electrónicamente y es válida sin firma ni sello.</p>
        <p>Documento generado el <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>Sistema de Ventas - Librería Belén &copy; <?php echo date('Y'); ?></p>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="background-color: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
            🖨️ Imprimir Boleta
        </button>
        <button onclick="window.close()" style="background-color: #95a5a6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            ✖️ Cerrar
        </button>
    </div>

    <script>
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>