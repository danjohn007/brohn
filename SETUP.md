# Sistema de Reservaciones de Karaoke Sensō

## Instalación y Configuración

### Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache o Nginx
- Extensiones PHP: PDO, PDO_MySQL

### Pasos de Instalación

1. **Configurar Base de Datos**
   ```bash
   # Crear la base de datos
   mysql -u root -p
   CREATE DATABASE reservaciones_karaoke;
   exit
   
   # Importar el schema
   mysql -u root -p reservaciones_karaoke < sql/schema.sql
   ```

2. **Configurar Conexión a Base de Datos**
   
   Editar `config/database.php` y ajustar las credenciales:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'reservaciones_karaoke');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   ```

3. **Configurar Servidor Web**
   
   **Apache (.htaccess)**
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```
   
   **Nginx**
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

4. **Establecer Permisos**
   ```bash
   chmod 755 -R reservaciones/
   chmod 644 reservaciones/config/database.php
   ```

### Cuentas por Defecto

#### Usuario Administrador
- **Email:** admin@karaoke.com
- **Contraseña:** password

#### Usuario de Prueba
- **Email:** usuario@test.com
- **Contraseña:** password

### Estructura de Archivos

```
reservaciones/
├── config/
│   └── database.php          # Configuración de BD
├── public/
│   ├── index.php            # Página principal
│   ├── login.php            # Inicio de sesión
│   ├── logout.php           # Cerrar sesión
│   ├── registro.php         # Registro de usuarios
│   ├── reservacion.php      # Crear reservación
│   ├── mis-reservaciones.php # Gestión de reservaciones del usuario
│   └── check_availability.php # API AJAX para disponibilidad
├── admin/
│   └── dashboard.php        # Panel de administración
├── includes/
│   ├── header.php           # Header común
│   ├── footer.php           # Footer común
│   └── auth.php             # Sistema de autenticación
├── assets/
│   └── styles.css           # Estilos personalizados
└── sql/
    └── schema.sql           # Esquema de base de datos
```

### Funcionalidades Principales

#### Para Usuarios
- ✅ Registro e inicio de sesión
- ✅ Crear nuevas reservaciones
- ✅ Ver historial de reservaciones
- ✅ Cancelar reservaciones (con restricción de tiempo)
- ✅ Verificación de disponibilidad en tiempo real

#### Para Administradores
- ✅ Panel de administración completo
- ✅ Ver todas las reservaciones
- ✅ Confirmar/cancelar reservaciones
- ✅ Estadísticas del sistema
- ✅ Gestión de usuarios

#### Características Técnicas
- ✅ Autenticación segura con hash de contraseñas
- ✅ Validación en tiempo real con AJAX
- ✅ Diseño responsivo con Bootstrap 5
- ✅ Prevención de SQL injection con prepared statements
- ✅ Sistema de roles (usuario/admin)
- ✅ Gestión de sesiones segura

### API Endpoints

#### AJAX
- `POST /public/check_availability.php` - Verificar disponibilidad

### Base de Datos

#### Tablas Principales

**users**
- `id` - ID único del usuario
- `nombre` - Nombre completo
- `email` - Email único
- `password` - Contraseña hasheada
- `rol` - Rol (user/admin)
- `fecha_registro` - Fecha de registro
- `activo` - Estado del usuario

**reservations**
- `id` - ID único de la reservación
- `user_id` - FK al usuario
- `fecha` - Fecha de la reservación
- `hora` - Hora de la reservación
- `cantidad_personas` - Número de personas
- `servicio` - Tipo de servicio
- `status` - Estado (pendiente/confirmada/cancelada)
- `notas` - Notas adicionales
- `fecha_creacion` - Timestamp de creación

**availability**
- `id` - ID único
- `fecha` - Fecha disponible
- `hora` - Hora disponible
- `cupo_maximo` - Capacidad máxima
- `cupo_ocupado` - Espacios ocupados
- `activo` - Estado de disponibilidad

### Configuración de Horarios

El sistema está configurado para:
- **Horarios:** 6:00 PM - 10:00 PM
- **Capacidad:** 10 personas por franja horaria
- **Servicios:** Standard, Premium, VIP, Evento Privado
- **Restricciones:** Cancelación hasta 2 horas antes

### Seguridad

- Contraseñas hasheadas con `password_hash()`
- Prepared statements para prevenir SQL injection
- Validación de entrada en cliente y servidor
- Control de acceso basado en roles
- Sesiones seguras con validación

### Personalización

Para personalizar el sistema:

1. **Colores y estilos:** Editar `assets/styles.css`
2. **Horarios:** Modificar `sql/schema.sql` y `public/reservacion.php`
3. **Servicios:** Actualizar opciones en `public/reservacion.php`
4. **Capacidades:** Ajustar en tabla `availability`

### Mantenimiento

#### Limpiar reservaciones antiguas
```sql
DELETE FROM reservations 
WHERE fecha < DATE_SUB(CURDATE(), INTERVAL 6 MONTH);
```

#### Generar disponibilidad futura
```sql
INSERT INTO availability (fecha, hora, cupo_maximo) 
SELECT 
    DATE_ADD(CURDATE(), INTERVAL seq.seq DAY) as fecha,
    TIME(CONCAT(hour_seq.hour, ':00:00')) as hora,
    10 as cupo_maximo
FROM 
    (SELECT 30 as seq UNION SELECT 31 UNION ... ) seq
CROSS JOIN 
    (SELECT 18 as hour UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22) hour_seq;
```

### Soporte

Para soporte técnico o preguntas sobre el sistema, contactar al administrador del sistema.

---

**Karaoke Sensō - Sistema de Reservaciones**  
*Tu momento estrella te espera*