<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Obtener parámetros de filtro
$search = isset($_GET['search']) ? $_GET['search'] : '';
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

// Obtener productos y categorías
$productos = getAllProductos($search, $categoria);
$categorias = getCategorias();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Sistema de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-book"></i> Librería Belén
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
                </span>
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Productos</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if ($user_role == 'admin'): ?>
                            <a href="nuevo_producto.php" class="btn btn-primary me-2">
                                <i class="fas fa-plus"></i> Nuevo Producto
                            </a>
                            <a href="categorias.php" class="btn btn-outline-secondary">
                                <i class="fas fa-tags"></i> Categorías
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Filtros de Búsqueda</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="search" class="form-label">Buscar Producto</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Buscar por nombre o código..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="categoria" class="form-label">Categoría</label>
                                    <select class="form-select" id="categoria" name="categoria">
                                        <option value="">Todas las categorías</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" 
                                                    <?php echo ($categoria == $cat['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                        <a href="lista_productos.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de productos -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Productos Registrados</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($productos)): ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i> No se encontraron productos con los filtros aplicados.
                                <?php if ($user_role == 'admin'): ?>
                                    <br><br>
                                    <a href="nuevo_producto.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Agregar Primer Producto
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Nombre</th>
                                            <th>Categoría</th>
                                            <th>Precio</th>
                                            <th>Stock</th>
                                            <th>Estado</th>
                                            <?php if ($user_role == 'admin'): ?>
                                                <th>Acciones</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productos as $producto): ?>
                                            <tr>
                                                <td>
                                                    <code><?php echo htmlspecialchars($producto['codigo']); ?></code>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                                    <?php if (!empty($producto['descripcion'])): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 50)); ?>...</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($producto['categoria_nombre']): ?>
                                                        <span class="badge bg-secondary">
                                                            <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin categoría</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                    <span class="h6 text-success">
                                        S/ <?php echo number_format($producto['precio'], 2); ?>
                                    </span>
                                </td>
                                                <td>
                                                    <?php
                                                    $stock = $producto['stock'];
                                                    $badge_class = 'bg-success';
                                                    if ($stock <= 5) {
                                                        $badge_class = 'bg-danger';
                                                    } elseif ($stock <= 10) {
                                                        $badge_class = 'bg-warning';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo $stock; ?> unidades
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($producto['activo']): ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php if ($user_role == 'admin'): ?>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary" 
                                                               data-bs-toggle="tooltip" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-info" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#stockModal" 
                                                                    data-producto-id="<?php echo $producto['id']; ?>"
                                                                    data-producto-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                                                    data-stock-actual="<?php echo $producto['stock']; ?>"
                                                                    title="Ajustar Stock">
                                                                <i class="fas fa-boxes"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-<?php echo $producto['activo'] ? 'warning' : 'success'; ?>" 
                                                                    onclick="toggleProducto(<?php echo $producto['id']; ?>, <?php echo $producto['activo'] ? 'false' : 'true'; ?>)"
                                                                    data-bs-toggle="tooltip" 
                                                                    title="<?php echo $producto['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                                                <i class="fas fa-<?php echo $producto['activo'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Productos
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count($productos); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-book fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Stock Bajo
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count(array_filter($productos, function($p) { return $p['stock'] <= 10; })); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Sin Stock
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count(array_filter($productos, function($p) { return $p['stock'] == 0; })); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Valor Inventario
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            S/ <?php echo number_format(array_sum(array_map(function($p) { return $p['precio'] * $p['stock']; }, $productos)), 2); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para ajustar stock -->
    <?php if ($user_role == 'admin'): ?>
    <div class="modal fade" id="stockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="stockForm">
                        <input type="hidden" id="producto_id" name="producto_id">
                        <div class="mb-3">
                            <label class="form-label">Producto:</label>
                            <p id="producto_nombre" class="fw-bold"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Actual:</label>
                            <p id="stock_actual" class="fw-bold text-info"></p>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo_stock" class="form-label">Nuevo Stock:</label>
                            <input type="number" class="form-control" id="nuevo_stock" name="nuevo_stock" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo del Ajuste:</label>
                            <select class="form-select" id="motivo" name="motivo" required>
                                <option value="">Seleccionar motivo...</option>
                                <option value="entrada">Entrada de mercancía</option>
                                <option value="correccion">Corrección de inventario</option>
                                <option value="devolucion">Devolución</option>
                                <option value="perdida">Pérdida/Daño</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarStock()">Actualizar Stock</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Modal de stock
        const stockModal = document.getElementById('stockModal');
        if (stockModal) {
            stockModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const productoId = button.getAttribute('data-producto-id');
                const productoNombre = button.getAttribute('data-producto-nombre');
                const stockActual = button.getAttribute('data-stock-actual');
                
                document.getElementById('producto_id').value = productoId;
                document.getElementById('producto_nombre').textContent = productoNombre;
                document.getElementById('stock_actual').textContent = stockActual + ' unidades';
                document.getElementById('nuevo_stock').value = stockActual;
            });
        }
        
        // Función para actualizar stock
        function actualizarStock() {
            const form = document.getElementById('stockForm');
            const formData = new FormData(form);
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const data = {
                producto_id: document.getElementById('producto_id').value,
                nuevo_stock: document.getElementById('nuevo_stock').value,
                motivo: document.getElementById('motivo').value
            };
            
            // Deshabilitar botón mientras se procesa
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
            
            fetch('actualizar_stock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.text().then(text => {
                    if (!text.trim()) {
                        throw new Error('Respuesta vacía del servidor');
                    }
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Respuesta no válida:', text);
                        throw new Error('Respuesta no es JSON válido');
                    }
                });
            })
            .then(result => {
                if (result.success) {
                    showAlert(result.message, 'success');
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert(result.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error al actualizar el stock', 'error');
            })
            .finally(() => {
                // Restaurar botón
                btn.disabled = false;
                btn.innerHTML = 'Actualizar Stock';
                bootstrap.Modal.getInstance(stockModal).hide();
            });
        }
        
        // Función para activar/desactivar producto
        function toggleProducto(productoId, nuevoEstado) {
            const accion = nuevoEstado ? 'activar' : 'desactivar';
            if (confirm(`¿Está seguro de que desea ${accion} este producto?`)) {
                // Aquí iría la petición AJAX para cambiar el estado
                showAlert(`Funcionalidad de ${accion} producto en desarrollo`, 'info');
            }
        }
        
        // Autocompletado en tiempo real
        let searchTimeout;
        const searchInput = document.getElementById('search');
        const searchResults = document.createElement('div');
        searchResults.className = 'autocomplete-results';
        searchResults.style.cssText = `
             position: fixed;
             background: white;
             border: 1px solid #ddd;
             border-radius: 0 0 8px 8px;
             max-height: 300px;
             overflow-y: auto;
             z-index: 99999;
             display: none;
             box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
             border-top: none;
         `;
         
         // Función para posicionar el desplegable
         function posicionarDesplegable() {
             const rect = searchInput.getBoundingClientRect();
             searchResults.style.top = (rect.bottom) + 'px';
             searchResults.style.left = rect.left + 'px';
             searchResults.style.width = rect.width + 'px';
         }
        
        // Agregar el contenedor de resultados al body
         document.body.appendChild(searchResults);
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Limpiar timeout anterior
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            // Esperar 300ms antes de buscar
            searchTimeout = setTimeout(() => {
                fetch(`buscar_productos.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.productos.length > 0) {
                        mostrarResultados(data.productos);
                    } else {
                        searchResults.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error en búsqueda:', error);
                    searchResults.style.display = 'none';
                });
            }, 300);
        });
        
        function mostrarResultados(productos) {
             searchResults.innerHTML = '';
             posicionarDesplegable();
            
            productos.forEach(producto => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.style.cssText = `
                    padding: 10px;
                    border-bottom: 1px solid #eee;
                    cursor: pointer;
                    transition: background-color 0.2s;
                `;
                
                item.innerHTML = `
                    <div style="font-weight: bold;">${producto.nombre}</div>
                    <div style="font-size: 0.9em; color: #666;">
                        Código: ${producto.codigo} | 
                        Precio: S/ ${producto.precio} | 
                        Stock: ${producto.stock} | 
                        Categoría: ${producto.categoria}
                    </div>
                `;
                
                // Hover effect
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = 'white';
                });
                
                // Click para seleccionar
                item.addEventListener('click', function() {
                    searchInput.value = producto.nombre;
                    searchResults.style.display = 'none';
                    // Enviar formulario automáticamente
                    searchInput.closest('form').submit();
                });
                
                searchResults.appendChild(item);
            });
            
            searchResults.style.display = 'block';
        }
        
        // Ocultar resultados al hacer click fuera
         document.addEventListener('click', function(e) {
             if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                 searchResults.style.display = 'none';
             }
         });
         
         // Reposicionar al hacer scroll o redimensionar
         window.addEventListener('scroll', function() {
             if (searchResults.style.display === 'block') {
                 posicionarDesplegable();
             }
         });
         
         window.addEventListener('resize', function() {
             if (searchResults.style.display === 'block') {
                 posicionarDesplegable();
             }
         });
        
        // Manejar teclas de navegación
        searchInput.addEventListener('keydown', function(e) {
            const items = searchResults.querySelectorAll('.autocomplete-item');
            let selectedIndex = -1;
            
            // Encontrar item seleccionado actual
            items.forEach((item, index) => {
                if (item.style.backgroundColor === 'rgb(0, 123, 255)') {
                    selectedIndex = index;
                }
            });
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection(items, selectedIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, 0);
                updateSelection(items, selectedIndex);
            } else if (e.key === 'Enter' && selectedIndex >= 0) {
                e.preventDefault();
                items[selectedIndex].click();
            } else if (e.key === 'Escape') {
                searchResults.style.display = 'none';
            }
        });
        
        function updateSelection(items, selectedIndex) {
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.style.backgroundColor = '#007bff';
                    item.style.color = 'white';
                } else {
                    item.style.backgroundColor = 'white';
                    item.style.color = 'black';
                }
            });
        }
        
        // Función para activar/desactivar productos (solo admin)
        function toggleProducto(productoId, nuevoEstado) {
            const accion = nuevoEstado ? 'activar' : 'desactivar';
            
            if (confirm(`¿Está seguro de que desea ${accion} este producto?`)) {
                // Mostrar indicador de carga
                const button = event.target.closest('button');
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;
                
                // Realizar petición AJAX
                fetch('toggle_producto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `producto_id=${productoId}&activo=${nuevoEstado}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        // Recargar la página después de 1.5 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message, 'error');
                        // Restaurar botón
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error al procesar la solicitud', 'error');
                    // Restaurar botón
                    button.innerHTML = originalContent;
                    button.disabled = false;
                });
            }
        }
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>