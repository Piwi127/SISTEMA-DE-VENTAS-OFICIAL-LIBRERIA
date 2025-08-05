<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Configurar respuesta JSON
header('Content-Type: application/json');

try {
    // Obtener datos JSON del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['venta_id']) || empty($input['venta_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de venta libre no proporcionado']);
        exit();
    }
    
    $venta_id = (int)$input['venta_id'];
    $usuario_id = $_SESSION['user_id'];
    
    $pdo = getConnection();
    
    // Verificar que la venta existe y está activa
    $stmt = $pdo->prepare("SELECT id, numero_venta, estado, total FROM ventas_libres WHERE id = ?");
    $stmt->execute([$venta_id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$venta) {
        echo json_encode(['success' => false, 'message' => 'Venta libre no encontrada']);
        exit();
    }
    
    if ($venta['estado'] !== 'activa') {
        echo json_encode(['success' => false, 'message' => 'La venta ya está anulada']);
        exit();
    }
    
    // Verificar permisos (solo admin o el mismo vendedor puede anular)
    if ($_SESSION['rol'] !== 'admin') {
        $stmt = $pdo->prepare("SELECT usuario_id FROM ventas_libres WHERE id = ?");
        $stmt->execute([$venta_id]);
        $venta_usuario = $stmt->fetchColumn();
        
        if ($venta_usuario != $usuario_id) {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para anular esta venta']);
            exit();
        }
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Anular la venta libre
    $stmt = $pdo->prepare("UPDATE ventas_libres SET estado = 'anulada' WHERE id = ?");
    $resultado = $stmt->execute([$venta_id]);
    
    if (!$resultado) {
        throw new Exception('Error al anular la venta libre');
    }
    
    // Registrar la anulación en un log (opcional)
    $stmt = $pdo->prepare("
        INSERT INTO logs_sistema (usuario_id, accion, descripcion, fecha) 
        VALUES (?, 'anular_venta_libre', ?, NOW())
    ");
    
    // Verificar si la tabla logs_sistema existe antes de insertar
    $table_exists = $pdo->query("SHOW TABLES LIKE 'logs_sistema'")->rowCount() > 0;
    
    if ($table_exists) {
        $descripcion = "Venta libre anulada: {$venta['numero_venta']} por S/ {$venta['total']}";
        $stmt->execute([$usuario_id, $descripcion]);
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Venta libre anulada exitosamente',
        'numero_venta' => $venta['numero_venta']
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log del error (en producción, usar un sistema de logs apropiado)
    error_log('Error en anular_venta_libre.php: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor. Intente nuevamente.'
    ]);
}
?>