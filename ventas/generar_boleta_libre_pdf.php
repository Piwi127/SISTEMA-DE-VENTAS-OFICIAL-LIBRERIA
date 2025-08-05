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

// Verificar que se proporcione el ID de la venta libre
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de venta libre no proporcionado']);
    exit();
}

$venta_id = (int)$_GET['id'];
$action = $_GET['action'] ?? 'view'; // view, print, download

try {
    $pdo = getConnection();
    
    // Obtener datos de la venta libre
    $stmt = $pdo->prepare("
        SELECT vl.*, u.nombre as vendedor_nombre
        FROM ventas_libres vl 
        LEFT JOIN usuarios u ON vl.usuario_id = u.id
        WHERE vl.id = ? AND vl.estado = 'activa'
    ");
    $stmt->execute([$venta_id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$venta) {
        echo json_encode(['success' => false, 'message' => 'Venta libre no encontrada']);
        exit();
    }
    
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

// Calcular totales (para venta libre no hay IGV separado, es precio final)
$subtotal = $venta['total'];
$igv = 0; // Las ventas libres pueden no incluir IGV separado
$total = $venta['total'];

// Crear PDF con TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurar información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($empresa['nombre']);
$pdf->SetTitle('Boleta Venta Libre N° ' . $venta['numero_venta']);
$pdf->SetSubject('Boleta de Venta Libre');
$pdf->SetKeywords('Boleta, Venta Libre, ' . $empresa['nombre']);

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
                <h2 style="margin: 0; font-size: 16px; color: #e74c3c;">BOLETA VENTA LIBRE</h2>
                <p style="margin: 5px 0; font-size: 14px; font-weight: bold;">N° ' . $venta['numero_venta'] . '</p>
            </div>
        </td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Información de la venta
$html = '<table style="width: 100%; margin-bottom: 15px;">
    <tr>
        <td style="width: 50%;">
            <p><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($venta['fecha_venta'])) . '</p>
            <p><strong>Vendedor:</strong> ' . $venta['vendedor_nombre'] . '</p>
            <p><strong>Método de Pago:</strong> ' . ucfirst($venta['metodo_pago']) . '</p>
        </td>
        <td style="width: 50%;">
            <p><strong>Cantidad:</strong> ' . $venta['cantidad'] . '</p>
            <p><strong>Precio Unitario:</strong> S/ ' . number_format($venta['precio_unitario'], 2) . '</p>
            <p><strong>Estado:</strong> ' . ucfirst($venta['estado']) . '</p>
        </td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Información del servicio/producto
$html = '<div style="border: 1px solid #bdc3c7; padding: 15px; margin-bottom: 15px; background-color: #f8f9fa;">
    <h3 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 14px;">MOTIVO DE VENTA</h3>
    <p style="margin: 0 0 10px 0; font-size: 12px; line-height: 1.4;">' . nl2br(htmlspecialchars($venta['motivo_venta'])) . '</p>
    
    <h3 style="margin: 15px 0 10px 0; color: #2c3e50; font-size: 14px;">DESCRIPCIÓN DETALLADA</h3>
    <p style="margin: 0; font-size: 12px; line-height: 1.4;">' . nl2br(htmlspecialchars($venta['descripcion'])) . '</p>';

if (!empty($venta['notas'])) {
    $html .= '
    <h3 style="margin: 15px 0 10px 0; color: #2c3e50; font-size: 14px;">NOTAS ADICIONALES</h3>
    <p style="margin: 0; font-size: 12px; line-height: 1.4; color: #7f8c8d;">' . nl2br(htmlspecialchars($venta['notas'])) . '</p>';
}

$html .= '</div>';

$pdf->writeHTML($html, true, false, true, false, '');

// Tabla de resumen
$html = '<table border="1" cellpadding="8" cellspacing="0" style="width: 100%; margin-bottom: 15px;">
    <thead>
        <tr style="background-color: #34495e; color: white;">
            <th style="width: 15%; text-align: center;"><strong>Cantidad</strong></th>
            <th style="width: 45%; text-align: left;"><strong>Concepto</strong></th>
            <th style="width: 20%; text-align: right;"><strong>P. Unitario</strong></th>
            <th style="width: 20%; text-align: right;"><strong>Total</strong></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: center; font-size: 14px;">' . $venta['cantidad'] . '</td>
            <td style="text-align: left; font-size: 12px;">' . htmlspecialchars(substr($venta['motivo_venta'], 0, 50)) . (strlen($venta['motivo_venta']) > 50 ? '...' : '') . '</td>
            <td style="text-align: right; font-size: 12px;">S/ ' . number_format($venta['precio_unitario'], 2) . '</td>
            <td style="text-align: right; font-size: 12px;">S/ ' . number_format($venta['total'], 2) . '</td>
        </tr>
    </tbody>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Totales
$html = '<table style="width: 100%; margin-top: 10px;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="width: 40%;">
            <table border="1" cellpadding="8" cellspacing="0" style="width: 100%;">
                <tr style="background-color: #2c3e50; color: white;">
                    <td style="text-align: left; font-size: 14px;"><strong>TOTAL A PAGAR:</strong></td>
                    <td style="text-align: right; font-size: 14px;"><strong>S/ ' . number_format($venta['total'], 2) . '</strong></td>
                </tr>
            </table>
        </td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Información adicional sobre venta libre
$html = '<div style="border: 1px solid #f39c12; background-color: #fef9e7; padding: 10px; margin-top: 20px;">
    <p style="margin: 0; font-size: 11px; color: #d68910; text-align: center;">
        <strong>VENTA LIBRE:</strong> Este documento corresponde a una venta de producto/servicio especial no incluido en el inventario regular.
    </p>
</div>';

$pdf->writeHTML($html, true, false, true, false, '');

// Pie de página
$html = '<div style="text-align: center; margin-top: 30px; border-top: 1px solid #bdc3c7; padding-top: 15px;">
    <p style="font-size: 14px; color: #27ae60;"><strong>¡Gracias por confiar en nosotros!</strong></p>
    <p style="font-size: 12px; color: #7f8c8d;">Vuelva pronto</p>
    <p style="font-size: 10px; color: #95a5a6; margin-top: 15px;">Sistema de Ventas - Generado el ' . date('d/m/Y H:i') . '</p>
</div>';

$pdf->writeHTML($html, true, false, true, false, '');

// Configurar nombre del archivo
$filename = 'Boleta_Libre_' . $venta['numero_venta'] . '_' . date('Ymd') . '.pdf';

// Limpiar buffer de salida antes de enviar PDF
ob_end_clean();

// Determinar cómo enviar el PDF según la acción
switch ($action) {
    case 'download':
        // Forzar descarga
        $pdf->Output($filename, 'D');
        break;
    case 'print':
        // Abrir en nueva ventana para imprimir
        $pdf->Output($filename, 'I');
        break;
    default:
        // Mostrar en el navegador
        $pdf->Output($filename, 'I');
        break;
}
?>