<?php
/**
 * Interfaz para la creación de una nueva venta.
 * Muestra una lista de productos filtrables y un carrito de compras interactivo.
 * La lógica del carrito (agregar, quitar, calcular total) se maneja con JavaScript en el lado del cliente.
 */
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

// Obtener productos y clientes
$search = isset($_GET['search']) ? $_GET['search'] : '';
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$productos = getProductos($search, $categoria);
$clientes = getClientes();
$categorias = getCategorias();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Venta - Sistema de Ventas</title>
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
                    <h1 class="h2">Nueva Venta</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="limpiarCarrito()">
                            <i class="fas fa-trash"></i> Limpiar Carrito
                        </button>
                        <button type="button" class="btn btn-success" onclick="procesarVenta()" id="procesar-venta-btn">
                            <i class="fas fa-check"></i> Procesar Venta
                        </button>
                    </div>
                </div>

                <div class="row">
                    <!-- Productos -->
                    <div class="col-md-8">
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold">Productos Disponibles</h6>
                            </div>
                            <div class="card-body">
                                <!-- Filtros -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="search-productos" 
                                                   placeholder="Buscar productos..." value="<?php echo htmlspecialchars($search); ?>">
                                            <button class="btn btn-outline-secondary" type="button" onclick="buscarProductos()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" id="filter-categoria" onchange="buscarProductos()">
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
                                        <a href="nueva_venta.php" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    </div>
                                </div>

                                <!-- Lista de productos -->
                                <div class="row">
                                    <?php if (empty($productos)): ?>
                                        <div class="col-12">
                                            <div class="alert alert-info text-center">
                                                <i class="fas fa-info-circle"></i> No se encontraron productos.
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($productos as $producto): ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card product-card h-100" 
                                                     onclick="agregarAlCarrito(<?php echo $producto['id']; ?>, '<?php echo addslashes($producto['nombre']); ?>', <?php echo $producto['precio']; ?>, <?php echo $producto['stock']; ?>)">
                                                    <div class="card-body">
                                                        <h6 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h6>
                                                        <p class="card-text">
                                                            <small class="text-muted">
                                                                Código: <?php echo htmlspecialchars($producto['codigo']); ?><br>
                                                                Categoría: <?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?>
                                                            </small>
                                                        </p>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="h5 mb-0 text-primary">S/ <?php echo number_format($producto['precio'], 2); ?></span>
                                                            <span class="badge <?php echo ($producto['stock'] > 10) ? 'bg-success' : (($producto['stock'] > 0) ? 'bg-warning' : 'bg-danger'); ?>">
                                                                Stock: <?php echo $producto['stock']; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <?php if ($producto['stock'] == 0): ?>
                                                        <div class="card-footer bg-danger text-white text-center">
                                                            <small>Sin Stock</small>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="card-footer bg-light text-center">
                                                            <small class="text-success">
                                                                <i class="fas fa-plus"></i> Agregar al carrito
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carrito -->
                    <div class="col-md-4">
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold">Carrito de Compras</h6>
                            </div>
                            <div class="card-body">
                                <!-- Selección de cliente -->
                                <div class="mb-3">
                                    <label for="cliente_id" class="form-label">Cliente *</label>
                                    <select class="form-select" id="cliente_id" required>
                                        <option value="">Seleccionar cliente...</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?php echo $cliente['id']; ?>">
                                                <?php echo htmlspecialchars($cliente['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Items del carrito -->
                                <div id="carrito-items" class="mb-3">
                                    <p class="text-muted text-center">El carrito está vacío</p>
                                </div>

                                <!-- Total -->
                                <div class="border-top pt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Total:</h5>
                                        <h4 class="mb-0 text-primary" id="total-venta">S/ 0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones rápidas -->
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold">Acciones Rápidas</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="../clientes/nuevo_cliente.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-user-plus"></i> Nuevo Cliente
                                    </a>
                                    <a href="../productos/lista_productos.php" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-eye"></i> Ver Todos los Productos
                                    </a>
                                    <a href="lista_ventas.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-list"></i> Ver Ventas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Contenedor de alertas -->
    <div id="alert-container"></div>

    <!-- Modal de Confirmación de Venta -->
    <div class="modal fade" id="modalConfirmacionVenta" tabindex="-1" aria-labelledby="modalConfirmacionVentaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalConfirmacionVentaLabel">
                        <i class="fas fa-shopping-cart"></i> Confirmación de Venta
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Información del Cliente -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user"></i> Información del Cliente</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0" id="cliente-confirmacion">Cliente no seleccionado</p>
                        </div>
                    </div>

                    <!-- Detalle de Productos -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-list"></i> Detalle de la Venta</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Cant.</th>
                                            <th class="text-end">Precio Unit.</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detalle-productos-confirmacion">
                                        <!-- Se llenará dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de Totales -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-calculator"></i> Resumen de Totales</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal (sin IGV):</span>
                                        <span id="subtotal-sin-igv">S/ 0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>IGV (18%):</span>
                                        <span id="igv-monto">S/ 0.00</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <span>Cantidad de Items:</span>
                                        <span id="total-items">0</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total a Pagar:</strong>
                                        <strong class="text-primary" id="total-confirmacion">S/ 0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success" onclick="finalizarVenta()">
                        <i class="fas fa-check"></i> Finalizar Venta
                    </button>
                    <button type="button" class="btn btn-info" onclick="descargarBoleta()">
                        <i class="fas fa-file-pdf"></i> Descargar Boleta PDF
                    </button>
                    <button type="button" class="btn btn-warning" onclick="descargarFactura()">
                        <i class="fas fa-file-invoice"></i> Descargar Factura PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Función específica para buscar productos en esta página
        function buscarProductos() {
            const searchTerm = document.getElementById('search-productos').value;
            const categoria = document.getElementById('filter-categoria').value;
            
            let url = 'nueva_venta.php?';
            const params = new URLSearchParams();
            
            if (searchTerm) {
                params.append('search', searchTerm);
            }
            if (categoria) {
                params.append('categoria', categoria);
            }
            
            window.location.href = url + params.toString();
        }
        
        // Autocompletado en tiempo real para productos
        let searchTimeout;
        const searchInput = document.getElementById('search-productos');
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
                fetch(`../productos/buscar_productos.php?q=${encodeURIComponent(query)}`)
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
                    buscarProductos();
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
            } else if (e.key === 'Enter' && selectedIndex === -1) {
                // Si no hay selección, buscar normalmente
                buscarProductos();
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
        
        // Permitir buscar con Enter (mantener funcionalidad original)
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && searchResults.style.display === 'none') {
                buscarProductos();
            }
        });
    </script>
</body>
</html>