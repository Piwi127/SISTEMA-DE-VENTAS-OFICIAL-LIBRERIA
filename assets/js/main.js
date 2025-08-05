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
                                <small class="text-muted">S/ ${item.precio.toFixed(2)} c/u</small>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column align-items-center">
                                    <label class="form-label mb-1 small">Cantidad</label>
                                    <input type="number" class="form-control text-center" 
                                           value="${item.cantidad}" min="1" max="${item.stock}"
                                           style="width: 80px;"
                                           onchange="actualizarCantidad(${item.id}, this.value)"
                                           oninput="actualizarCantidad(${item.id}, this.value)">
                                    <small class="text-muted mt-1">Max: ${item.stock}</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <strong>S/ ${item.subtotal.toFixed(2)}</strong>
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
        totalContainer.textContent = `S/ ${totalVenta.toFixed(2)}`;
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
    // Usar alert simple para debug
    console.log(`ALERT [${type}]: ${message}`);
    alert(`[${type.toUpperCase()}] ${message}`);
    
    // Intentar también mostrar en contenedor si existe
    try {
        let alertContainer = document.getElementById('alert-container');
        if (!alertContainer) {
            // Crear contenedor de alertas si no existe
            const container = document.createElement('div');
            container.id = 'alert-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
            container.style.maxWidth = '400px';
            document.body.appendChild(container);
            alertContainer = container;
        }
        
        const alertId = 'alert-' + Date.now();
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert" style="margin-bottom: 10px;">
                <i class="fas fa-${getAlertIcon(type)}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.innerHTML += alertHtml;
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                alertElement.remove();
            }
        }, 5000);
    } catch (error) {
        console.error('Error en showAlert:', error);
    }
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
    
    // Mostrar modal de confirmación
    mostrarModalConfirmacion();
}

// Nueva función para mostrar el modal de confirmación
function mostrarModalConfirmacion() {
    // Obtener información del cliente seleccionado
    const clienteSelect = document.getElementById('cliente_id');
    const clienteNombre = clienteSelect.options[clienteSelect.selectedIndex].text;
    
    // Actualizar información del cliente en el modal
    document.getElementById('cliente-confirmacion').textContent = clienteNombre;
    
    // Llenar detalle de productos
    const detalleProductos = document.getElementById('detalle-productos-confirmacion');
    detalleProductos.innerHTML = '';
    
    let totalItems = 0;
    carrito.forEach(item => {
        totalItems += item.cantidad;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.nombre}</td>
            <td class="text-center">${item.cantidad}</td>
            <td class="text-end">S/ ${item.precio.toFixed(2)}</td>
            <td class="text-end">S/ ${item.subtotal.toFixed(2)}</td>
        `;
        detalleProductos.appendChild(row);
    });
    
    // Calcular totales (los precios ya incluyen IGV)
    const subtotalSinIGV = totalVenta / 1.18; // Dividir entre 1.18 para obtener base sin IGV
    const igvMonto = totalVenta - subtotalSinIGV;
    
    // Actualizar totales en el modal
    document.getElementById('subtotal-sin-igv').textContent = `S/ ${subtotalSinIGV.toFixed(2)}`;
    document.getElementById('igv-monto').textContent = `S/ ${igvMonto.toFixed(2)}`;
    document.getElementById('total-items').textContent = totalItems;
    document.getElementById('total-confirmacion').textContent = `S/ ${totalVenta.toFixed(2)}`;
    
    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmacionVenta'));
    modal.show();
}

// Función para finalizar la venta
function finalizarVenta() {
    const clienteId = document.getElementById('cliente_id').value;
    
    // Enviar datos al servidor
    const formData = new FormData();
    formData.append('cliente_id', clienteId);
    formData.append('productos', JSON.stringify(carrito));
    formData.append('total', totalVenta);
    
    fetch('procesar_venta.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmacionVenta'));
            modal.hide();
            
            showAlert('Venta procesada exitosamente', 'success');
            
            // Guardar ID de venta para descargas
            window.ultimaVentaId = data.venta_id;
            
            limpiarCarrito();
            
            // Redirigir después de un momento
            setTimeout(() => {
                window.location.href = 'lista_ventas.php';
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

// Función para descargar boleta PDF
function descargarBoleta() {
    if (!window.ultimaVentaId) {
        showAlert('Debe finalizar la venta primero', 'warning');
        return;
    }
    
    // Crear formulario temporal para descarga
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'generar_boleta_pdf.php';
    form.target = '_blank';
    
    const ventaIdInput = document.createElement('input');
    ventaIdInput.type = 'hidden';
    ventaIdInput.name = 'venta_id';
    ventaIdInput.value = window.ultimaVentaId;
    
    form.appendChild(ventaIdInput);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    showAlert('Generando boleta PDF...', 'info');
}

// Función para descargar factura PDF
function descargarFactura() {
    if (!window.ultimaVentaId) {
        showAlert('Debe finalizar la venta primero', 'warning');
        return;
    }
    
    // Crear formulario temporal para descarga
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'generar_factura_pdf.php';
    form.target = '_blank';
    
    const ventaIdInput = document.createElement('input');
    ventaIdInput.type = 'hidden';
    ventaIdInput.name = 'venta_id';
    ventaIdInput.value = window.ultimaVentaId;
    
    form.appendChild(ventaIdInput);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    showAlert('Generando factura PDF...', 'info');
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