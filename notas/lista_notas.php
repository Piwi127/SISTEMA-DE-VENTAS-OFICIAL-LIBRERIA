<?php
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">
    <h2>Notas</h2>
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
            <!-- Aquí se cargarán las notas desde la base de datos -->
            <tr>
                <td>1</td>
                <td>Reunión con Proveedores</td>
                <td>Confirmar asistencia y preparar agenda.</td>
                <td>2024-08-15 10:00</td>
                <td>Pendiente</td>
                <td>
                    <button class="btn btn-sm btn-warning">Editar</button>
                    <button class="btn btn-sm btn-danger">Eliminar</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>