# Sistema de Reservaciones Online - Karaoke Sensō

Este proyecto es un sistema completo de **reservaciones online** desarrollado con **PHP** como backend y **MySQL** como base de datos. El sistema permite a los usuarios hacer reservaciones, consultar disponibilidad en tiempo real y al administrador gestionar todas las reservas.

## ✅ Estado del Proyecto: COMPLETADO

El sistema ha sido completamente implementado con todas las funcionalidades requeridas.

## 🚀 Instalación Rápida

Para configurar y ejecutar el sistema, consulta el archivo [SETUP.md](SETUP.md) con instrucciones detalladas.

### Cuentas de Prueba

- **Administrador:** admin@karaoke.com / password
- **Usuario:** usuario@test.com / password

## ✅ Funcionalidades Implementadas

### 🔐 Sistema de Autenticación
- ✅ Registro de usuarios con validación
- ✅ Inicio de sesión seguro
- ✅ Gestión de sesiones
- ✅ Control de acceso por roles (usuario/admin)
- ✅ Contraseñas hasheadas con bcrypt

### 👤 Módulo de Usuario
- ✅ Registro e inicio de sesión de usuarios
- ✅ Formulario para hacer reservaciones con validación en tiempo real
- ✅ Visualización completa del historial de reservaciones
- ✅ Cancelación de reservación antes de la fecha (con restricción de 2 horas)
- ✅ Dashboard personalizado con estadísticas

### 📅 Módulo de Disponibilidad
- ✅ Consulta de fechas y horas disponibles en tiempo real
- ✅ Sistema de cupos por franja horaria (máximo 10 personas)
- ✅ Validación de disponibilidad antes de confirmar reservación
- ✅ API AJAX para verificación instantánea

### 🛠️ Módulo de Administrador
- ✅ Login administrativo con privilegios especiales
- ✅ Panel completo para ver todas las reservaciones
- ✅ Confirmar o cancelar manualmente reservaciones
- ✅ Estadísticas en tiempo real del sistema
- ✅ Gestión de usuarios y reservaciones
- ✅ Auto-actualización del dashboard

### 🎨 Interfaz de Usuario
- ✅ Diseño responsivo con Bootstrap 5
- ✅ Tema personalizado con colores corporativos
- ✅ Iconografía consistente con Font Awesome
- ✅ Animaciones y transiciones suaves
- ✅ Experiencia de usuario optimizada

### 🔒 Seguridad
- ✅ Prepared statements para prevenir SQL injection
- ✅ Validación de entrada en cliente y servidor
- ✅ Sanitización de datos
- ✅ Control de acceso basado en roles
- ✅ Sesiones seguras

## 🚩 Objetivo

Crear un sistema funcional que pueda integrarse fácilmente en una página existente o ser usado como módulo independiente de reservaciones de concursos de karaoke. El sistema incluye una página web completa con identidad visual de Karaoke Sensō.

## 🗃️ Base de Datos (MySQL)

**Tablas implementadas:**
- ✅ `users`: id, nombre, email, contraseña, rol, fecha_registro, activo
- ✅ `reservations`: id, user_id, fecha, hora, cantidad_personas, servicio, status, notas, timestamps
- ✅ `availability`: id, fecha, hora, cupo_maximo, cupo_ocupado, activo

## 💻 Tecnologías Utilizadas

- ✅ **PHP 7.4+** con PDO para base de datos
- ✅ **MySQL** con esquema completo y datos de prueba
- ✅ **Bootstrap 5** para diseño responsivo
- ✅ **AJAX/jQuery** para validación en tiempo real
- ✅ **Font Awesome** para iconografía
- ✅ **CSS personalizado** con variables y animaciones

## 📁 Estructura de Archivos Implementada

```
/reservaciones/
├── config/
│   └── database.php          # ✅ Conexión segura a MySQL con PDO
├── public/
│   ├── index.php            # ✅ Página principal con hero section
│   ├── login.php            # ✅ Sistema de login con cuentas demo
│   ├── logout.php           # ✅ Cierre de sesión
│   ├── registro.php         # ✅ Registro de usuarios con validación
│   ├── reservacion.php      # ✅ Formulario de reservación con AJAX
│   ├── mis-reservaciones.php # ✅ Gestión de reservaciones del usuario
│   └── check_availability.php # ✅ API para verificación de disponibilidad
├── admin/
│   └── dashboard.php        # ✅ Panel administrativo completo
├── includes/
│   ├── header.php           # ✅ Header común con navegación
│   ├── footer.php           # ✅ Footer con scripts y enlaces
│   └── auth.php             # ✅ Sistema de autenticación
├── assets/
│   └── styles.css           # ✅ Estilos personalizados responsive
└── sql/
    └── schema.sql           # ✅ Esquema completo con datos de prueba
```

## 🎯 Características Destacadas

### 🚀 Experiencia de Usuario
- **Interfaz Intuitiva:** Diseño moderno y fácil de usar
- **Tiempo Real:** Verificación instantánea de disponibilidad
- **Responsivo:** Funciona perfectamente en móviles y desktop
- **Navegación Fluida:** Menús adaptativos según el rol del usuario

### ⚡ Funcionalidades Avanzadas
- **Validación Doble:** Cliente y servidor para máxima seguridad
- **Gestión de Cupos:** Control automático de capacidad por horario
- **Sistema de Estados:** Pendiente → Confirmada → Cancelada
- **Restricciones Inteligentes:** Cancelación limitada a 2 horas antes
- **Estadísticas en Vivo:** Dashboard con métricas actualizadas

### 🔧 Configuración Flexible
- **Horarios Personalizables:** 6:00 PM - 10:00 PM (modificable)
- **Servicios Múltiples:** Standard, Premium, VIP, Evento Privado
- **Capacidades Variables:** 10 personas por slot (ajustable)
- **Roles Definidos:** Usuario regular y administrador

## 🎯 Tareas Completadas

- ✅ `config/database.php`: Conexión segura a MySQL con patrón Singleton
- ✅ `registro.php` y `login.php`: Formularios con validación y cuentas demo
- ✅ `reservacion.php`: Formulario con validación AJAX en tiempo real
- ✅ `dashboard.php` (admin): Vista completa con estadísticas y gestión
- ✅ `schema.sql`: Script completo con datos de prueba incluidos
- ✅ Sistema de autenticación completo con roles y seguridad
- ✅ Interfaz responsiva con tema personalizado de Karaoke Sensō
- ✅ API AJAX para verificación de disponibilidad en tiempo real

## 📊 Métricas del Proyecto

- **Archivos PHP:** 12 archivos implementados
- **Líneas de Código:** ~2,000 líneas (HTML, PHP, CSS, JS)
- **Tablas de BD:** 3 tablas principales con relaciones
- **Funcionalidades:** 100% de requerimientos completados
- **Seguridad:** Implementación completa con mejores prácticas
- **Responsive:** Compatible con todos los dispositivos

## 🚀 Demo en Vivo

El sistema está listo para usarse inmediatamente:

1. **Configurar base de datos** según [SETUP.md](SETUP.md)
2. **Acceder a** `public/index.php` 
3. **Usar cuentas de prueba** para explorar funcionalidades
4. **Administrar** desde `admin/dashboard.php`

## 📧 Soporte

Sistema desarrollado para **Karaoke Sensō** - Plataforma de Lanzamiento de Karaoke.

*¡Tu momento estrella te espera!*

## 📄 Recursos

- [Plataforma de Lanzamiento de Karaoke Sensō (PDF)](https://github.com/user-attachments/files/21400191/Plataforma.de.Lanzamiento.de.Karaoke.Senso.pdf)

---
