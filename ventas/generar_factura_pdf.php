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
    <title>Factura - <?php echo str_pad($venta['id'], 8, '0', STR_PAD_LEFT); ?></title>
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
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 26px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .document-type {
            font-size: 20px;
            font-weight: bold;
            background-color: #e74c3c;
            color: white;
            padding: 12px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-title {
            font-weight: bold;
            background-color: #ecf0f1;
            padding: 10px;
            border-left: 4px solid #e74c3c;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .info-content {
            padding: 0 10px;
            line-height: 1.6;
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
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #e74c3c;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 3px solid #e74c3c;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .total-final {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            border-top: 3px solid #e74c3c;
            padding-top: 15px;
            margin-top: 15px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 2px solid #ddd;
            padding-top: 20px;
        }
        .legal-info {
            background-color: #f1f2f6;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 11px;
            line-height: 1.4;
        }
        .ruc-box {
            background-color: #e74c3c;
            color: white;
            padding: 8px;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 0;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">LIBRERÍA BELÉN S.A.C.</div>
        <div class="ruc-box">RUC: 20123456789</div>
        <div><strong>Dirección:</strong> Av. Principal 123, Lima, Perú</div>
        <div><strong>Teléfono:</strong> (01) 234-5678 | <strong>Email:</strong> facturacion@libreriabelen.com</div>
        <div class="document-type">FACTURA ELECTRÓNICA</div>
        <div style="font-size: 16px;"><strong>N° F001-<?php echo str_pad($venta['id'], 8, '0', STR_PAD_LEFT); ?></strong></div>
    </div>

    <div class="two-column">
        <div class="column">
            <div class="info-section">
                <div class="info-title">DATOS DEL CLIENTE</div>
                <div class="info-content">
                    <strong>Razón Social:</strong> <?php echo htmlspecialchars($venta['cliente_nombre']); ?><br>
                    <strong>RUC/DNI:</strong> 12345678901<br>
                    <?php if ($venta['cliente_email']): ?>
                    <strong>Email:</strong> <?php echo htmlspecialchars($venta['cliente_email']); ?><br>
                    <?php endif; ?>
                    <?php if ($venta['cliente_telefono']): ?>
                    <strong>Teléfono:</strong> <?php echo htmlspecialchars($venta['cliente_telefono']); ?><br>
                    <?php endif; ?>
                    <?php if ($venta['cliente_direccion']): ?>
                    <strong>Dirección Fiscal:</strong> <?php echo htmlspecialchars($venta['cliente_direccion']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="column">
            <div class="info-section">
                <div class="info-title">DATOS DE LA FACTURA</div>
                <div class="info-content">
                    <strong>Fecha de Emisión:</strong> <?php echo date('d/m/Y', strtotime($venta['fecha_venta'])); ?><br>
                    <strong>Hora de Emisión:</strong> <?php echo date('H:i:s', strtotime($venta['fecha_venta'])); ?><br>
                    <strong>Fecha de Vencimiento:</strong> <?php echo date('d/m/Y', strtotime($venta['fecha_venta'] . ' +30 days')); ?><br>
                    <strong>Vendedor:</strong> <?php echo htmlspecialchars($venta['vendedor_nombre']); ?><br>
                    <strong>Moneda:</strong> Soles Peruanos (PEN)<br>
                    <strong>Tipo de Operación:</strong> Venta Interna
                </div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-title">DETALLE DE PRODUCTOS Y/O SERVICIOS</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Código</th>
                    <th style="width: 40%;">Descripción</th>
                    <th style="width: 10%;">Unidad</th>
                    <th style="width: 10%;">Cantidad</th>
                    <th style="width: 12%;">Precio Unit.</th>
                    <th style="width: 13%;">Importe</th>
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
                    <td class="text-center">UND</td>
                    <td class="text-center"><?php echo $detalle['cantidad']; ?></td>
                    <td class="text-right">S/ <?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                    <td class="text-right">S/ <?php echo number_format($detalle['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="total-section">
        <div class="info-title">RESUMEN DE IMPORTES</div>
        <?php 
        $subtotal_sin_igv = $venta['total'] / 1.18;
        $igv_monto = $venta['total'] - $subtotal_sin_igv;
        ?>
        <div class="total-row">
            <span>Operaciones Gravadas:</span>
            <span>S/ <?php echo number_format($subtotal_sin_igv, 2); ?></span>
        </div>
        <div class="total-row">
            <span>Operaciones Inafectas:</span>
            <span>S/ 0.00</span>
        </div>
        <div class="total-row">
            <span>Operaciones Exoneradas:</span>
            <span>S/ 0.00</span>
        </div>
        <div class="total-row">
            <span>IGV (18%):</span>
            <span>S/ <?php echo number_format($igv_monto, 2); ?></span>
        </div>
        <div class="total-row">
            <span>Otros Tributos:</span>
            <span>S/ 0.00</span>
        </div>
        <div class="total-row total-final">
            <span>IMPORTE TOTAL:</span>
            <span>S/ <?php echo number_format($venta['total'], 2); ?></span>
        </div>
    </div>

    <div class="legal-info">
        <div class="info-title">INFORMACIÓN LEGAL</div>
        <p><strong>Condiciones de Pago:</strong> Contado</p>
        <p><strong>Observaciones:</strong> Factura emitida de acuerdo a la Ley de Comprobantes de Pago.</p>
        <p><strong>Representación Impresa de la Factura Electrónica:</strong> Esta factura ha sido generada en el Sistema de Facturación Electrónica de SUNAT.</p>
        <p><strong>Autorización:</strong> Resolución de Superintendencia N° 097-2012/SUNAT</p>
    </div>

    <div class="footer">
        <p><strong>¡Gracias por confiar en nosotros!</strong></p>
        <p>Esta factura es una representación impresa de la Factura Electrónica generada en el sistema de SUNAT.</p>
        <p>Para consultas sobre esta factura, comuníquese al (01) 234-5678 o escriba a facturacion@libreriabelen.com</p>
        <p>Documento generado el <?php echo date('d/m/Y H:i:s'); ?></p>
        <p><strong>Sistema de Facturación Electrónica - Librería Belén S.A.C. &copy; <?php echo date('Y'); ?></strong></p>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="background-color: #e74c3c; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px; font-size: 14px;">
            🖨️ Imprimir Factura
        </button>
        <button onclick="window.close()" style="background-color: #95a5a6; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
            ✖️ Cerrar
        </button>
    </div>

    <script>
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>