// JavaScript principal para el Sistema de Ventas

// Variables globales
let carrito = [];
let totalVenta = 0;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Inicializar modales
    initializeModals();
    
    // Cargar carrito desde localStorage si existe
    loadCarritoFromStorage();
    
    // Actualizar display del carrito
    updateCarritoDisplay();
});

// Funciones del carrito de compras
function agregarAlCarrito(productoId, nombre, precio, stock) {
    // Verificar si el producto ya está en el carrito
    const existingItem = carrito.find(item => item.id === productoId);
    
    if (existingItem) {
        // Si ya existe, incrementar cantidad si hay stock
        if (existingItem.cantidad < stock) {
            existingItem.cantidad++;
            existingItem.subtotal = existingItem.cantidad * existingItem.precio;
        } else {
            showAlert('No hay suficiente stock disponible', 'warning');
            return;
        }
    } else {
        // Si no existe, agregar nuevo item
        carrito.push({
            id: productoId,
            nombre: nombre,
            precio: parseFloat(precio),
            cantidad: 1,
            stock: stock,
            subtotal: parseFloat(precio)
        });
    }
    
    // Actualizar display y guardar en localStorage
    updateCarritoDisplay();
    saveCarritoToStorage();
    showAlert('Producto agregado al carrito', 'success');
}

function removerDelCarrito(productoId) {
    carrito = carrito.filter(item => item.id !== productoId);
    updateCarritoDisplay();
    saveCarritoToStorage();
    showAlert('Producto removido del carrito', 'info');
}

function actualizarCantidad(productoId, nuevaCantidad) {
    const item = carrito.find(item => item.id === productoId);
    if (item) {
        if (nuevaCantidad > 0 && nuevaCantidad <= item.stock) {
            item.cantidad = parseInt(nuevaCantidad);
            item.subtotal = item.cantidad * item.precio;
            updateCarritoDisplay();
            saveCarritoToStorage();
        } else if (nuevaCantidad > item.stock) {
            showAlert('Cantidad excede el stock disponible', 'warning');
        }
    }
}

function limpiarCarrito() {
    carrito = [];
    updateCarritoDisplay();
    saveCarritoToStorage();
    showAlert('Carrito limpiado', 'info');
}

function updateCarritoDisplay() {
    const carritoContainer = document.getElementById('carrito-items');
    const totalContainer = document.getElementById('total-venta');
    const carritoCount = document.getElementById('carrito-count');
    
    if (carritoContainer) {
        carritoContainer.innerHTML = '';
        
        if (carrito.length === 0) {
            carritoContainer.innerHTML = '<p class="text-muted text-center">El carrito está vacío</p>';
        } else {
            carrito.forEach(item => {
                const itemHtml = `
                    <div class="cart-item">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-1">${item.nombre}</h6>
                                <small class="text-muted">$${item.precio.toFixed(2)} c/u</small>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="actualizarCantidad(${item.id}, ${item.cantidad - 1})">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control text-center" 
                                           value="${item.cantidad}" min="1" max="${item.stock}"
                                           onchange="actualizarCantidad(${item.id}, this.value)">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="actualizarCantidad(${item.id}, ${item.cantidad + 1})">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <strong>$${item.subtotal.toFixed(2)}</strong>
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="removerDelCarrito(${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                carritoContainer.innerHTML += itemHtml;
            });
        }
    }
    
    // Calcular total
    totalVenta = carrito.reduce((total, item) => total + item.subtotal, 0);
    
    if (totalContainer) {
        totalContainer.textContent = `$${totalVenta.toFixed(2)}`;
    }
    
    if (carritoCount) {
        carritoCount.textContent = carrito.length;
    }
}

// Funciones de localStorage
function saveCarritoToStorage() {
    localStorage.setItem('carrito_ventas', JSON.stringify(carrito));
}

function loadCarritoFromStorage() {
    const savedCarrito = localStorage.getItem('carrito_ventas');
    if (savedCarrito) {
        carrito = JSON.parse(savedCarrito);
    }
}

// Función para mostrar alertas
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        // Crear contenedor de alertas si no existe
        const container = document.createElement('div');
        container.id = 'alert-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    const alertId = 'alert-' + Date.now();
    const alertHtml = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${getAlertIcon(type)}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.getElementById('alert-container').innerHTML += alertHtml;
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
            alertElement.remove();
        }
    }, 5000);
}

function getAlertIcon(type) {
    const icons = {
        'success': 'check-circle',
        'danger': 'exclamation-triangle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Función para buscar productos
function buscarProductos() {
    const searchTerm = document.getElementById('search-productos').value;
    const categoria = document.getElementById('filter-categoria').value;
    
    // Construir URL con parámetros
    let url = window.location.pathname + '?';
    const params = new URLSearchParams();
    
    if (searchTerm) {
        params.append('search', searchTerm);
    }
    if (categoria) {
        params.append('categoria', categoria);
    }
    
    window.location.href = url + params.toString();
}

// Función para confirmar eliminación
function confirmarEliminacion(mensaje = '¿Está seguro de que desea eliminar este elemento?') {
    return confirm(mensaje);
}

// Función para validar formularios
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Función para formatear números como moneda
function formatearMoneda(cantidad) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(cantidad);
}

// Función para inicializar modales
function initializeModals() {
    // Limpiar carrito al cerrar modal de nueva venta
    const ventaModal = document.getElementById('nuevaVentaModal');
    if (ventaModal) {
        ventaModal.addEventListener('hidden.bs.modal', function () {
            // No limpiar automáticamente, dejar que el usuario decida
        });
    }
}

// Función para procesar venta
function procesarVenta() {
    if (carrito.length === 0) {
        showAlert('El carrito está vacío', 'warning');
        return;
    }
    
    const clienteId = document.getElementById('cliente_id').value;
    if (!clienteId) {
        showAlert('Debe seleccionar un cliente', 'warning');
        return;
    }
    
    // Mostrar confirmación
    if (confirm(`¿Confirmar venta por $${totalVenta.toFixed(2)}?`)) {
        // Enviar datos al servidor
        const formData = new FormData();
        formData.append('cliente_id', clienteId);
        formData.append('productos', JSON.stringify(carrito));
        formData.append('total', totalVenta);
        
        fetch('ventas/procesar_venta.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Venta procesada exitosamente', 'success');
                limpiarCarrito();
                // Redirigir o actualizar página
                setTimeout(() => {
                    window.location.href = 'ventas/lista_ventas.php';
                }, 2000);
            } else {
                showAlert('Error al procesar la venta: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            showAlert('Error de conexión', 'danger');
            console.error('Error:', error);
        });
    }
}

// Función para imprimir
function imprimirPagina() {
    window.print();
}

// Función para exportar a PDF (requiere jsPDF)
function exportarPDF(elementId, filename = 'documento.pdf') {
    // Esta función requiere la librería jsPDF
    // Se puede implementar cuando se necesite
    showAlert('Función de exportar PDF en desarrollo', 'info');
}

// Event listeners para teclas de acceso rápido
document.addEventListener('keydown', function(e) {
    // Ctrl + N para nueva venta
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        const nuevaVentaBtn = document.getElementById('nueva-venta-btn');
        if (nuevaVentaBtn) {
            nuevaVentaBtn.click();
        }
    }
    
    // Escape para limpiar carrito
    if (e.key === 'Escape') {
        const modal = document.querySelector('.modal.show');
        if (!modal && carrito.length > 0) {
            if (confirm('¿Limpiar carrito?')) {
                limpiarCarrito();
            }
        }
    }
});