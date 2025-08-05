<?php
require_once __DIR__ . '/../config/database.php';

// Función para obtener estadísticas del dashboard
function getDashboardStats() {
    $pdo = getConnection();
    
    // Ventas de hoy (solo activas)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as ventas_hoy FROM ventas WHERE DATE(fecha) = CURDATE() AND estado != 'cancelada'");
    $stmt->execute();
    $ventas_hoy = $stmt->fetch()['ventas_hoy'];
    
    // Ventas del mes (solo activas)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as ventas_mes FROM ventas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) AND estado != 'cancelada'");
    $stmt->execute();
    $ventas_mes = $stmt->fetch()['ventas_mes'];
    
    // Ventas libres de hoy (solo activas)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as ventas_libres_hoy FROM ventas_libres WHERE DATE(fecha_venta) = CURDATE() AND estado = 'activa'");
    $stmt->execute();
    $ventas_libres_hoy = $stmt->fetch()['ventas_libres_hoy'];
    
    // Ventas libres del mes (solo activas)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as ventas_libres_mes FROM ventas_libres WHERE MONTH(fecha_venta) = MONTH(CURDATE()) AND YEAR(fecha_venta) = YEAR(CURDATE()) AND estado = 'activa'");
    $stmt->execute();
    $ventas_libres_mes = $stmt->fetch()['ventas_libres_mes'];
    
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
        'ventas_libres_hoy' => $ventas_libres_hoy,
        'ventas_libres_mes' => $ventas_libres_mes,
        'total_productos' => $total_productos,
        'total_clientes' => $total_clientes
    ];
}

// Función para obtener ventas recientes
function getVentasRecientes($limit = 10) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT v.id, v.total, v.fecha, v.estado, c.nombre as cliente_nombre 
        FROM ventas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id 
        WHERE v.estado != 'cancelada'
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

// Función consolidada para obtener productos (optimizada)
function getProductos($search = '', $categoria = '', $incluir_inactivos = false) {
    $pdo = getConnection();
    $sql = "SELECT p.*, c.nombre as categoria_nombre FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE 1=1";
    
    $params = [];
    
    // Filtrar por estado activo si no se incluyen inactivos
    if (!$incluir_inactivos) {
        $sql .= " AND p.activo = 1";
    }
    
    if (!empty($search)) {
        $sql .= " AND (p.nombre LIKE ? OR p.codigo LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($categoria)) {
        $sql .= " AND p.categoria_id = ?";
        $params[] = $categoria;
    }
    
    // Ordenar por estado activo primero si se incluyen inactivos
    if ($incluir_inactivos) {
        $sql .= " ORDER BY p.activo DESC, p.nombre";
    } else {
        $sql .= " ORDER BY p.nombre";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Función de compatibilidad para getAllProductos (mantener retrocompatibilidad)
function getAllProductos($search = '', $categoria = '') {
    return getProductos($search, $categoria, true);
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

// ========== FUNCIONES PARA VENTAS LIBRES ==========

// Función para generar número de venta libre
function generarNumeroVentaLibre() {
    $pdo = getConnection();
    $fecha = date('Ymd');
    
    // Obtener el último número del día
    $stmt = $pdo->prepare("SELECT numero_venta FROM ventas_libres WHERE DATE(fecha_venta) = CURDATE() ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $ultimo = $stmt->fetch();
    
    if ($ultimo) {
        // Extraer el número secuencial del último número
        $partes = explode('-', $ultimo['numero_venta']);
        $secuencial = intval($partes[2]) + 1;
    } else {
        $secuencial = 1;
    }
    
    return 'VL-' . $fecha . '-' . str_pad($secuencial, 4, '0', STR_PAD_LEFT);
}

// Función para obtener ventas libres con filtros
function getVentasLibres($filtros = []) {
    $pdo = getConnection();
    $sql = "SELECT vl.*, u.nombre as vendedor_nombre FROM ventas_libres vl 
            LEFT JOIN usuarios u ON vl.usuario_id = u.id 
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($filtros['fecha_inicio'])) {
        $sql .= " AND DATE(vl.fecha_venta) >= ?";
        $params[] = $filtros['fecha_inicio'];
    }
    
    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND DATE(vl.fecha_venta) <= ?";
        $params[] = $filtros['fecha_fin'];
    }
    
    if (!empty($filtros['estado'])) {
        $sql .= " AND vl.estado = ?";
        $params[] = $filtros['estado'];
    }
    
    if (!empty($filtros['busqueda'])) {
        $sql .= " AND (vl.motivo_venta LIKE ? OR vl.descripcion LIKE ? OR vl.numero_venta LIKE ?)";
        $busqueda = "%{$filtros['busqueda']}%";
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
    }
    
    $sql .= " ORDER BY vl.fecha_venta DESC";
    
    if (!empty($filtros['limit'])) {
        $sql .= " LIMIT " . intval($filtros['limit']);
        if (!empty($filtros['offset'])) {
            $sql .= " OFFSET " . intval($filtros['offset']);
        }
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Función para contar ventas libres con filtros
function contarVentasLibres($filtros = []) {
    $pdo = getConnection();
    $sql = "SELECT COUNT(*) as total FROM ventas_libres WHERE 1=1";
    
    $params = [];
    
    if (!empty($filtros['fecha_inicio'])) {
        $sql .= " AND DATE(fecha_venta) >= ?";
        $params[] = $filtros['fecha_inicio'];
    }
    
    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND DATE(fecha_venta) <= ?";
        $params[] = $filtros['fecha_fin'];
    }
    
    if (!empty($filtros['estado'])) {
        $sql .= " AND estado = ?";
        $params[] = $filtros['estado'];
    }
    
    if (!empty($filtros['busqueda'])) {
        $sql .= " AND (motivo_venta LIKE ? OR descripcion LIKE ? OR numero_venta LIKE ?)";
        $busqueda = "%{$filtros['busqueda']}%";
        $params[] = $busqueda;
        $params[] = $busqueda;
        $params[] = $busqueda;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch()['total'];
}

// Función para obtener venta libre por ID
function getVentaLibreById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT vl.*, u.nombre as vendedor_nombre FROM ventas_libres vl 
                          LEFT JOIN usuarios u ON vl.usuario_id = u.id 
                          WHERE vl.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Función para obtener estadísticas de ventas libres
function getEstadisticasVentasLibres() {
    $pdo = getConnection();
    
    // Total de ventas libres activas
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_ventas, COALESCE(SUM(total), 0) as total_ingresos FROM ventas_libres WHERE estado = 'activa'");
    $stmt->execute();
    $activas = $stmt->fetch();
    
    // Total de ventas libres anuladas
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_anuladas FROM ventas_libres WHERE estado = 'anulada'");
    $stmt->execute();
    $anuladas = $stmt->fetch();
    
    return [
        'total_ventas' => $activas['total_ventas'],
        'total_ingresos' => $activas['total_ingresos'],
        'total_anuladas' => $anuladas['total_anuladas']
    ];
}

// Función para obtener ventas libres recientes
function getVentasLibresRecientes($limit = 5) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT vl.id, vl.numero_venta, vl.motivo_venta, vl.total, vl.fecha_venta, vl.estado, u.nombre as vendedor_nombre 
        FROM ventas_libres vl 
        LEFT JOIN usuarios u ON vl.usuario_id = u.id 
        ORDER BY vl.fecha_venta DESC 
        LIMIT :limit
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

?>