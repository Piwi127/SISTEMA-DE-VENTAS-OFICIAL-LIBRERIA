<?php
ob_start(); // Iniciar buffer de salida
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Verificar que se proporcione el ID de la venta
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de venta no proporcionado']);
    exit();
}

$venta_id = (int)$_GET['id'];

try {
    $pdo = getConnection();
    
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
        echo json_encode(['success' => false, 'message' => 'Venta no encontrada']);
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
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    exit();
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

// Crear PDF con TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurar información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($empresa['nombre']);
$pdf->SetTitle('Boleta de Venta N° ' . str_pad($venta['id'], 6, '0', STR_PAD_LEFT));
$pdf->SetSubject('Boleta de Venta');
$pdf->SetKeywords('Boleta, Venta, ' . $empresa['nombre']);

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Configurar salto de página automático
$pdf->SetAutoPageBreak(TRUE, 25);

// Configurar factor de escala de imagen
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Configurar fuente
$pdf->SetFont('helvetica', '', 10);

// Agregar página
$pdf->AddPage();

// Encabezado de la empresa
$html = '<table style="width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px;">
    <tr>
        <td style="text-align: center;">
            <h1 style="font-size: 18px; margin: 0; color: #2c3e50;">' . $empresa['nombre'] . '</h1>
            <p style="margin: 5px 0; font-size: 12px;">RUC: ' . $empresa['ruc'] . '</p>
            <p style="margin: 5px 0; font-size: 12px;">' . $empresa['direccion'] . '</p>
            <p style="margin: 5px 0; font-size: 12px;">Tel: ' . $empresa['telefono'] . '</p>
            <div style="border: 2px solid #000; padding: 8px; margin-top: 10px; background-color: #f8f9fa;">
                <h2 style="margin: 0; font-size: 16px; color: #e74c3c;">BOLETA DE VENTA</h2>
                <p style="margin: 5px 0; font-size: 14px; font-weight: bold;">N° ' . str_pad($venta['id'], 6, '0', STR_PAD_LEFT) . '</p>
            </div>
        </td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Información de la venta
$html = '<table style="width: 100%; margin-bottom: 15px;">
    <tr>
        <td style="width: 50%;">
            <p><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($venta['fecha'])) . '</p>
            <p><strong>Cliente:</strong> ' . ($venta['cliente_nombre'] ?: 'Cliente General') . '</p>';

if ($venta['cliente_telefono']) {
    $html .= '<p><strong>Teléfono:</strong> ' . $venta['cliente_telefono'] . '</p>';
}

$html .= '        </td>
        <td style="width: 50%;">
            <p><strong>Vendedor:</strong> ' . $venta['vendedor_nombre'] . '</p>
            <p><strong>Total Items:</strong> ' . $total_items . '</p>
        </td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Tabla de productos
$html = '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; margin-bottom: 15px;">
    <thead>
        <tr style="background-color: #34495e; color: white;">
            <th style="width: 10%; text-align: center;"><strong>Cant.</strong></th>
            <th style="width: 50%; text-align: left;"><strong>Producto</strong></th>
            <th style="width: 20%; text-align: right;"><strong>P. Unit.</strong></th>
            <th style="width: 20%; text-align: right;"><strong>Subtotal</strong></th>
        </tr>
    </thead>
    <tbody>';

foreach ($detalles as $detalle) {
    $html .= '<tr>
            <td style="text-align: center;">' . $detalle['cantidad'] . '</td>
            <td style="text-align: left;">' . $detalle['producto_nombre'] . '<br><small style="color: #7f8c8d;">Código: ' . $detalle['producto_codigo'] . '</small></td>
            <td style="text-align: right;">S/ ' . number_format($detalle['precio_unitario'], 2) . '</td>
            <td style="text-align: right;">S/ ' . number_format($detalle['subtotal'], 2) . '</td>
        </tr>';
}

$html .= '    </tbody>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Totales
$html = '<table style="width: 100%; margin-top: 10px;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="width: 40%;">
            <table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="text-align: left; background-color: #ecf0f1;"><strong>Subtotal:</strong></td>
                    <td style="text-align: right;">S/ ' . number_format($subtotal, 2) . '</td>
                </tr>
                <tr>
                    <td style="text-align: left; background-color: #ecf0f1;"><strong>IGV (18%):</strong></td>
                    <td style="text-align: right;">S/ ' . number_format($igv, 2) . '</td>
                </tr>
                <tr style="background-color: #2c3e50; color: white;">
                    <td style="text-align: left;"><strong>TOTAL A PAGAR:</strong></td>
                    <td style="text-align: right;"><strong>S/ ' . number_format($venta['total'], 2) . '</strong></td>
                </tr>
            </table>
        </td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Pie de página
$html = '<div style="text-align: center; margin-top: 30px; border-top: 1px solid #bdc3c7; padding-top: 15px;">
    <p style="font-size: 14px; color: #27ae60;"><strong>¡Gracias por su compra!</strong></p>
    <p style="font-size: 12px; color: #7f8c8d;">Vuelva pronto</p>
    <p style="font-size: 10px; color: #95a5a6; margin-top: 15px;">Sistema de Ventas - Generado el ' . date('d/m/Y H:i') . '</p>
</div>';

$pdf->writeHTML($html, true, false, true, false, '');

// Configurar nombre del archivo
$filename = 'Boleta_' . str_pad($venta['id'], 6, '0', STR_PAD_LEFT) . '_' . date('Ymd') . '.pdf';

// Limpiar buffer de salida antes de enviar PDF
ob_end_clean();

// Enviar PDF al navegador
$pdf->Output($filename, 'I');