<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $asunto = $_POST['asunto'];
    $cuerpo_mensaje = $_POST['cuerpo_mensaje'];
    $fecha_recordatorio = $_POST['fecha_recordatorio'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $stmt = $pdo->prepare("INSERT INTO notas (user_id, asunto, cuerpo_mensaje, fecha_recordatorio) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $asunto, $cuerpo_mensaje, $fecha_recordatorio]);

        $_SESSION['message'] = 'Nota guardada exitosamente.';
        $_SESSION['message_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Error al guardar la nota: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }

    header('Location: lista_notas.php');
    exit();
}
?>