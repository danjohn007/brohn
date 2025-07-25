<?php
/**
 * Sistema de Autenticación
 * Funciones para login, registro y gestión de sesiones
 */

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function register($nombre, $email, $password) {
        try {
            // Verificar si el email ya existe
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'El email ya está registrado'];
            }
            
            // Validar datos
            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Email inválido'];
            }
            
            // Hash de la contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar usuario
            $stmt = $this->db->prepare("INSERT INTO users (nombre, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $email, $hashedPassword]);
            
            return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al registrar usuario: ' . $e->getMessage()];
        }
    }
    
    /**
     * Iniciar sesión
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre, email, password, rol FROM users WHERE email = ? AND activo = 1");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
            }
            
            $user = $stmt->fetch();
            
            if (password_verify($password, $user['password'])) {
                // Establecer sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol'];
                $_SESSION['logged_in'] = true;
                
                return ['success' => true, 'message' => 'Login exitoso', 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
            }
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error en el login: ' . $e->getMessage()];
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada'];
    }
    
    /**
     * Verificar si el usuario está logueado
     */
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Verificar si el usuario es administrador
     */
    public static function isAdmin() {
        return self::isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Obtener información del usuario actual
     */
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'nombre' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'rol' => $_SESSION['user_role']
            ];
        }
        return null;
    }
    
    /**
     * Requerir login - redirige si no está logueado
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    /**
     * Requerir admin - redirige si no es administrador
     */
    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: index.php');
            exit;
        }
    }
}
?>