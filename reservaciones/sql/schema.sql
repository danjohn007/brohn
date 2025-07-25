-- Sistema de Reservaciones - Database Schema
-- Creación de base de datos y tablas

CREATE DATABASE IF NOT EXISTS reservaciones_karaoke;
USE reservaciones_karaoke;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('user', 'admin') DEFAULT 'user',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de disponibilidad
CREATE TABLE IF NOT EXISTS availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    cupo_maximo INT DEFAULT 10,
    cupo_ocupado INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_fecha_hora (fecha, hora)
);

-- Tabla de reservaciones
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    cantidad_personas INT NOT NULL,
    servicio VARCHAR(100) DEFAULT 'Karaoke Standard',
    status ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    notas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_fecha_hora (fecha, hora),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
);

-- Insertar usuario administrador por defecto
INSERT INTO users (nombre, email, password, rol) 
VALUES ('Administrador', 'admin@karaoke.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Insertar disponibilidad por defecto para los próximos 30 días
INSERT INTO availability (fecha, hora, cupo_maximo) 
SELECT 
    DATE_ADD(CURDATE(), INTERVAL seq.seq DAY) as fecha,
    TIME(CONCAT(hour_seq.hour, ':00:00')) as hora,
    10 as cupo_maximo
FROM 
    (SELECT 0 as seq UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION 
     SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION 
     SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION 
     SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION 
     SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION 
     SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29) seq
CROSS JOIN 
    (SELECT 18 as hour UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22) hour_seq
ON DUPLICATE KEY UPDATE cupo_maximo=cupo_maximo;

-- Crear un usuario de prueba
INSERT INTO users (nombre, email, password, rol) 
VALUES ('Usuario Prueba', 'usuario@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user')
ON DUPLICATE KEY UPDATE nombre=nombre;