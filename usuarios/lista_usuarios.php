<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pdo = getConnection();

// Procesar acciones (activar/desactivar usuario)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int)$_GET['id'];
    
    if ($action === 'toggle_status' && $user_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
        $stmt->execute([$user_id]);
        header('Location: lista_usuarios.php?msg=status_updated');
        exit();
    }
}

// Obtener lista de usuarios
$stmt = $pdo->query("
    SELECT 
        id, 
        nombre, 
        email, 
        rol, 
        activo, 
        ultimo_acceso,
        fecha_creacion
    FROM usuarios 
    ORDER BY fecha_creacion DESC
");
$usuarios = $stmt->fetchAll();

$mensaje = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'status_updated':
            $mensaje = '<div class="alert alert-success">Estado del usuario actualizado correctamente.</div>';
            break;
        case 'user_added':
            $mensaje = '<div class="alert alert-success">Usuario agregado correctamente.</div>';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Librería Belén</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-book"></i> Librería Belén
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Dashboard</a>
                <a class="nav-link" href="../logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users-cog"></i> Gestión de Usuarios</h2>
                    <a href="nuevo_usuario.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </a>
                </div>
                
                <?php echo $mensaje; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Lista de Usuarios del Sistema</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Último Acceso</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>
                                            <?php if ($usuario['id'] == $_SESSION['user_id']): ?>
                                                <span class="badge bg-info ms-1">Tú</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td>
                                            <?php if ($usuario['rol'] === 'admin'): ?>
                                                <span class="badge bg-danger">Administrador</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Vendedor</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($usuario['activo']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($usuario['ultimo_acceso']) {
                                                echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']));
                                            } else {
                                                echo '<span class="text-muted">Nunca</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" 
                                                   class="btn btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <?php if ($usuario['id'] !== $_SESSION['user_id']): ?>
                                                <a href="?action=toggle_status&id=<?php echo $usuario['id']; ?>" 
                                                   class="btn btn-outline-<?php echo $usuario['activo'] ? 'warning' : 'success'; ?>" 
                                                   title="<?php echo $usuario['activo'] ? 'Desactivar' : 'Activar'; ?>"
                                                   onclick="return confirm('¿Está seguro de cambiar el estado de este usuario?')">
                                                    <i class="fas fa-<?php echo $usuario['activo'] ? 'ban' : 'check'; ?>"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <a href="ver_usuario.php?id=<?php echo $usuario['id']; ?>" 
                                                   class="btn btn-outline-info" title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (empty($usuarios)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay usuarios registrados</h5>
                            <p class="text-muted">Comience agregando el primer usuario al sistema.</p>
                            <a href="nuevo_usuario.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Agregar Primer Usuario
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Estadísticas rápidas -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total Usuarios</h6>
                                        <h4><?php echo count($usuarios); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Usuarios Activos</h6>
                                        <h4><?php echo count(array_filter($usuarios, function($u) { return $u['activo']; })); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user-check fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Administradores</h6>
                                        <h4><?php echo count(array_filter($usuarios, function($u) { return $u['rol'] === 'admin'; })); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user-shield fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>