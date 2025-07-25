<?php
$pageTitle = "Inicio";
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-lg">
            <div class="card-body p-5 text-center">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h2 class="display-5 fw-bold mb-4">
                            ¡Bienvenido a Karaoke Sensō!
                        </h2>
                        <p class="lead mb-4">
                            La plataforma definitiva para reservar tu momento estrella. 
                            Disfruta de la mejor experiencia de karaoke con tecnología de vanguardia 
                            y un ambiente espectacular.
                        </p>
                        <?php if (!Auth::isLoggedIn()): ?>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="registro.php" class="btn btn-primary btn-lg me-md-2">
                                    <i class="fas fa-user-plus"></i> Registrarse
                                </a>
                                <a href="login.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="reservacion.php" class="btn btn-accent btn-lg me-md-2">
                                    <i class="fas fa-calendar-plus"></i> Hacer Reservación
                                </a>
                                <a href="mis-reservaciones.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-list"></i> Mis Reservaciones
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-6">
                        <div class="text-center">
                            <i class="fas fa-microphone-alt" style="font-size: 8rem; color: var(--primary-color); opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="row mb-5">
    <div class="col-12">
        <h3 class="text-center mb-5">¿Por qué elegir Karaoke Sensō?</h3>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="feature-card fade-in">
            <i class="fas fa-music"></i>
            <h5>Catálogo Extenso</h5>
            <p class="text-muted">
                Miles de canciones en múltiples idiomas. Desde clásicos hasta los últimos éxitos.
            </p>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="feature-card fade-in">
            <i class="fas fa-calendar-check"></i>
            <h5>Reservas Fáciles</h5>
            <p class="text-muted">
                Sistema de reservaciones en línea simple y eficiente. Disponibilidad en tiempo real.
            </p>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="feature-card fade-in">
            <i class="fas fa-star"></i>
            <h5>Experiencia Premium</h5>
            <p class="text-muted">
                Equipo de audio profesional, iluminación espectacular y ambiente único.
            </p>
        </div>
    </div>
</div>

<!-- How it Works Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header text-center">
                <h4 class="mb-0">
                    <i class="fas fa-info-circle"></i> ¿Cómo funciona?
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">1</span>
                        </div>
                        <h6>Regístrate</h6>
                        <p class="text-muted small">Crea tu cuenta en segundos</p>
                    </div>
                    
                    <div class="col-md-3 text-center mb-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">2</span>
                        </div>
                        <h6>Elige tu horario</h6>
                        <p class="text-muted small">Selecciona fecha y hora disponible</p>
                    </div>
                    
                    <div class="col-md-3 text-center mb-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">3</span>
                        </div>
                        <h6>Confirma tu reserva</h6>
                        <p class="text-muted small">Completa la reservación</p>
                    </div>
                    
                    <div class="col-md-3 text-center mb-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">4</span>
                        </div>
                        <h6>¡Disfruta!</h6>
                        <p class="text-muted small">Vive tu momento estrella</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section (if user is logged in) -->
<?php if (Auth::isLoggedIn()): ?>
    <?php
    try {
        $db = getDB();
        
        // Obtener estadísticas del usuario
        $stmt = $db->prepare("SELECT COUNT(*) as total_reservaciones FROM reservations WHERE user_id = ?");
        $stmt->execute([Auth::getCurrentUser()['id']]);
        $userStats = $stmt->fetch();
        
        // Obtener próximas reservaciones
        $stmt = $db->prepare("SELECT COUNT(*) as proximas FROM reservations WHERE user_id = ? AND fecha >= CURDATE() AND status != 'cancelada'");
        $stmt->execute([Auth::getCurrentUser()['id']]);
        $upcomingReservations = $stmt->fetch();
        
    } catch (PDOException $e) {
        $userStats = ['total_reservaciones' => 0];
        $upcomingReservations = ['proximas' => 0];
    }
    ?>
    
    <div class="row mb-5">
        <div class="col-12">
            <h4 class="text-center mb-4">Tu Dashboard</h4>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-number"><?php echo $userStats['total_reservaciones']; ?></div>
                <div class="stat-label">Reservaciones Totales</div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-number"><?php echo $upcomingReservations['proximas']; ?></div>
                <div class="stat-label">Próximas Reservaciones</div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Contact Section -->
<div class="row">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body text-center p-5">
                <h4 class="mb-4">¿Tienes preguntas?</h4>
                <p class="mb-4">
                    Nuestro equipo está aquí para ayudarte. Contáctanos para cualquier consulta 
                    sobre reservaciones, eventos especiales o información general.
                </p>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <i class="fas fa-phone fa-2x text-primary mb-2"></i>
                                <br>
                                <strong>+52 (55) 1234-5678</strong>
                            </div>
                            <div class="col-md-4 mb-3">
                                <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                                <br>
                                <strong>info@karaokesenso.com</strong>
                            </div>
                            <div class="col-md-4 mb-3">
                                <i class="fas fa-map-marker-alt fa-2x text-primary mb-2"></i>
                                <br>
                                <strong>Ciudad de México</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>