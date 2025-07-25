        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-microphone-alt"></i> Karaoke Sensō</h5>
                    <p class="mb-0">Plataforma de Lanzamiento de Karaoke</p>
                    <p class="text-muted">Tu momento estrella te espera</p>
                </div>
                <div class="col-md-3">
                    <h6>Enlaces Rápidos</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light text-decoration-none">Inicio</a></li>
                        <?php if (Auth::isLoggedIn()): ?>
                            <li><a href="reservacion.php" class="text-light text-decoration-none">Reservar</a></li>
                            <li><a href="mis-reservaciones.php" class="text-light text-decoration-none">Mis Reservaciones</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="text-light text-decoration-none">Iniciar Sesión</a></li>
                            <li><a href="registro.php" class="text-light text-decoration-none">Registrarse</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Contacto</h6>
                    <p class="mb-1">
                        <i class="fas fa-envelope"></i> info@karaokesenso.com
                    </p>
                    <p class="mb-1">
                        <i class="fas fa-phone"></i> +52 (55) 1234-5678
                    </p>
                    <div class="mt-2">
                        <a href="#" class="text-light me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-3">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Karaoke Sensō. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <a href="#" class="text-light text-decoration-none">Términos de Servicio</a> | 
                        <a href="#" class="text-light text-decoration-none">Política de Privacidad</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Función para mostrar alertas con animación
        function showAlert(message, type = 'info') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const alertContainer = $('#alert-container');
            if (alertContainer.length) {
                alertContainer.html(alertHtml);
            } else {
                $('main .container').prepend('<div id="alert-container">' + alertHtml + '</div>');
            }
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 5000);
        }

        // Validación de formularios
        $(document).ready(function() {
            // Habilitar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    
    <!-- Scripts específicos de página -->
    <?php if (isset($customJS)): ?>
        <?php echo $customJS; ?>
    <?php endif; ?>
</body>
</html>