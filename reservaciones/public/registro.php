<?php
$pageTitle = "Registro";
$hideHeader = true;
require_once '../includes/header.php';

// Si ya está logueado, redirigir
if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$message = '';
$messageType = '';

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $message = 'Todos los campos son obligatorios';
        $messageType = 'danger';
    } elseif ($password !== $confirmPassword) {
        $message = 'Las contraseñas no coinciden';
        $messageType = 'danger';
    } else {
        $auth = new Auth();
        $result = $auth->register($nombre, $email, $password);
        
        if ($result['success']) {
            $message = $result['message'] . '. Ahora puedes iniciar sesión.';
            $messageType = 'success';
            // Limpiar el formulario
            $nombre = $email = '';
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg">
            <div class="card-header text-center">
                <h3 class="mb-0">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </h3>
                <p class="mb-0 mt-2 text-white-50">Únete a Karaoke Sensō</p>
            </div>
            <div class="card-body p-4">
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="registroForm">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">
                            <i class="fas fa-user"></i> Nombre Completo
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre" 
                               name="nombre" 
                               value="<?php echo htmlspecialchars($nombre ?? ''); ?>"
                               required 
                               placeholder="Ingresa tu nombre completo">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               required 
                               placeholder="tu@email.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Contraseña
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required 
                               minlength="6"
                               placeholder="Mínimo 6 caracteres">
                        <div class="form-text">
                            <small class="text-muted">La contraseña debe tener al menos 6 caracteres.</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock"></i> Confirmar Contraseña
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required 
                               placeholder="Repite tu contraseña">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">
                            Acepto los <a href="#" class="text-decoration-none">términos y condiciones</a> 
                            y la <a href="#" class="text-decoration-none">política de privacidad</a>
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Registrarse
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-0">¿Ya tienes una cuenta?</p>
                    <a href="login.php" class="btn btn-outline-primary mt-2">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Información adicional -->
        <div class="text-center mt-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-gift"></i> Beneficios de registrarse
                    </h6>
                    <ul class="list-unstyled text-start">
                        <li><i class="fas fa-check text-success"></i> Reservaciones rápidas y fáciles</li>
                        <li><i class="fas fa-check text-success"></i> Historial de tus reservaciones</li>
                        <li><i class="fas fa-check text-success"></i> Notificaciones de disponibilidad</li>
                        <li><i class="fas fa-check text-success"></i> Ofertas y promociones exclusivas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$customJS = "
<script>
$(document).ready(function() {
    // Validación en tiempo real
    $('#registroForm').on('submit', function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();
        
        if (password !== confirmPassword) {
            e.preventDefault();
            showAlert('Las contraseñas no coinciden', 'danger');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            showAlert('La contraseña debe tener al menos 6 caracteres', 'danger');
            return false;
        }
    });
    
    // Validación en tiempo real de confirmación de contraseña
    $('#confirm_password').on('keyup', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
";

require_once '../includes/footer.php';
?>