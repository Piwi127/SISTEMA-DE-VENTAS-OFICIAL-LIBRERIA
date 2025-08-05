<?php
// Sistema de notificaciones para recordatorios de notas
// Determinar la ruta correcta al archivo de configuración
$config_path = __DIR__ . '/../config/database.php';
if (!file_exists($config_path)) {
    $config_path = dirname(__DIR__) . '/config/database.php';
}
require_once $config_path;

function obtenerNotificacionesPendientes($user_id) {
    try {
        $pdo = getConnection();
        
        // Obtener notas con recordatorios que ya han llegado o están próximos (dentro de 5 minutos)
        $sql = "SELECT id, asunto, cuerpo_mensaje, fecha_recordatorio 
                FROM notas 
                WHERE user_id = ? 
                AND estado = 'pendiente' 
                AND fecha_recordatorio IS NOT NULL 
                AND fecha_recordatorio <= DATE_ADD(NOW(), INTERVAL 5 MINUTE)
                ORDER BY fecha_recordatorio ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener notificaciones: " . $e->getMessage());
        return [];
    }
}

function marcarNotificacionVista($nota_id) {
    try {
        $pdo = getConnection();
        
        // Actualizar el estado de la nota a completada
        $sql = "UPDATE notas SET estado = 'completada' WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nota_id]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Error al marcar notificación como vista: " . $e->getMessage());
        return false;
    }
}

// Si es una petición AJAX para obtener notificaciones
if (isset($_GET['action']) && $_GET['action'] === 'obtener_notificaciones') {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    
    $notificaciones = obtenerNotificacionesPendientes($_SESSION['user_id']);
    
    header('Content-Type: application/json');
    echo json_encode($notificaciones);
    exit;
}

// Si es una petición AJAX para marcar como vista
if (isset($_POST['action']) && $_POST['action'] === 'marcar_vista') {
    session_start();
    
    if (!isset($_SESSION['user_id']) || !isset($_POST['nota_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos incompletos']);
        exit;
    }
    
    $resultado = marcarNotificacionVista($_POST['nota_id']);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $resultado]);
    exit;
}
?>