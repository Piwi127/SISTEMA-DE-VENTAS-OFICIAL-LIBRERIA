<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Nueva Venta Libre';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-cash-register me-2"></i>
                        Nueva Venta Libre
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Venta Libre:</strong> Utilice este formulario para registrar ventas de productos o servicios especiales como cotizaciones, trabajos manuales, investigaciones, etc.
                    </div>

                    <form id="formVentaLibre" method="POST" action="procesar_venta_libre.php">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="motivo_venta" class="form-label">
                                        <i class="fas fa-clipboard-list me-1"></i>
                                        Motivo de Venta <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="motivo_venta" name="motivo_venta" rows="3" 
                                              placeholder="Ej: Trabajo de investigación, cotización especial, servicio de encuadernación, etc." 
                                              required></textarea>
                                    <div class="invalid-feedback">
                                        Por favor, ingrese el motivo de la venta.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">
                                        <i class="fas fa-align-left me-1"></i>
                                        Descripción Detallada <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                              placeholder="Describa detalladamente el producto o servicio proporcionado" 
                                              required></textarea>
                                    <div class="invalid-feedback">
                                        Por favor, ingrese una descripción detallada.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">
                                        <i class="fas fa-sort-numeric-up me-1"></i>
                                        Cantidad <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                           value="1" min="1" step="1" required>
                                    <div class="invalid-feedback">
                                        La cantidad debe ser mayor a 0.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="precio_unitario" class="form-label">
                                        <i class="fas fa-dollar-sign me-1"></i>
                                        Precio Unitario <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="precio_unitario" name="precio_unitario" 
                                           step="0.01" min="0.01" placeholder="0.00" required>
                                    <div class="invalid-feedback">
                                        El precio debe ser mayor a 0.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="total" class="form-label">
                                        <i class="fas fa-calculator me-1"></i>
                                        Total <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="total" name="total" 
                                           step="0.01" min="0.01" placeholder="0.00" readonly>
                                    <div class="invalid-feedback">
                                        El total debe ser mayor a 0.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="metodo_pago" class="form-label">
                                        <i class="fas fa-credit-card me-1"></i>
                                        Método de Pago
                                    </label>
                                    <select class="form-select" id="metodo_pago" name="metodo_pago">
                                        <option value="efectivo" selected>Efectivo</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="transferencia">Transferencia</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="notas" class="form-label">
                                        <i class="fas fa-sticky-note me-1"></i>
                                        Notas Adicionales
                                    </label>
                                    <textarea class="form-control" id="notas" name="notas" rows="2" 
                                              placeholder="Información adicional sobre la venta (opcional)"></textarea>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted">Campos marcados con <span class="text-danger">*</span> son obligatorios</span>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button type="submit" class="btn btn-primary btn-lg" id="btnGenerar">
                                            <i class="fas fa-cash-register me-2"></i>
                                            Generar Venta
                                        </button>
                                        <button type="button" class="btn btn-success btn-lg" id="btnImprimir" disabled>
                                            <i class="fas fa-print me-2"></i>
                                            Imprimir Boleta
                                        </button>
                                        <button type="button" class="btn btn-info btn-lg" id="btnDescargar" disabled>
                                            <i class="fas fa-download me-2"></i>
                                            Descargar Boleta
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Venta Registrada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">¡Venta registrada exitosamente!</h4>
                    <p class="mb-0">Número de venta: <strong id="numeroVenta"></strong></p>
                    <p class="text-muted">Ahora puede imprimir o descargar la boleta</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="nuevaVenta()">Nueva Venta</button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let ventaId = null;

// Calcular total automáticamente
function calcularTotal() {
    const cantidad = parseFloat(document.getElementById('cantidad').value) || 0;
    const precioUnitario = parseFloat(document.getElementById('precio_unitario').value) || 0;
    const total = cantidad * precioUnitario;
    document.getElementById('total').value = total.toFixed(2);
}

// Event listeners para cálculo automático
document.getElementById('cantidad').addEventListener('input', calcularTotal);
document.getElementById('precio_unitario').addEventListener('input', calcularTotal);

// Validación del formulario
document.getElementById('formVentaLibre').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (this.checkValidity()) {
        procesarVenta();
    } else {
        this.classList.add('was-validated');
    }
});

// Procesar venta
function procesarVenta() {
    const formData = new FormData(document.getElementById('formVentaLibre'));
    const btnGenerar = document.getElementById('btnGenerar');
    
    // Deshabilitar botón y mostrar loading
    btnGenerar.disabled = true;
    btnGenerar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
    
    fetch('procesar_venta_libre.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ventaId = data.venta_id;
            document.getElementById('numeroVenta').textContent = data.numero_venta;
            
            // Habilitar botones de boleta
            document.getElementById('btnImprimir').disabled = false;
            document.getElementById('btnDescargar').disabled = false;
            
            // Mostrar modal de confirmación
            new bootstrap.Modal(document.getElementById('modalConfirmacion')).show();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la venta. Intente nuevamente.');
    })
    .finally(() => {
        // Restaurar botón
        btnGenerar.disabled = false;
        btnGenerar.innerHTML = '<i class="fas fa-cash-register me-2"></i>Generar Venta';
    });
}

// Imprimir boleta
document.getElementById('btnImprimir').addEventListener('click', function() {
    if (ventaId) {
        window.open(`generar_boleta_libre_pdf.php?id=${ventaId}&action=print`, '_blank');
    }
});

// Descargar boleta
document.getElementById('btnDescargar').addEventListener('click', function() {
    if (ventaId) {
        window.open(`generar_boleta_libre_pdf.php?id=${ventaId}&action=download`, '_blank');
    }
});

// Nueva venta
function nuevaVenta() {
    location.reload();
}

// Validación en tiempo real
document.querySelectorAll('input[required], textarea[required]').forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>