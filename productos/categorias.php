<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'crear') {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        
        if (!empty($nombre)) {
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
                if ($stmt->execute([$nombre, $descripcion])) {
                    $_SESSION['message'] = 'Categoría creada exitosamente';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Error al crear la categoría';
                    $_SESSION['message_type'] = 'danger';
                }
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Error: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            $_SESSION['message'] = 'El nombre de la categoría es obligatorio';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: categorias.php');
        exit();
    }
    
    if ($action === 'editar') {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        
        if (!empty($nombre) && $id > 0) {
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
                if ($stmt->execute([$nombre, $descripcion, $id])) {
                    $_SESSION['message'] = 'Categoría actualizada exitosamente';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Error al actualizar la categoría';
                    $_SESSION['message_type'] = 'danger';
                }
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Error: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
        header('Location: categorias.php');
        exit();
    }
    
    if ($action === 'eliminar') {
        $id = intval($_POST['id']);
        
        if ($id > 0) {
            try {
                $pdo = getConnection();
                // Verificar si hay productos en esta categoría
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = ?");
                $stmt->execute([$id]);
                $count = $stmt->fetchColumn();
                
                if ($count > 0) {
                    $_SESSION['message'] = 'No se puede eliminar la categoría porque tiene productos asociados';
                    $_SESSION['message_type'] = 'warning';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
                    if ($stmt->execute([$id])) {
                        $_SESSION['message'] = 'Categoría eliminada exitosamente';
                        $_SESSION['message_type'] = 'success';
                    } else {
                        $_SESSION['message'] = 'Error al eliminar la categoría';
                        $_SESSION['message_type'] = 'danger';
                    }
                }
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Error: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
        header('Location: categorias.php');
        exit();
    }
}

// Obtener categorías
try {
    $pdo = getConnection();
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as total_productos 
        FROM categorias c 
        LEFT JOIN productos p ON c.id = p.categoria_id 
        GROUP BY c.id 
        ORDER BY c.nombre
    ");
    $categorias = $stmt->fetchAll();
} catch (PDOException $e) {
    $categorias = [];
    $error_message = "Error al cargar las categorías: " . $e->getMessage();
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestión de Categorías</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="lista_productos.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Productos
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario para nueva categoría -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-plus-circle"></i> Nueva Categoría
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="crear">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre de la Categoría <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" maxlength="255">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Crear
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de categorías -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-list"></i> Categorías Existentes
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($categorias)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay categorías registradas</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Total Productos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <tr>
                                            <td><?php echo $categoria['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($categoria['nombre']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($categoria['descripcion'] ?: 'Sin descripción'); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $categoria['total_productos']; ?> productos
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" 
                                                        onclick="editarCategoria(<?php echo $categoria['id']; ?>, '<?php echo addslashes($categoria['nombre']); ?>', '<?php echo addslashes($categoria['descripcion']); ?>')" 
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($categoria['total_productos'] == 0): ?>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="eliminarCategoria(<?php echo $categoria['id']; ?>, '<?php echo addslashes($categoria['nombre']); ?>')" 
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled title="No se puede eliminar (tiene productos)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal para editar categoría -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">
                    <i class="fas fa-edit"></i> Editar Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="editar">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label">Nombre de la Categoría <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required maxlength="100">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_descripcion" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="edit_descripcion" name="descripcion" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar la categoría <strong id="delete_nombre"></strong>?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function editarCategoria(id, nombre, descripcion) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_descripcion').value = descripcion || '';
    
    const modal = new bootstrap.Modal(document.getElementById('editarModal'));
    modal.show();
}

function eliminarCategoria(id, nombre) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_nombre').textContent = nombre;
    
    const modal = new bootstrap.Modal(document.getElementById('eliminarModal'));
    modal.show();
}
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>