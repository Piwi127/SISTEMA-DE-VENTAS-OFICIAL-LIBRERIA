<?php
/**
 * Página para la creación de un nuevo producto.
 * Realiza validaciones de los datos del formulario antes de insertarlos en la base de datos.
 * Solo accesible para usuarios con el rol de 'admin'.
 */
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

// Obtener categorías para el select
$categorias = getCategorias();

// Procesar formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $codigo = trim($_POST['codigo']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $categoria_id = intval($_POST['categoria_id']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    $errores = [];
    
    // Validaciones
    if (empty($nombre)) {
        $errores[] = "El nombre del producto es obligatorio";
    }
    
    if (empty($codigo)) {
        $errores[] = "El código del producto es obligatorio";
    }
    
    if ($precio <= 0) {
        $errores[] = "El precio debe ser mayor a 0";
    }
    
    if ($stock < 0) {
        $errores[] = "El stock no puede ser negativo";
    }
    
    if ($categoria_id <= 0) {
        $errores[] = "Debe seleccionar una categoría válida";
    } else {
        // Verificar si la categoría_id existe en la tabla categorias
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT id FROM categorias WHERE id = ?");
            $stmt->execute([$categoria_id]);
            if (!$stmt->fetch()) {
                $errores[] = "La categoría seleccionada no existe.";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al verificar la categoría: " . $e->getMessage();
        }
    }
    
    // Verificar si el código ya existe
    if (empty($errores)) {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT id FROM productos WHERE codigo = ?");
            $stmt->execute([$codigo]);
            if ($stmt->fetch()) {
                $errores[] = "Ya existe un producto con ese código";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al verificar el código: " . $e->getMessage();
        }
    }
    
    // Si no hay errores, insertar el producto
    if (empty($errores)) {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO productos (nombre, codigo, descripcion, precio, stock, categoria_id, activo, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            if ($stmt->execute([$nombre, $codigo, $descripcion, $precio, $stock, $categoria_id, $activo])) {
                $_SESSION['message'] = 'Producto creado exitosamente';
                $_SESSION['message_type'] = 'success';
                header('Location: lista_productos.php');
                exit();
            } else {
                $errores[] = "Error al crear el producto";
            }
        } catch (PDOException $e) {
            $errores[] = "Error de base de datos: " . $e->getMessage();
        }
    }
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
                <h1 class="h2">Nuevo Producto</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="lista_productos.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a la Lista
                    </a>
                </div>
            </div>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong>
                    <ul class="mb-0">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-plus-circle"></i> Agregar Nuevo Producto
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" 
                                                   required maxlength="255">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="codigo" class="form-label">Código del Producto <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="codigo" name="codigo" 
                                                   value="<?php echo isset($_POST['codigo']) ? htmlspecialchars($_POST['codigo']) : ''; ?>" 
                                                   required maxlength="50">
                                            <div class="form-text">Código único para identificar el producto</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                              maxlength="500"><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                                    <div class="form-text">Descripción opcional del producto (máximo 500 caracteres)</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="precio" class="form-label">Precio <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">S/</span>
                                                <input type="number" class="form-control" id="precio" name="precio" 
                                                       value="<?php echo isset($_POST['precio']) ? $_POST['precio'] : ''; ?>" 
                                                       step="0.01" min="0.01" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="stock" class="form-label">Stock Inicial <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="stock" name="stock" 
                                                   value="<?php echo isset($_POST['stock']) ? $_POST['stock'] : '0'; ?>" 
                                                   min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                                <option value="">Seleccionar categoría...</option>
                                                <?php foreach ($categorias as $categoria): ?>
                                                    <option value="<?php echo $categoria['id']; ?>" 
                                                            <?php echo (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                               <?php echo (!isset($_POST['activo']) || $_POST['activo']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="activo">
                                            Producto activo
                                        </label>
                                        <div class="form-text">Los productos inactivos no aparecerán en las ventas</div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="lista_productos.php" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Producto
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Información Importante</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Campos Obligatorios:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Nombre del producto</li>
                                        <li><i class="fas fa-check text-success"></i> Código único</li>
                                        <li><i class="fas fa-check text-success"></i> Precio</li>
                                        <li><i class="fas fa-check text-success"></i> Stock inicial</li>
                                        <li><i class="fas fa-check text-success"></i> Categoría</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Recomendaciones:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-info-circle text-info"></i> Use códigos únicos y descriptivos</li>
                                        <li><i class="fas fa-info-circle text-info"></i> Verifique el precio antes de guardar</li>
                                        <li><i class="fas fa-info-circle text-info"></i> El stock se puede ajustar posteriormente</li>
                                        <li><i class="fas fa-info-circle text-info"></i> La descripción ayuda en las búsquedas</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script para validación en tiempo real -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const codigoInput = document.getElementById('codigo');
    const nombreInput = document.getElementById('nombre');
    
    // Convertir código a mayúsculas y eliminar espacios
    codigoInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/\s/g, '');
    });
    
    // Validar que el precio sea positivo
    const precioInput = document.getElementById('precio');
    precioInput.addEventListener('input', function() {
        if (parseFloat(this.value) <= 0) {
            this.setCustomValidity('El precio debe ser mayor a 0');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Validar que el stock no sea negativo
    const stockInput = document.getElementById('stock');
    stockInput.addEventListener('input', function() {
        if (parseInt(this.value) < 0) {
            this.setCustomValidity('El stock no puede ser negativo');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>