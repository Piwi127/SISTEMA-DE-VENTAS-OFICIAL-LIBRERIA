<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Obtener notas del usuario
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $stmt = $pdo->prepare("SELECT * FROM notas WHERE user_id = ? ORDER BY fecha_recordatorio ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $notas = [];
    $error_message = "Error al cargar las notas: " . $e->getMessage();
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="container mt-5">
    <h2>Notas</h2>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    <div class="card">
        <div class="card-header">
            Agregar Nueva Nota
        </div>
        <div class="card-body">
            <form action="procesar_nota.php" method="POST">
                <div class="form-group">
                    <label for="asunto">Asunto:</label>
                    <input type="text" class="form-control" id="asunto" name="asunto" required>
                </div>
                <div class="form-group">
                    <label for="cuerpo_mensaje">Cuerpo del Mensaje:</label>
                    <textarea class="form-control" id="cuerpo_mensaje" name="cuerpo_mensaje" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label for="fecha_recordatorio">Fecha y Hora del Recordatorio:</label>
                    <input type="datetime-local" class="form-control" id="fecha_recordatorio" name="fecha_recordatorio" required>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Nota</button>
            </form>
        </div>
    </div>

    <h3 class="mt-5">Listado de Notas</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Asunto</th>
                <th>Cuerpo del Mensaje</th>
                <th>Fecha Recordatorio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($notas)): ?>
                <tr>
                    <td colspan="6" class="text-center">No hay notas registradas</td>
                </tr>
            <?php else: ?>
                <?php foreach ($notas as $nota): ?>
                    <tr data-nota-id="<?php echo $nota['id']; ?>">
                        <td><?php echo htmlspecialchars($nota['id']); ?></td>
                        <td><?php echo htmlspecialchars($nota['asunto']); ?></td>
                        <td><?php echo htmlspecialchars(substr($nota['cuerpo_mensaje'], 0, 50)) . (strlen($nota['cuerpo_mensaje']) > 50 ? '...' : ''); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($nota['fecha_recordatorio'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $nota['estado'] == 'completada' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($nota['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="verNota(<?php echo $nota['id']; ?>)" title="Ver completa">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editarNota(<?php echo $nota['id']; ?>)" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-<?php echo $nota['estado'] == 'completada' ? 'secondary' : 'success'; ?>" 
                                    onclick="cambiarEstado(<?php echo $nota['id']; ?>, '<?php echo $nota['estado'] == 'completada' ? 'pendiente' : 'completada'; ?>')" 
                                    title="<?php echo $nota['estado'] == 'completada' ? 'Marcar como pendiente' : 'Marcar como completada'; ?>">
                                <i class="fas fa-<?php echo $nota['estado'] == 'completada' ? 'undo' : 'check'; ?>"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarNota(<?php echo $nota['id']; ?>)" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para ver nota completa -->
<div class="modal fade" id="modalVerNota" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de la Nota</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="contenidoNota">
                <!-- Contenido se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar nota -->
<div class="modal fade" id="modalEditarNota" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Nota</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEditarNota" action="procesar_nota.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="editNotaId" name="nota_id">
                    <input type="hidden" name="accion" value="editar">
                    <div class="form-group">
                        <label for="editAsunto">Asunto:</label>
                        <input type="text" class="form-control" id="editAsunto" name="asunto" required>
                    </div>
                    <div class="form-group">
                        <label for="editCuerpoMensaje">Cuerpo del Mensaje:</label>
                        <textarea class="form-control" id="editCuerpoMensaje" name="cuerpo_mensaje" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editFechaRecordatorio">Fecha y Hora del Recordatorio:</label>
                        <input type="datetime-local" class="form-control" id="editFechaRecordatorio" name="fecha_recordatorio" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function verNota(id) {
    fetch('procesar_nota.php?accion=ver&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('contenidoNota').innerHTML = `
                    <h6><strong>Asunto:</strong></h6>
                    <p>${data.nota.asunto}</p>
                    <h6><strong>Mensaje:</strong></h6>
                    <p>${data.nota.cuerpo_mensaje}</p>
                    <h6><strong>Fecha de Recordatorio:</strong></h6>
                    <p>${data.nota.fecha_recordatorio}</p>
                    <h6><strong>Estado:</strong></h6>
                    <p><span class="badge badge-${data.nota.estado === 'completada' ? 'success' : 'warning'}">${data.nota.estado}</span></p>
                `;
                $('#modalVerNota').modal('show');
            } else {
                alert('Error al cargar la nota');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar la nota');
        });
}

function editarNota(id) {
    fetch('procesar_nota.php?accion=ver&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editNotaId').value = data.nota.id;
                document.getElementById('editAsunto').value = data.nota.asunto;
                document.getElementById('editCuerpoMensaje').value = data.nota.cuerpo_mensaje;
                document.getElementById('editFechaRecordatorio').value = data.nota.fecha_recordatorio.replace(' ', 'T');
                $('#modalEditarNota').modal('show');
            } else {
                alert('Error al cargar la nota');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar la nota');
        });
}

function cambiarEstado(id, nuevoEstado) {
    if (confirm('¿Está seguro de cambiar el estado de esta nota?')) {
        fetch('procesar_nota.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `accion=cambiar_estado&nota_id=${id}&estado=${nuevoEstado}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al cambiar el estado');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cambiar el estado');
        });
    }
}

function eliminarNota(id) {
    if (confirm('¿Está seguro de eliminar esta nota? Esta acción no se puede deshacer.')) {
        fetch('procesar_nota.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `accion=eliminar&nota_id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al eliminar la nota');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la nota');
        });
    }
}

// Resaltar nota específica si viene desde notificación
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight');
    
    if (highlightId) {
        const notaRow = document.querySelector(`tr[data-nota-id="${highlightId}"]`);
        if (notaRow) {
            notaRow.style.backgroundColor = '#fff3cd';
            notaRow.style.border = '2px solid #ffc107';
            notaRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Quitar el resaltado después de 5 segundos
            setTimeout(() => {
                notaRow.style.transition = 'all 0.5s ease';
                notaRow.style.backgroundColor = '';
                notaRow.style.border = '';
            }, 5000);
        }
    }
});
</script>

            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>