<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para realizar esta acción']);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener el ID de la venta
$venta_id = isset($_POST['venta_id']) ? intval($_POST['venta_id']) : 0;

if ($venta_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de venta inválido']);
    exit();
}

try {
    $pdo = getConnection();
    $pdo->beginTransaction();
    
    // Verificar que la venta existe y no está ya anulada
    $stmt = $pdo->prepare("SELECT id, estado FROM ventas WHERE id = ?");
    $stmt->execute([$venta_id]);
    $venta = $stmt->fetch();
    
    if (!$venta) {
        throw new Exception('La venta no existe');
    }
    
    if ($venta['estado'] === 'cancelada') {
        throw new Exception('La venta ya está anulada');
    }
    
    // Obtener los productos de la venta para restaurar el stock
    $stmt = $pdo->prepare("
        SELECT producto_id, cantidad 
        FROM detalle_ventas 
        WHERE venta_id = ?
    ");
    $stmt->execute([$venta_id]);
    $detalles = $stmt->fetchAll();
    
    // Restaurar el stock de cada producto
    foreach ($detalles as $detalle) {
        $stmt = $pdo->prepare("
            UPDATE productos 
            SET stock = stock + ? 
            WHERE id = ?
        ");
        $stmt->execute([$detalle['cantidad'], $detalle['producto_id']]);
    }
    
    // Marcar la venta como cancelada
    $stmt = $pdo->prepare("
        UPDATE ventas 
        SET estado = 'cancelada' 
        WHERE id = ?
    ");
    $stmt->execute([$venta_id]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Venta anulada exitosamente. El stock de los productos ha sido restaurado.'
    ]);
    
} catch (Exception $e) {
    $pdo->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Error al anular la venta: ' . $e->getMessage()
    ]);
}
?>