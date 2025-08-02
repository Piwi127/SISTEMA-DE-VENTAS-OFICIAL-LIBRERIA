<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$pdo = getConnection();
$error = '';
$success = '';

// Obtener ID del producto
$producto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($producto_id <= 0) {
    header('Location: lista_productos.php?error=invalid_id');
    exit();
}

// Obtener datos del producto
$stmt = $pdo->prepare("
    SELECT p.*, c.nombre as categoria_nombre 
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$producto_id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: lista_productos.php?error=not_found');
    exit();
}

// Obtener categorías para el select
$stmt = $pdo->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");
$categorias = $stmt->fetchAll();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = limpiarInput($_POST['codigo']);
    $nombre = limpiarInput($_POST['nombre']);
    $descripcion = limpiarInput($_POST['descripcion']);
    $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $stock_minimo = isset($_POST['stock_minimo']) ? (int)$_POST['stock_minimo'] : 5;
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($codigo) || empty($nombre) || $precio <= 0) {
        $error = 'Los campos código, nombre y precio son obligatorios.';
    } else {
        // Verificar si el código ya existe en otro producto
        $stmt = $pdo->prepare("SELECT id FROM productos WHERE codigo = ? AND id != ?");
        $stmt->execute([$codigo, $producto_id]);
        
        if ($stmt->fetch()) {
            $error = 'Ya existe otro producto con este código.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE productos SET 
                        codigo = ?, 
                        nombre = ?, 
                        descripcion = ?, 
                        categoria_id = ?, 
                        precio = ?, 
                        stock = ?, 
                        stock_minimo = ?, 
                        activo = ?,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$codigo, $nombre, $descripcion, $categoria_id, $precio, $stock, $stock_minimo, $activo, $producto_id])) {
                    $success = 'Producto actualizado correctamente.';
                    
                    // Actualizar los datos del producto para mostrar los cambios
                    $stmt = $pdo->prepare("
                        SELECT p.*, c.nombre as categoria_nombre 
                        FROM productos p 
                        LEFT JOIN categorias c ON p.categoria_id = c.id 
                        WHERE p.id = ?
                    ");
                    $stmt->execute([$producto_id]);
                    $producto = $stmt->fetch();
                } else {
                    $error = 'Error al actualizar el producto.';
                }
            } catch (PDOException $e) {
                $error = 'Error en la base de datos: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Librería Belén</title>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-edit"></i> Editar Producto: <?php echo htmlspecialchars($producto['nombre']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="codigo" class="form-label">
                                            <i class="fas fa-barcode"></i> Código del Producto *
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="codigo" 
                                               name="codigo" 
                                               value="<?php echo htmlspecialchars($producto['codigo']); ?>"
                                               required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="categoria_id" class="form-label">
                                            <i class="fas fa-tags"></i> Categoría
                                        </label>
                                        <select class="form-select" id="categoria_id" name="categoria_id">
                                            <option value="">Sin categoría</option>
                                            <?php foreach ($categorias as $categoria): ?>
                                                <option value="<?php echo $categoria['id']; ?>" 
                                                        <?php echo ($categoria['id'] == $producto['categoria_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label">
                                    <i class="fas fa-box"></i> Nombre del Producto *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">
                                    <i class="fas fa-align-left"></i> Descripción
                                </label>
                                <textarea class="form-control" 
                                          id="descripcion" 
                                          name="descripcion" 
                                          rows="3"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="precio" class="form-label">
                                            <i class="fas fa-dollar-sign"></i> Precio (S/) *
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="precio" 
                                               name="precio" 
                                               value="<?php echo $producto['precio']; ?>"
                                               step="0.01" 
                                               min="0.01"
                                               required>
                                        <div class="form-text">Precio incluye IGV</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">
                                            <i class="fas fa-cubes"></i> Stock Actual
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="stock" 
                                               name="stock" 
                                               value="<?php echo $producto['stock']; ?>"
                                               min="0">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="stock_minimo" class="form-label">
                                            <i class="fas fa-exclamation-triangle"></i> Stock Mínimo
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="stock_minimo" 
                                               name="stock_minimo" 
                                               value="<?php echo $producto['stock_minimo']; ?>"
                                               min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="activo" 
                                           name="activo" 
                                           <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="activo">
                                        <i class="fas fa-eye"></i> Producto activo (visible en ventas)
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="lista_productos.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver a la Lista
                                </a>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Información del producto -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información del Producto</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ID:</strong> <?php echo $producto['id']; ?></p>
                                <p><strong>Categoría:</strong> <?php echo $producto['categoria_nombre'] ?: 'Sin categoría'; ?></p>
                                <p><strong>Estado:</strong> 
                                    <span class="badge <?php echo $producto['activo'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Creado:</strong> <?php echo date('d/m/Y H:i', strtotime($producto['fecha_creacion'])); ?></p>
                                <?php if ($producto['fecha_actualizacion']): ?>
                                <p><strong>Última actualización:</strong> <?php echo date('d/m/Y H:i', strtotime($producto['fecha_actualizacion'])); ?></p>
                                <?php endif; ?>
                                <p><strong>Stock Status:</strong> 
                                    <?php if ($producto['stock'] <= 0): ?>
                                        <span class="badge bg-danger">Sin stock</span>
                                    <?php elseif ($producto['stock'] <= $producto['stock_minimo']): ?>
                                        <span class="badge bg-warning">Stock bajo</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Stock normal</span>
                                    <?php endif; ?>
                                </p>
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