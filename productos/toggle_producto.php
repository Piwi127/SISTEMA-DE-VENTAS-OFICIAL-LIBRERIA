<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos para realizar esta acción.'
    ]);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
    exit();
}

// Obtener datos del POST
$producto_id = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
$nuevo_estado = isset($_POST['activo']) ? (bool)$_POST['activo'] : false;

// Validar datos
if ($producto_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de producto inválido.'
    ]);
    exit();
}

try {
    $pdo = getConnection();
    
    // Verificar que el producto existe
    $stmt = $pdo->prepare("SELECT id, nombre, activo FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        echo json_encode([
            'success' => false,
            'message' => 'Producto no encontrado.'
        ]);
        exit();
    }
    
    // Actualizar el estado del producto
    $stmt = $pdo->prepare("
        UPDATE productos 
        SET activo = ?, fecha_actualizacion = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    if ($stmt->execute([$nuevo_estado ? 1 : 0, $producto_id])) {
        $accion = $nuevo_estado ? 'activado' : 'desactivado';
        
        echo json_encode([
            'success' => true,
            'message' => "Producto '{$producto['nombre']}' {$accion} correctamente.",
            'nuevo_estado' => $nuevo_estado
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el producto.'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Error en toggle_producto.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor.'
    ]);
}
?>