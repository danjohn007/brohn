<?php
require_once '../includes/auth.php';

// Cerrar sesión
$auth = new Auth();
$auth->logout();

// Redirigir a la página principal
header('Location: index.php?logged_out=true');
exit;
?>