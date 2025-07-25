<?php
$pageTitle = "Panel de Administración";
require_once '../includes/header.php';

// Requerir privilegios de administrador
Auth::requireAdmin();

$message = '';
$messageType = '';

// Procesar acciones del administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        $reservationId = (int)($_POST['reservation_id'] ?? 0);
        $db = getDB();
        
        if ($action === 'confirm' || $action === 'cancel') {
            $newStatus = $action === 'confirm' ? 'confirmada' : 'cancelada';
            
            // Obtener detalles de la reservación
            $stmt = $db->prepare("
                SELECT id, fecha, hora, cantidad_personas, status 
                FROM reservations 
                WHERE id = ?
            ");
            $stmt->execute([$reservationId]);
            $reservation = $stmt->fetch();
            
            if (!$reservation) {
                throw new Exception('Reservación no encontrada');
            }
            
            if ($reservation['status'] === $newStatus) {
                throw new Exception('La reservación ya tiene ese estado');
            }
            
            // Iniciar transacción
            $db->beginTransaction();
            
            try {
                // Actualizar estado de la reservación
                $stmt = $db->prepare("UPDATE reservations SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $reservationId]);
                
                // Ajustar cupo según la acción
                if ($action === 'cancel' && $reservation['status'] !== 'cancelada') {
                    // Liberar cupo
                    $stmt = $db->prepare("
                        UPDATE availability 
                        SET cupo_ocupado = cupo_ocupado - ? 
                        WHERE fecha = ? AND hora = ?
                    ");
                    $stmt->execute([$reservation['cantidad_personas'], $reservation['fecha'], $reservation['hora']]);
                } elseif ($action === 'confirm' && $reservation['status'] === 'cancelada') {
                    // Ocupar cupo nuevamente
                    $stmt = $db->prepare("
                        UPDATE availability 
                        SET cupo_ocupado = cupo_ocupado + ? 
                        WHERE fecha = ? AND hora = ?
                    ");
                    $stmt->execute([$reservation['cantidad_personas'], $reservation['fecha'], $reservation['hora']]);
                }
                
                $db->commit();
                
                $actionText = $action === 'confirm' ? 'confirmada' : 'cancelada';
                $message = "Reservación {$actionText} exitosamente";
                $messageType = 'success';
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
        }
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Obtener estadísticas generales
try {
    $db = getDB();
    
    // Estadísticas básicas
    $stats = [];
    
    // Total de reservaciones
    $stmt = $db->query("SELECT COUNT(*) as total FROM reservations");
    $stats['total_reservaciones'] = $stmt->fetch()['total'];
    
    // Reservaciones por estado
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM reservations GROUP BY status");
    while ($row = $stmt->fetch()) {
        $stats['por_estado'][$row['status']] = $row['count'];
    }
    
    // Reservaciones de hoy
    $stmt = $db->prepare("SELECT COUNT(*) as hoy FROM reservations WHERE fecha = CURDATE()");
    $stmt->execute();
    $stats['hoy'] = $stmt->fetch()['hoy'];
    
    // Reservaciones de esta semana
    $stmt = $db->prepare("
        SELECT COUNT(*) as semana 
        FROM reservations 
        WHERE YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)
    ");
    $stmt->execute();
    $stats['semana'] = $stmt->fetch()['semana'];
    
    // Total de usuarios
    $stmt = $db->query("SELECT COUNT(*) as usuarios FROM users WHERE rol = 'user'");
    $stats['usuarios'] = $stmt->fetch()['usuarios'];
    
} catch (PDOException $e) {
    $stats = [
        'total_reservaciones' => 0,
        'por_estado' => ['pendiente' => 0, 'confirmada' => 0, 'cancelada' => 0],
        'hoy' => 0,
        'semana' => 0,
        'usuarios' => 0
    ];
}

// Obtener todas las reservaciones con información del usuario
try {
    $stmt = $db->prepare("
        SELECT r.id, r.fecha, r.hora, r.cantidad_personas, r.servicio, r.status, 
               r.notas, r.fecha_creacion, u.nombre, u.email
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.fecha DESC, r.hora DESC
        LIMIT 50
    ");
    $stmt->execute();
    $reservaciones = $stmt->fetchAll();
} catch (PDOException $e) {
    $reservaciones = [];
}

// Funciones de formato
function formatearFechaEspanol($fecha) {
    $meses = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
    ];
    
    $timestamp = strtotime($fecha);
    $dia = date('j', $timestamp);
    $mes = $meses[(int)date('n', $timestamp)];
    
    return "$dia $mes";
}

function formatearHora($hora) {
    return date('g:i A', strtotime($hora));
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2>
            <i class="fas fa-tachometer-alt"></i> Panel de Administración
        </h2>
        <p class="text-muted">Gestión de reservaciones y estadísticas del sistema</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Estadísticas principales -->
<div class="row mb-5">
    <div class="col-md-2 mb-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_reservaciones']; ?></div>
            <div class="stat-label">Total Reservaciones</div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-warning text-white">
            <div class="card-body">
                <h3><?php echo $stats['por_estado']['pendiente'] ?? 0; ?></h3>
                <p class="mb-0">Pendientes</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <h3><?php echo $stats['por_estado']['confirmada'] ?? 0; ?></h3>
                <p class="mb-0">Confirmadas</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-danger text-white">
            <div class="card-body">
                <h3><?php echo $stats['por_estado']['cancelada'] ?? 0; ?></h3>
                <p class="mb-0">Canceladas</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body">
                <h3><?php echo $stats['hoy']; ?></h3>
                <p class="mb-0">Hoy</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card text-center bg-primary text-white">
            <div class="card-body">
                <h3><?php echo $stats['usuarios']; ?></h3>
                <p class="mb-0">Usuarios</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de reservaciones -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Reservaciones Recientes
                </h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="exportData()">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Personas</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservaciones)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i><br>
                                        No hay reservaciones registradas
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reservaciones as $reservacion): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $reservacion['id']; ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($reservacion['nombre']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($reservacion['email']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo formatearFechaEspanol($reservacion['fecha']); ?><br>
                                            <small class="text-muted"><?php echo date('Y', strtotime($reservacion['fecha'])); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo formatearHora($reservacion['hora']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo $reservacion['cantidad_personas']; ?> personas
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($reservacion['servicio']); ?></small>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $reservacion['status']; ?>">
                                                <?php echo ucfirst($reservacion['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <?php if ($reservacion['status'] === 'pendiente'): ?>
                                                    <button class="btn btn-sm btn-success" 
                                                            onclick="changeStatus(<?php echo $reservacion['id']; ?>, 'confirm')"
                                                            title="Confirmar">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="changeStatus(<?php echo $reservacion['id']; ?>, 'cancel')"
                                                            title="Cancelar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php elseif ($reservacion['status'] === 'confirmada'): ?>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="changeStatus(<?php echo $reservacion['id']; ?>, 'cancel')"
                                                            title="Cancelar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php elseif ($reservacion['status'] === 'cancelada'): ?>
                                                    <button class="btn btn-sm btn-success" 
                                                            onclick="changeStatus(<?php echo $reservacion['id']; ?>, 'confirm')"
                                                            title="Reactivar">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="viewDetails(<?php echo $reservacion['id']; ?>)"
                                                        title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambio de estado -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalTitle">Cambiar Estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="statusModalBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="reservation_id" id="statusReservationId">
                    <input type="hidden" name="action" id="statusAction">
                    <button type="submit" class="btn" id="statusSubmitBtn">
                        Confirmar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$customJS = "
<script>
function changeStatus(reservationId, action) {
    let title, body, btnClass, btnText;
    
    if (action === 'confirm') {
        title = 'Confirmar Reservación';
        body = '¿Confirmar esta reservación?';
        btnClass = 'btn-success';
        btnText = 'Confirmar';
    } else {
        title = 'Cancelar Reservación';
        body = '¿Cancelar esta reservación?';
        btnClass = 'btn-danger';
        btnText = 'Cancelar';
    }
    
    $('#statusModalTitle').text(title);
    $('#statusModalBody').html('<p>' + body + '</p>');
    $('#statusReservationId').val(reservationId);
    $('#statusAction').val(action);
    $('#statusSubmitBtn').removeClass().addClass('btn ' + btnClass).text(btnText);
    
    $('#statusModal').modal('show');
}

function viewDetails(reservationId) {
    // Aquí se podría implementar un modal con más detalles
    showAlert('Funcionalidad de detalles en desarrollo', 'info');
}

function exportData() {
    // Aquí se podría implementar la exportación a CSV
    showAlert('Funcionalidad de exportación en desarrollo', 'info');
}

$(document).ready(function() {
    // Auto-refresh cada 30 segundos
    setInterval(function() {
        const alertContainer = $('.alert');
        if (alertContainer.length === 0) {
            location.reload();
        }
    }, 30000);
});
</script>
";

require_once '../includes/footer.php';
?>