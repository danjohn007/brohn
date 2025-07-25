<?php
require_once '../includes/auth.php';

// Solo permitir requests POST y usuarios logueados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Auth::isLoggedIn()) {
    http_response_code(403);
    exit('Forbidden');
}

header('Content-Type: application/json');

try {
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    
    if (empty($fecha) || empty($hora)) {
        echo json_encode(['error' => 'Fecha y hora requeridas']);
        exit;
    }
    
    $db = getDB();
    
    // Verificar disponibilidad
    $stmt = $db->prepare("
        SELECT cupo_maximo, cupo_ocupado 
        FROM availability 
        WHERE fecha = ? AND hora = ? AND activo = 1
    ");
    $stmt->execute([$fecha, $hora]);
    $availability = $stmt->fetch();
    
    if (!$availability) {
        echo json_encode([
            'available' => false,
            'message' => 'Horario no disponible'
        ]);
        exit;
    }
    
    $espacios_disponibles = $availability['cupo_maximo'] - $availability['cupo_ocupado'];
    
    echo json_encode([
        'available' => $espacios_disponibles > 0,
        'cupo_maximo' => (int)$availability['cupo_maximo'],
        'cupo_ocupado' => (int)$availability['cupo_ocupado'],
        'espacios_disponibles' => $espacios_disponibles
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al verificar disponibilidad']);
}
?>