<?php
require_once __DIR__ . '/../config/database.php';

// Función para obtener estadísticas del dashboard
function getDashboardStats() {
    $pdo = getConnection();
    
    // Ventas de hoy
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as ventas_hoy FROM ventas WHERE DATE(fecha) = CURDATE()");
    $stmt->execute();
    $ventas_hoy = $stmt->fetch()['ventas_hoy'];
    
    // Ventas del mes
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as ventas_mes FROM ventas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())");
    $stmt->execute();
    $ventas_mes = $stmt->fetch()['ventas_mes'];
    
    // Total productos
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_productos FROM productos WHERE activo = 1");
    $stmt->execute();
    $total_productos = $stmt->fetch()['total_productos'];
    
    // Total clientes
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_clientes FROM clientes WHERE activo = 1");
    $stmt->execute();
    $total_clientes = $stmt->fetch()['total_clientes'];
    
    return [
        'ventas_hoy' => $ventas_hoy,
        'ventas_mes' => $ventas_mes,
        'total_productos' => $total_productos,
        'total_clientes' => $total_clientes
    ];
}

// Función para obtener ventas recientes
function getVentasRecientes($limit = 10) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT v.id, v.total, v.fecha, c.nombre as cliente_nombre 
        FROM ventas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id 
        ORDER BY v.fecha DESC 
        LIMIT :limit
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Función para autenticar usuario
function authenticateUser($email, $password) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND activo = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Función para verificar si el usuario ha iniciado sesión
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Función para obtener todos los productos
function getProductos($search = '', $categoria = '') {
    $pdo = getConnection();
    $sql = "SELECT p.*, c.nombre as categoria_nombre FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.activo = 1";
    
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (p.nombre LIKE ? OR p.codigo LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($categoria)) {
        $sql .= " AND p.categoria_id = ?";
        $params[] = $categoria;
    }
    
    $sql .= " ORDER BY p.nombre";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Función para obtener producto por ID
function getProductoById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND activo = 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Función para obtener todos los clientes
function getClientes($search = '') {
    $pdo = getConnection();
    $sql = "SELECT * FROM clientes WHERE activo = 1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (nombre LIKE ? OR email LIKE ? OR telefono LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY nombre";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Función para obtener cliente por ID
function getClienteById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? AND activo = 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Función para crear una nueva venta
function crearVenta($cliente_id, $productos, $total, $usuario_id) {
    $pdo = getConnection();
    
    try {
        $pdo->beginTransaction();
        
        // Insertar venta
        $stmt = $pdo->prepare("INSERT INTO ventas (cliente_id, total, fecha, usuario_id) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$cliente_id, $total, $usuario_id]);
        $venta_id = $pdo->lastInsertId();
        
        // Insertar detalles de venta
        foreach ($productos as $producto) {
            $stmt = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $venta_id,
                $producto['id'],
                $producto['cantidad'],
                $producto['precio'],
                $producto['subtotal']
            ]);
            
            // Actualizar stock
            $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$producto['cantidad'], $producto['id']]);
        }
        
        $pdo->commit();
        return $venta_id;
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

// Función para obtener categorías
function getCategorias() {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Función para validar stock
function validarStock($producto_id, $cantidad) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();
    
    return $producto && $producto['stock'] >= $cantidad;
}

// Función para formatear fecha
function formatearFecha($fecha) {
    return date('d/m/Y H:i', strtotime($fecha));
}

// Función para formatear moneda
function formatearMoneda($cantidad) {
    return 'S/ ' . number_format($cantidad, 2);
}

// Función para limpiar input
function limpiarInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para verificar permisos de administrador
function esAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Función para generar código de producto
function generarCodigoProducto() {
    return 'PROD' . date('Ymd') . rand(1000, 9999);
}
?>