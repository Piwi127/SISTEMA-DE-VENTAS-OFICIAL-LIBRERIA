<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    // Obtener datos del POST
    $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
    $productos_json = isset($_POST['productos']) ? $_POST['productos'] : '';
    $total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
    $usuario_id = $_SESSION['user_id'];
    
    // Validar datos básicos
    if ($cliente_id <= 0) {
        throw new Exception('Cliente no válido');
    }
    
    if (empty($productos_json)) {
        throw new Exception('No hay productos en la venta');
    }
    
    if ($total <= 0) {
        throw new Exception('Total de venta no válido');
    }
    
    // Decodificar productos
    $productos = json_decode($productos_json, true);
    if (!$productos || !is_array($productos)) {
        throw new Exception('Datos de productos no válidos');
    }
    
    // Verificar que el cliente existe
    $cliente = getClienteById($cliente_id);
    if (!$cliente) {
        throw new Exception('Cliente no encontrado');
    }
    
    // Validar productos y stock
    $productos_validados = [];
    $total_calculado = 0;
    
    foreach ($productos as $item) {
        if (!isset($item['id']) || !isset($item['cantidad']) || !isset($item['precio'])) {
            throw new Exception('Datos de producto incompletos');
        }
        
        $producto_id = intval($item['id']);
        $cantidad = intval($item['cantidad']);
        $precio = floatval($item['precio']);
        
        if ($cantidad <= 0) {
            throw new Exception('Cantidad no válida para producto ID: ' . $producto_id);
        }
        
        // Obtener producto de la base de datos
        $producto = getProductoById($producto_id);
        if (!$producto) {
            throw new Exception('Producto no encontrado: ID ' . $producto_id);
        }
        
        // Verificar stock
        if (!validarStock($producto_id, $cantidad)) {
            throw new Exception('Stock insuficiente para: ' . $producto['nombre']);
        }
        
        // Verificar precio (usar precio actual de la base de datos)
        $precio_actual = floatval($producto['precio']);
        $subtotal = $cantidad * $precio_actual;
        
        $productos_validados[] = [
            'id' => $producto_id,
            'cantidad' => $cantidad,
            'precio' => $precio_actual,
            'subtotal' => $subtotal
        ];
        
        $total_calculado += $subtotal;
    }
    
    // Verificar que el total coincida (con tolerancia de centavos)
    if (abs($total_calculado - $total) > 0.01) {
        throw new Exception('El total no coincide con los productos seleccionados');
    }
    
    // Crear la venta
    $venta_id = crearVenta($cliente_id, $productos_validados, $total_calculado, $usuario_id);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Venta procesada exitosamente',
        'venta_id' => $venta_id,
        'total' => $total_calculado
    ]);
    
} catch (Exception $e) {
    // Log del error (en un sistema real, usar un logger apropiado)
    error_log('Error en procesar_venta.php: ' . $e->getMessage());
    
    // Respuesta de error
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>