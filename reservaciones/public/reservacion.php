<?php
$pageTitle = "Hacer Reservación";
require_once '../includes/header.php';

// Requerir login
Auth::requireLogin();

$message = '';
$messageType = '';

// Procesar formulario de reservación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $cantidad_personas = (int)($_POST['cantidad_personas'] ?? 0);
        $servicio = $_POST['servicio'] ?? 'Karaoke Standard';
        $notas = trim($_POST['notas'] ?? '');
        
        // Validaciones
        if (empty($fecha) || empty($hora) || $cantidad_personas <= 0) {
            throw new Exception('Todos los campos obligatorios deben completarse');
        }
        
        if ($cantidad_personas > 10) {
            throw new Exception('El máximo de personas por reservación es 10');
        }
        
        // Verificar que la fecha no sea en el pasado
        if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
            throw new Exception('No se pueden hacer reservaciones para fechas pasadas');
        }
        
        // Verificar disponibilidad
        $stmt = $db->prepare("
            SELECT cupo_maximo, cupo_ocupado 
            FROM availability 
            WHERE fecha = ? AND hora = ? AND activo = 1
        ");
        $stmt->execute([$fecha, $hora]);
        $availability = $stmt->fetch();
        
        if (!$availability) {
            throw new Exception('La fecha y hora seleccionada no está disponible');
        }
        
        if (($availability['cupo_ocupado'] + $cantidad_personas) > $availability['cupo_maximo']) {
            $disponible = $availability['cupo_maximo'] - $availability['cupo_ocupado'];
            throw new Exception("No hay suficiente cupo. Solo quedan {$disponible} espacios disponibles");
        }
        
        // Verificar que el usuario no tenga ya una reservación en la misma fecha y hora
        $stmt = $db->prepare("
            SELECT id FROM reservations 
            WHERE user_id = ? AND fecha = ? AND hora = ? AND status != 'cancelada'
        ");
        $stmt->execute([Auth::getCurrentUser()['id'], $fecha, $hora]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Ya tienes una reservación para esta fecha y hora');
        }
        
        // Iniciar transacción
        $db->beginTransaction();
        
        try {
            // Crear la reservación
            $stmt = $db->prepare("
                INSERT INTO reservations (user_id, fecha, hora, cantidad_personas, servicio, notas, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pendiente')
            ");
            $stmt->execute([
                Auth::getCurrentUser()['id'], 
                $fecha, 
                $hora, 
                $cantidad_personas, 
                $servicio, 
                $notas
            ]);
            
            // Actualizar cupo ocupado
            $stmt = $db->prepare("
                UPDATE availability 
                SET cupo_ocupado = cupo_ocupado + ? 
                WHERE fecha = ? AND hora = ?
            ");
            $stmt->execute([$cantidad_personas, $fecha, $hora]);
            
            $db->commit();
            
            $message = 'Reservación creada exitosamente. Tu reservación está pendiente de confirmación.';
            $messageType = 'success';
            
            // Limpiar formulario
            $_POST = [];
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Obtener horarios disponibles para los próximos 30 días
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT fecha, hora, cupo_maximo, cupo_ocupado 
        FROM availability 
        WHERE fecha >= CURDATE() AND fecha <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
        AND activo = 1 
        ORDER BY fecha, hora
    ");
    $stmt->execute();
    $disponibilidad = $stmt->fetchAll();
} catch (PDOException $e) {
    $disponibilidad = [];
}
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-plus"></i> Nueva Reservación
                </h4>
            </div>
            <div class="card-body">
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="reservacionForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label">
                                <i class="fas fa-calendar"></i> Fecha *
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha" 
                                   name="fecha" 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"
                                   value="<?php echo htmlspecialchars($_POST['fecha'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="hora" class="form-label">
                                <i class="fas fa-clock"></i> Hora *
                            </label>
                            <select class="form-control" id="hora" name="hora" required>
                                <option value="">Seleccionar hora</option>
                                <option value="18:00:00" <?php echo ($_POST['hora'] ?? '') === '18:00:00' ? 'selected' : ''; ?>>6:00 PM</option>
                                <option value="19:00:00" <?php echo ($_POST['hora'] ?? '') === '19:00:00' ? 'selected' : ''; ?>>7:00 PM</option>
                                <option value="20:00:00" <?php echo ($_POST['hora'] ?? '') === '20:00:00' ? 'selected' : ''; ?>>8:00 PM</option>
                                <option value="21:00:00" <?php echo ($_POST['hora'] ?? '') === '21:00:00' ? 'selected' : ''; ?>>9:00 PM</option>
                                <option value="22:00:00" <?php echo ($_POST['hora'] ?? '') === '22:00:00' ? 'selected' : ''; ?>>10:00 PM</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cantidad_personas" class="form-label">
                                <i class="fas fa-users"></i> Cantidad de Personas *
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="cantidad_personas" 
                                   name="cantidad_personas" 
                                   min="1" 
                                   max="10"
                                   value="<?php echo htmlspecialchars($_POST['cantidad_personas'] ?? ''); ?>"
                                   required 
                                   placeholder="Número de personas">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="servicio" class="form-label">
                                <i class="fas fa-music"></i> Tipo de Servicio
                            </label>
                            <select class="form-control" id="servicio" name="servicio">
                                <option value="Karaoke Standard" <?php echo ($_POST['servicio'] ?? '') === 'Karaoke Standard' ? 'selected' : ''; ?>>Karaoke Standard</option>
                                <option value="Karaoke Premium" <?php echo ($_POST['servicio'] ?? '') === 'Karaoke Premium' ? 'selected' : ''; ?>>Karaoke Premium</option>
                                <option value="Karaoke VIP" <?php echo ($_POST['servicio'] ?? '') === 'Karaoke VIP' ? 'selected' : ''; ?>>Karaoke VIP</option>
                                <option value="Evento Privado" <?php echo ($_POST['servicio'] ?? '') === 'Evento Privado' ? 'selected' : ''; ?>>Evento Privado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notas" class="form-label">
                            <i class="fas fa-sticky-note"></i> Notas Adicionales
                        </label>
                        <textarea class="form-control" 
                                  id="notas" 
                                  name="notas" 
                                  rows="3" 
                                  placeholder="Solicitudes especiales, celebraciones, etc. (opcional)"><?php echo htmlspecialchars($_POST['notas'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Mostrar disponibilidad en tiempo real -->
                    <div id="availability-info" class="alert alert-info" style="display: none;">
                        <i class="fas fa-info-circle"></i> 
                        <span id="availability-text">Selecciona fecha y hora para ver disponibilidad</span>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-check"></i> Confirmar Reservación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Información de precios -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-dollar-sign"></i> Precios
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="d-flex justify-content-between">
                        <span>Karaoke Standard</span>
                        <strong>$300/hora</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span>Karaoke Premium</span>
                        <strong>$500/hora</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span>Karaoke VIP</span>
                        <strong>$800/hora</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span>Evento Privado</span>
                        <strong>$1,200/hora</strong>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Información importante -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Información Importante
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small">
                    <li><i class="fas fa-check text-success"></i> Las reservaciones requieren confirmación</li>
                    <li><i class="fas fa-check text-success"></i> Máximo 10 personas por reservación</li>
                    <li><i class="fas fa-check text-success"></i> Cancelación gratuita hasta 2 horas antes</li>
                    <li><i class="fas fa-check text-success"></i> Horarios disponibles: 6:00 PM - 10:00 PM</li>
                    <li><i class="fas fa-check text-success"></i> Incluye sistema de sonido profesional</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$customJS = "
<script>
$(document).ready(function() {
    // Verificar disponibilidad en tiempo real
    function checkAvailability() {
        const fecha = $('#fecha').val();
        const hora = $('#hora').val();
        
        if (fecha && hora) {
            $.post('check_availability.php', {
                fecha: fecha,
                hora: hora
            }, function(response) {
                const data = JSON.parse(response);
                const availabilityInfo = $('#availability-info');
                const availabilityText = $('#availability-text');
                
                if (data.available) {
                    const disponible = data.cupo_maximo - data.cupo_ocupado;
                    availabilityInfo.removeClass('alert-danger alert-warning').addClass('alert-success');
                    availabilityText.html('✓ Disponible - ' + disponible + ' espacios libres de ' + data.cupo_maximo);
                } else {
                    availabilityInfo.removeClass('alert-success alert-warning').addClass('alert-danger');
                    availabilityText.html('✗ No disponible - Cupo completo');
                }
                
                availabilityInfo.show();
            }).fail(function() {
                $('#availability-info').hide();
            });
        } else {
            $('#availability-info').hide();
        }
    }
    
    // Eventos para verificar disponibilidad
    $('#fecha, #hora').on('change', checkAvailability);
    
    // Validación del formulario
    $('#reservacionForm').on('submit', function(e) {
        const cantidadPersonas = parseInt($('#cantidad_personas').val());
        
        if (cantidadPersonas > 10) {
            e.preventDefault();
            showAlert('El máximo de personas por reservación es 10', 'warning');
            return false;
        }
        
        if (cantidadPersonas <= 0) {
            e.preventDefault();
            showAlert('Debe especificar al menos 1 persona', 'warning');
            return false;
        }
    });
    
    // Verificar disponibilidad inicial si hay valores
    checkAvailability();
});
</script>
";

require_once '../includes/footer.php';
?>