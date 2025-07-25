# Sistema de Reservaciones Online

Este proyecto corresponde al desarrollo de un sistema completo de **reservaciones online** usando **PHP** como backend y **MySQL** como base de datos. El sistema permitirá a los usuarios hacer reservaciones, consultar disponibilidad y al administrador gestionar todas las reservas.

## 🚩 Objetivo

Crear un sistema funcional que pueda integrarse fácilmente en una página existente o ser usado como módulo independiente de reservaciones (ejemplo: concursos de karaoke). Incluirá una página web con logos y contenido especificado en el PDF adjunto.

## 📝 Requerimientos funcionales

### 1. Módulo de Usuario
- Registro e inicio de sesión de usuarios.
- Formulario para hacer una reservación (nombre, fecha, hora, cantidad de personas, servicio o espacio).
- Visualización de historial de reservaciones.
- Cancelación de reservación antes de la fecha.

### 2. Módulo de Disponibilidad
- Consulta de fechas y horas disponibles según reglas definidas (ejemplo: no más de X por franja horaria).
- Validación de disponibilidad antes de confirmar la reservación.

### 3. Módulo de Administrador
- Login administrativo.
- Panel para ver todas las reservaciones (pendientes, confirmadas, canceladas).
- Confirmar o cancelar manualmente reservaciones.
- Exportar listado (CSV o PDF opcional).

## 🗃️ Base de Datos (MySQL)

Tablas sugeridas:
- `users`: id, nombre, email, contraseña, rol
- `reservations`: id, user_id, fecha, hora, cantidad_personas, status
- `availability`: id, fecha, hora, cupo_maximo

## 💻 Tecnologías

- PHP
- MySQL
- Bootstrap
- AJAX para validación en tiempo real

## 📁 Estructura de Archivos Sugerida

```
/reservaciones/
│
├── config/
│   └── database.php
│
├── public/
│   ├── index.php
│   ├── login.php
│   ├── registro.php
│   └── reservacion.php
│
├── admin/
│   └── dashboard.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── auth.php
│
├── assets/
│   └── styles.css
│
└── sql/
    └── schema.sql
```

## 🎯 Tareas por archivo

- `config/database.php`: Conexión segura a MySQL.
- `registro.php` y `login.php`: Formularios y lógica para cuentas de usuario.
- `reservacion.php`: Formulario y validación en tiempo real para reservaciones.
- `dashboard.php` (admin): Vista y gestión de reservaciones.
- `schema.sql`: Script para crear las tablas.

## 📄 Recursos

- [Plataforma de Lanzamiento de Karaoke Sensō (PDF)](https://github.com/user-attachments/files/21400191/Plataforma.de.Lanzamiento.de.Karaoke.Senso.pdf)

---
