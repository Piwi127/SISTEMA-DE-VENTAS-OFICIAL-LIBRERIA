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
        SELECT dv.*, p.nombre as producto_nombre, p.codigo as producto_codigo
        FROM detalle_ventas dv
        JOIN productos p ON dv.producto_id = p.id
        WHERE dv.venta_id = ?
    ");
    $stmt->execute([$venta_id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

// Datos de la empresa
$empresa = [
    'nombre' => 'Libreria Belen',
    'direccion' => 'Jr. Conchucos 120',
    'ruc' => '10765351114',
    'telefono' => '947 872 207'
];

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
    <title>Boleta de Venta - <?php echo $empresa['nombre']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }
        
        .boleta {
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .empresa-nombre {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .empresa-info {
            font-size: 10px;
            margin-bottom: 2px;
        }
        
        .boleta-titulo {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            border: 1px solid #000;
            padding: 5px;
        }
        
        .venta-info {
            margin: 10px 0;
            font-size: 10px;
        }
        
        .venta-info div {
            margin-bottom: 2px;
        }
        
        .productos {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
            margin: 10px 0;
        }
        
        .producto-header {
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 5px;
        }
        
        .producto {
            margin-bottom: 5px;
            font-size: 10px;
        }
        
        .producto-nombre {
            font-weight: bold;
        }
        
        .producto-detalle {
            display: flex;
            justify-content: space-between;
            margin-top: 2px;
        }
        
        .totales {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .total-linea {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .total-final {
            font-weight: bold;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .no-print {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            body {
                margin: 0;
            }
            
            .boleta {
                width: 100%;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨️ Imprimir</button>
        <a href="lista_ventas.php" class="btn btn-secondary">← Volver</a>
    </div>
    
    <div class="boleta">
        <div class="header">
            <div class="empresa-nombre"><?php echo $empresa['nombre']; ?></div>
            <div class="empresa-info">RUC: <?php echo $empresa['ruc']; ?></div>
            <div class="empresa-info"><?php echo $empresa['direccion']; ?></div>
            <div class="empresa-info">Tel: <?php echo $empresa['telefono']; ?></div>
            <div class="boleta-titulo">BOLETA DE VENTA</div>
        </div>
        
        <div class="venta-info">
            <div><strong>Boleta N°:</strong> <?php echo str_pad($venta['id'], 6, '0', STR_PAD_LEFT); ?></div>
            <div><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></div>
            <div><strong>Cliente:</strong> <?php echo $venta['cliente_nombre'] ?: 'Cliente General'; ?></div>
            <?php if ($venta['cliente_telefono']): ?>
            <div><strong>Teléfono:</strong> <?php echo $venta['cliente_telefono']; ?></div>
            <?php endif; ?>
            <div><strong>Vendedor:</strong> <?php echo $venta['vendedor_nombre']; ?></div>
        </div>
        
        <div class="productos">
            <div class="producto-header">PRODUCTOS</div>
            <?php foreach ($detalles as $detalle): ?>
            <div class="producto">
                <div class="producto-nombre"><?php echo $detalle['producto_nombre']; ?></div>
                <div class="producto-detalle">
                    <span><?php echo $detalle['cantidad']; ?> x S/ <?php echo number_format($detalle['precio_unitario'], 2); ?></span>
                    <span>S/ <?php echo number_format($detalle['subtotal'], 2); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="totales">
            <div class="total-linea">
                <span>Items:</span>
                <span><?php echo $total_items; ?></span>
            </div>
            <div class="total-linea">
                <span>Subtotal:</span>
                <span>S/ <?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="total-linea">
                <span>IGV (18%):</span>
                <span>S/ <?php echo number_format($igv, 2); ?></span>
            </div>
            <div class="total-linea total-final">
                <span>TOTAL A PAGAR:</span>
                <span>S/ <?php echo number_format($venta['total'], 2); ?></span>
            </div>
        </div>
        
        <div class="footer">
            <div>¡Gracias por su compra!</div>
            <div>Vuelva pronto</div>
            <div style="margin-top: 10px; font-size: 8px;">
                Sistema de Ventas - <?php echo date('d/m/Y H:i'); ?>
            </div>
        </div>
    </div>
</body>
</html>