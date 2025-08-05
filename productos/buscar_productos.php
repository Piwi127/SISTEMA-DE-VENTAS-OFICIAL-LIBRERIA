<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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

// Obtener el término de búsqueda
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($search) || strlen($search) < 2) {
    echo json_encode(['success' => true, 'productos' => []]);
    exit();
}

try {
    $pdo = getConnection();
    
    // Buscar productos que coincidan con el término
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.nombre,
            p.codigo,
            p.precio,
            p.stock,
            c.nombre as categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE p.activo = 1 
        AND (p.nombre LIKE ? OR p.codigo LIKE ?)
        ORDER BY p.nombre ASC
        LIMIT 10
    ");
    
    $searchTerm = '%' . $search . '%';
    $stmt->execute([$searchTerm, $searchTerm]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear los resultados
    $resultados = [];
    foreach ($productos as $producto) {
        $resultados[] = [
            'id' => $producto['id'],
            'nombre' => $producto['nombre'],
            'codigo' => $producto['codigo'],
            'precio' => number_format($producto['precio'], 2),
            'stock' => $producto['stock'],
            'categoria' => $producto['categoria'] ?? 'Sin categoría',
            'texto_completo' => $producto['nombre'] . ' (' . $producto['codigo'] . ')'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'productos' => $resultados
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la búsqueda: ' . $e->getMessage()
    ]);
}

exit();
?>