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
    // Validar y sanitizar datos de entrada
    $motivo_venta = trim($_POST['motivo_venta'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $cantidad = intval($_POST['cantidad'] ?? 0);
    $precio_unitario = floatval($_POST['precio_unitario'] ?? 0);
    $total = floatval($_POST['total'] ?? 0);
    $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
    $notas = trim($_POST['notas'] ?? '');
    $usuario_id = $_SESSION['user_id'];
    
    // Validaciones
    $errores = [];
    
    if (empty($motivo_venta)) {
        $errores[] = 'El motivo de venta es obligatorio';
    }
    
    if (empty($descripcion)) {
        $errores[] = 'La descripción es obligatoria';
    }
    
    if ($cantidad <= 0) {
        $errores[] = 'La cantidad debe ser mayor a 0';
    }
    
    if ($precio_unitario <= 0) {
        $errores[] = 'El precio unitario debe ser mayor a 0';
    }
    
    if ($total <= 0) {
        $errores[] = 'El total debe ser mayor a 0';
    }
    
    // Verificar que el total calculado coincida
    $total_calculado = $cantidad * $precio_unitario;
    if (abs($total - $total_calculado) > 0.01) {
        $errores[] = 'El total no coincide con el cálculo (cantidad × precio unitario)';
    }
    
    // Validar método de pago
    $metodos_validos = ['efectivo', 'tarjeta', 'transferencia'];
    if (!in_array($metodo_pago, $metodos_validos)) {
        $errores[] = 'Método de pago no válido';
    }
    
    if (!empty($errores)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Errores de validación: ' . implode(', ', $errores)
        ]);
        exit();
    }
    
    // Generar número de venta único
    $numero_venta = generarNumeroVentaLibre();
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Insertar venta libre
    $sql = "INSERT INTO ventas_libres (
                numero_venta, motivo_venta, descripcion, cantidad, 
                precio_unitario, total, usuario_id, metodo_pago, notas
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([
        $numero_venta,
        $motivo_venta,
        $descripcion,
        $cantidad,
        $precio_unitario,
        $total,
        $usuario_id,
        $metodo_pago,
        $notas
    ]);
    
    if (!$resultado) {
        throw new Exception('Error al insertar la venta libre');
    }
    
    $venta_id = $pdo->lastInsertId();
    
    // Confirmar transacción
    $pdo->commit();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Venta libre registrada exitosamente',
        'venta_id' => $venta_id,
        'numero_venta' => $numero_venta,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log del error (en producción, usar un sistema de logs apropiado)
    error_log('Error en procesar_venta_libre.php: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor. Intente nuevamente.'
    ]);
}

?>