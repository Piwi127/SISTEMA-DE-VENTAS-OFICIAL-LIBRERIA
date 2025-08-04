<?php
session_start();
include_once '../includes/functions.php';
include_once '../config/database.php';

// Redirigir si el usuario no está logueado
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$db = getConnection();

$nombre = '';
$apellido = '';
$email = '';
$telefono = '';
$direccion = '';
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($nombre) || empty($apellido) || empty($email) || empty($telefono) || empty($direccion)) {
        $mensaje = 'Todos los campos son obligatorios.';
        $tipo_mensaje = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El formato del correo electrónico no es válido.';
        $tipo_mensaje = 'danger';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO clientes (nombre, apellido, email, telefono, direccion) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $email, $telefono, $direccion]);

            $mensaje = 'Cliente registrado exitosamente.';
            $tipo_mensaje = 'success';
            // Limpiar campos después de un registro exitoso
            $nombre = $apellido = $email = $telefono = $direccion = '';
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Código de error para entrada duplicada (ej. email único)
                $mensaje = 'El correo electrónico ya está registrado.';
            } else {
                $mensaje = 'Error al registrar el cliente: ' . $e->getMessage();
            }
            $tipo_mensaje = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include_once '../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="main-content-wrapper" style="min-height: calc(100vh - 56px); padding: 20px; margin-left: 0;">
            <main>
                <h1 class="mt-4">Registrar Nuevo Cliente</h1>
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-header">
                        Formulario de Registro
                    </div>
                    <div class="card-body">
                        <form action="nuevo_cliente.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($apellido); ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="3" required><?php echo htmlspecialchars($direccion); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Registrar Cliente</button>
                            <a href="lista_clientes.php" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>