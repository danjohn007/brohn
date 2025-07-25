<?php
$pageTitle = "Mis Reservaciones";
require_once '../includes/header.php';

// Requerir login
Auth::requireLogin();

$message = '';
$messageType = '';

// Procesar cancelación de reservación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    try {
        $reservationId = (int)($_POST['reservation_id'] ?? 0);
        $db = getDB();
        
        // Verificar que la reservación pertenezca al usuario
        $stmt = $db->prepare("
            SELECT id, fecha, hora, cantidad_personas, status 
            FROM reservations 
            WHERE id = ? AND user_id = ? AND status != 'cancelada'
        ");
        $stmt->execute([$reservationId, Auth::getCurrentUser()['id']]);
        $reservation = $stmt->fetch();
        
        if (!$reservation) {
            throw new Exception('Reservación no encontrada o ya cancelada');
        }
        
        // Verificar que la reservación no sea en las próximas 2 horas
        $reservationDateTime = strtotime($reservation['fecha'] . ' ' . $reservation['hora']);
        $currentDateTime = time();
        $hoursUntilReservation = ($reservationDateTime - $currentDateTime) / 3600;
        
        if ($hoursUntilReservation < 2) {
            throw new Exception('No se puede cancelar una reservación con menos de 2 horas de anticipación');
        }
        
        // Iniciar transacción
        $db->beginTransaction();
        
        try {
            // Cancelar la reservación
            $stmt = $db->prepare("UPDATE reservations SET status = 'cancelada' WHERE id = ?");
            $stmt->execute([$reservationId]);
            
            // Liberar el cupo
            $stmt = $db->prepare("
                UPDATE availability 
                SET cupo_ocupado = cupo_ocupado - ? 
                WHERE fecha = ? AND hora = ?
            ");
            $stmt->execute([$reservation['cantidad_personas'], $reservation['fecha'], $reservation['hora']]);
            
            $db->commit();
            
            $message = 'Reservación cancelada exitosamente';
            $messageType = 'success';
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Obtener reservaciones del usuario
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id, fecha, hora, cantidad_personas, servicio, status, notas, 
               fecha_creacion, fecha_actualizacion
        FROM reservations 
        WHERE user_id = ? 
        ORDER BY fecha DESC, hora DESC
    ");
    $stmt->execute([Auth::getCurrentUser()['id']]);
    $reservaciones = $stmt->fetchAll();
} catch (PDOException $e) {
    $reservaciones = [];
    $message = 'Error al cargar las reservaciones';
    $messageType = 'danger';
}

// Función para formatear fecha en español
function formatearFechaEspanol($fecha) {
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    
    $timestamp = strtotime($fecha);
    $dia = date('j', $timestamp);
    $mes = $meses[(int)date('n', $timestamp)];
    $anio = date('Y', $timestamp);
    
    return "$dia de $mes de $anio";
}

// Función para formatear hora
function formatearHora($hora) {
    return date('g:i A', strtotime($hora));
}
?>

<div class="row">
    <div class="col-12">
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-list"></i> Mis Reservaciones
            </h2>
            <a href="reservacion.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Reservación
            </a>
        </div>

        <?php if (empty($reservaciones)): ?>
            <div class="card text-center">
                <div class="card-body py-5">
                    <i class="fas fa-calendar-times fa-5x text-muted mb-4"></i>
                    <h4>No tienes reservaciones</h4>
                    <p class="text-muted mb-4">
                        Aún no has hecho ninguna reservación. ¡Es hora de planear tu momento estrella!
                    </p>
                    <a href="reservacion.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-calendar-plus"></i> Hacer mi Primera Reservación
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($reservaciones as $reservacion): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo formatearFechaEspanol($reservacion['fecha']); ?>
                                </h6>
                                <span class="status-badge status-<?php echo $reservacion['status']; ?>">
                                    <?php echo ucfirst($reservacion['status']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <strong><i class="fas fa-clock"></i> Hora:</strong><br>
                                        <?php echo formatearHora($reservacion['hora']); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong><i class="fas fa-users"></i> Personas:</strong><br>
                                        <?php echo $reservacion['cantidad_personas']; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <strong><i class="fas fa-music"></i> Servicio:</strong><br>
                                    <?php echo htmlspecialchars($reservacion['servicio']); ?>
                                </div>
                                
                                <?php if (!empty($reservacion['notas'])): ?>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-sticky-note"></i> Notas:</strong><br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($reservacion['notas']); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-muted small">
                                    <i class="fas fa-calendar-plus"></i> 
                                    Creada: <?php echo date('d/m/Y g:i A', strtotime($reservacion['fecha_creacion'])); ?>
                                </div>
                            </div>
                            
                            <?php if ($reservacion['status'] !== 'cancelada'): ?>
                                <div class="card-footer">
                                    <?php
                                    $reservationDateTime = strtotime($reservacion['fecha'] . ' ' . $reservacion['hora']);
                                    $currentDateTime = time();
                                    $hoursUntilReservation = ($reservationDateTime - $currentDateTime) / 3600;
                                    ?>
                                    
                                    <?php if ($hoursUntilReservation >= 2): ?>
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="cancelReservation(<?php echo $reservacion['id']; ?>)">
                                            <i class="fas fa-times"></i> Cancelar Reservación
                                        </button>
                                    <?php else: ?>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            No se puede cancelar (menos de 2 horas)
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Estadísticas -->
            <div class="row mt-5">
                <div class="col-12">
                    <h4>Estadísticas</h4>
                </div>
                
                <?php
                $totalReservaciones = count($reservaciones);
                $confirmadas = count(array_filter($reservaciones, fn($r) => $r['status'] === 'confirmada'));
                $pendientes = count(array_filter($reservaciones, fn($r) => $r['status'] === 'pendiente'));
                $canceladas = count(array_filter($reservaciones, fn($r) => $r['status'] === 'cancelada'));
                ?>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-primary text-white">
                        <div class="card-body">
                            <h3><?php echo $totalReservaciones; ?></h3>
                            <p class="mb-0">Total</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body">
                            <h3><?php echo $confirmadas; ?></h3>
                            <p class="mb-0">Confirmadas</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-warning text-white">
                        <div class="card-body">
                            <h3><?php echo $pendientes; ?></h3>
                            <p class="mb-0">Pendientes</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-danger text-white">
                        <div class="card-body">
                            <h3><?php echo $canceladas; ?></h3>
                            <p class="mb-0">Canceladas</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmación para cancelar -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Reservación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas cancelar esta reservación?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    No, mantener reservación
                </button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="reservation_id" id="cancelReservationId">
                    <button type="submit" class="btn btn-danger">
                        Sí, cancelar reservación
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$customJS = "
<script>
function cancelReservation(reservationId) {
    $('#cancelReservationId').val(reservationId);
    $('#cancelModal').modal('show');
}

$(document).ready(function() {
    // Auto-ocultar alertas después de 5 segundos
    $('.alert').delay(5000).fadeOut();
});
</script>
";

require_once '../includes/footer.php';
?>