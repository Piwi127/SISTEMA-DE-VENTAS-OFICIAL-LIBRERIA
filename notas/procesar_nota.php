<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    if (isset($_GET['accion'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error de conexión']);
        exit();
    } else {
        $_SESSION['message'] = 'Error de conexión a la base de datos';
        $_SESSION['message_type'] = 'danger';
        header('Location: lista_notas.php');
        exit();
    }
}

// Manejar solicitudes GET (ver nota)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['accion'])) {
    header('Content-Type: application/json');
    
    if ($_GET['accion'] == 'ver' && isset($_GET['id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM notas WHERE id = ? AND user_id = ?");
            $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
            $nota = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($nota) {
                echo json_encode(['success' => true, 'nota' => $nota]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Nota no encontrada']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener la nota']);
        }
    }
    exit();
}

// Manejar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? 'crear';
    
    try {
        switch ($accion) {
            case 'crear':
            default:
                $asunto = $_POST['asunto'];
                $cuerpo_mensaje = $_POST['cuerpo_mensaje'];
                $fecha_recordatorio = $_POST['fecha_recordatorio'];
                $user_id = $_SESSION['user_id'];
                
                $stmt = $pdo->prepare("INSERT INTO notas (user_id, asunto, cuerpo_mensaje, fecha_recordatorio) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $asunto, $cuerpo_mensaje, $fecha_recordatorio]);
                
                $_SESSION['message'] = 'Nota guardada exitosamente.';
                $_SESSION['message_type'] = 'success';
                break;
                
            case 'editar':
                $nota_id = $_POST['nota_id'];
                $asunto = $_POST['asunto'];
                $cuerpo_mensaje = $_POST['cuerpo_mensaje'];
                $fecha_recordatorio = $_POST['fecha_recordatorio'];
                
                $stmt = $pdo->prepare("UPDATE notas SET asunto = ?, cuerpo_mensaje = ?, fecha_recordatorio = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$asunto, $cuerpo_mensaje, $fecha_recordatorio, $nota_id, $_SESSION['user_id']]);
                
                if ($stmt->rowCount() > 0) {
                    $_SESSION['message'] = 'Nota actualizada exitosamente.';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'No se pudo actualizar la nota.';
                    $_SESSION['message_type'] = 'warning';
                }
                break;
                
            case 'cambiar_estado':
                header('Content-Type: application/json');
                $nota_id = $_POST['nota_id'];
                $estado = $_POST['estado'];
                
                $stmt = $pdo->prepare("UPDATE notas SET estado = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$estado, $nota_id, $_SESSION['user_id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado']);
                }
                exit();
                
            case 'eliminar':
                header('Content-Type: application/json');
                $nota_id = $_POST['nota_id'];
                
                $stmt = $pdo->prepare("DELETE FROM notas WHERE id = ? AND user_id = ?");
                $stmt->execute([$nota_id, $_SESSION['user_id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Nota eliminada']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la nota']);
                }
                exit();
        }
    } catch (PDOException $e) {
        if (in_array($accion, ['cambiar_estado', 'eliminar'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
            exit();
        } else {
            $_SESSION['message'] = 'Error al procesar la nota: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    }
    
    header('Location: lista_notas.php');
    exit();
}
?>