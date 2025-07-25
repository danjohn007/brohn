<?php
$pageTitle = "Iniciar Sesión";
$hideHeader = true;
require_once '../includes/header.php';

// Si ya está logueado, redirigir
if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$message = '';
$messageType = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $message = 'Email y contraseña son obligatorios';
        $messageType = 'danger';
    } else {
        $auth = new Auth();
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            // Redirigir según el rol
            if (Auth::isAdmin()) {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg">
            <div class="card-header text-center">
                <h3 class="mb-0">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </h3>
                <p class="mb-0 mt-2 text-white-50">Accede a tu cuenta</p>
            </div>
            <div class="card-body p-4">
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm">
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
                               placeholder="tu@email.com"
                               autofocus>
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
                               placeholder="Tu contraseña">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">
                            Recordarme
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-2">¿No tienes cuenta?</p>
                    <a href="registro.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </a>
                </div>
                
                <div class="text-center mt-3">
                    <a href="#" class="text-decoration-none small">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Cuentas de demo -->
        <div class="card mt-4 bg-light">
            <div class="card-body">
                <h6 class="card-title text-center">
                    <i class="fas fa-info-circle"></i> Cuentas de Prueba
                </h6>
                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="text-primary">Usuario</h6>
                        <small class="text-muted">
                            <strong>Email:</strong> usuario@test.com<br>
                            <strong>Pass:</strong> password
                        </small>
                        <br>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="fillDemo('usuario@test.com', 'password')">
                            Usar Demo
                        </button>
                    </div>
                    <div class="col-6">
                        <h6 class="text-danger">Admin</h6>
                        <small class="text-muted">
                            <strong>Email:</strong> admin@karaoke.com<br>
                            <strong>Pass:</strong> password
                        </small>
                        <br>
                        <button class="btn btn-sm btn-outline-danger mt-2" onclick="fillDemo('admin@karaoke.com', 'password')">
                            Usar Demo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$customJS = "
<script>
function fillDemo(email, password) {
    $('#email').val(email);
    $('#password').val(password);
    $('#loginForm').submit();
}

$(document).ready(function() {
    // Validación del formulario
    $('#loginForm').on('submit', function(e) {
        const email = $('#email').val().trim();
        const password = $('#password').val();
        
        if (!email || !password) {
            e.preventDefault();
            showAlert('Por favor completa todos los campos', 'warning');
            return false;
        }
        
        if (!email.includes('@')) {
            e.preventDefault();
            showAlert('Por favor ingresa un email válido', 'warning');
            return false;
        }
    });
    
    // Mensaje de bienvenida para usuarios nuevos
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('registered') === 'true') {
        showAlert('¡Registro exitoso! Ahora puedes iniciar sesión.', 'success');
    }
});
</script>
";

require_once '../includes/footer.php';
?>