<?php
// Configurar para devolver solo JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
ini_set('display_errors', 0);
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

// Validar datos requeridos
if (!isset($input['producto_id']) || !isset($input['nuevo_stock']) || !isset($input['motivo'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$producto_id = (int)$input['producto_id'];
$nuevo_stock = (int)$input['nuevo_stock'];
$motivo = trim($input['motivo']);
$usuario_id = $_SESSION['user_id'];

// Validar que el nuevo stock sea válido
if ($nuevo_stock < 0) {
    echo json_encode(['success' => false, 'message' => 'El stock no puede ser negativo']);
    exit();
}

if (empty($motivo)) {
    echo json_encode(['success' => false, 'message' => 'El motivo es requerido']);
    exit();
}

try {
    // Obtener el stock actual del producto
    $stmt = $pdo->prepare("SELECT stock, nombre FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        throw new Exception('Producto no encontrado');
    }
    
    $stock_anterior = $producto['stock'];
    $diferencia = $nuevo_stock - $stock_anterior;
    
    // Actualizar el stock del producto
    $stmt = $pdo->prepare("UPDATE productos SET stock = ? WHERE id = ?");
    $stmt->execute([$nuevo_stock, $producto_id]);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Stock actualizado correctamente',
        'data' => [
            'producto_id' => $producto_id,
            'producto_nombre' => $producto['nombre'],
            'stock_anterior' => $stock_anterior,
            'stock_nuevo' => $nuevo_stock,
            'diferencia' => $diferencia,
            'motivo' => $motivo
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar stock: ' . $e->getMessage()
    ]);
}

exit();
?>