# Sistema de Administración de Restaurante

Un sistema completo de administración para restaurantes desarrollado en PHP puro con arquitectura MVC, MySQL y Bootstrap.

## 🚀 Características Principales

### Gestión de Usuarios y Roles
- **Administrador**: Acceso total al sistema (gestiona mesas, meseros y platillos)
- **Mesero**: Toma pedidos y los asigna a las mesas
- **Cajero**: Genera tickets y realiza cobros
- Control de permisos por rol
- Autenticación segura con hashing de contraseñas

### Gestión de Mesas
- Alta, baja y modificación de mesas
- Estados: disponible, ocupada, cuenta solicitada, cerrada
- Asignación de mesero a cada mesa
- Vista gráfica del estado de las mesas

### Gestión de Meseros
- Registro de meseros con códigos de empleado
- Asignación de pedidos y mesas específicos
- Historial de pedidos por mesero
- Estadísticas de rendimiento

### Menú y Pedidos
- Administración completa de platillos (alta, baja, edición, precios)
- Creación de pedidos asignados a mesas
- Carga rápida de productos del menú
- Estados de pedido: pendiente → en preparación → listo → entregado

### Sistema de Tickets
- Generación automática de tickets al cerrar cuentas
- Detalles completos: mesa, mesero, platillos, cantidades, precios, total
- Cálculo automático de impuestos (IVA 16%)
- Exportación e impresión de tickets
- Diferentes métodos de pago

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+ (sin framework)
- **Base de Datos**: MySQL 5.7+
- **Frontend**: Bootstrap 5.3, jQuery
- **Iconos**: Bootstrap Icons
- **Arquitectura**: MVC (Model-View-Controller)
- **Servidor Web**: Apache con mod_rewrite

## 📋 Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache 2.4+ con mod_rewrite habilitado
- Extensiones PHP requeridas:
  - PDO
  - pdo_mysql
  - session
  - json

## 🔧 Instalación

### 1. Clonar o Descargar
```bash
git clone https://github.com/danjohn007/Sistema-de-Administraci-n-de-Restaurante.git
cd Sistema-de-Administraci-n-de-Restaurante
```

### 2. Configurar Base de Datos
1. Crear una base de datos MySQL:
```sql
CREATE DATABASE restaurante_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importar el esquema de la base de datos:
```bash
mysql -u tu_usuario -p restaurante_db < database/schema.sql
```

3. Importar los datos de ejemplo:
```bash
mysql -u tu_usuario -p restaurante_db < database/sample_data.sql
```

### 3. Configurar Conexión a Base de Datos
Editar el archivo `config/config.php` y actualizar las credenciales:

```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'restaurante_db');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

### 4. Configurar Apache

#### Opción A: Directorio Raíz del Servidor
Si instalas en la raíz de tu servidor web (`/var/www/html/` en Linux o `htdocs/` en XAMPP):
- Copia todos los archivos a la carpeta raíz
- Accede mediante: `http://localhost/`

#### Opción B: Subdirectorio
Si instalas en un subdirectorio (`/var/www/html/restaurante/`):
- Copia todos los archivos al subdirectorio
- Accede mediante: `http://localhost/restaurante/`

#### Opción C: Virtual Host (Recomendado)
1. Crear un virtual host en Apache:
```apache
<VirtualHost *:80>
    ServerName restaurante.local
    DocumentRoot /path/to/Sistema-de-Administraci-n-de-Restaurante
    <Directory /path/to/Sistema-de-Administraci-n-de-Restaurante>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

2. Agregar al archivo hosts (`/etc/hosts` en Linux/Mac o `C:\Windows\System32\drivers\etc\hosts` en Windows):
```
127.0.0.1 restaurante.local
```

3. Acceder mediante: `http://restaurante.local/`

### 5. Verificar Instalación
1. Navega a la URL configurada
2. Deberías ver la página de login
3. Usa las credenciales de prueba (ver sección "Usuarios de Prueba")

## 👥 Usuarios de Prueba

El sistema incluye usuarios predefinidos para testing:

| Rol | Email | Contraseña | Descripción |
|-----|-------|------------|-------------|
| Administrador | admin@restaurante.com | 123456 | Acceso completo al sistema |
| Cajero | cajero@restaurante.com | 123456 | Gestión de tickets y cobros |
| Mesero | mesero1@restaurante.com | 123456 | Juan Pérez - Código MES001 |
| Mesero | mesero2@restaurante.com | 123456 | Ana López - Código MES002 |

## 🗂️ Estructura del Proyecto

```
Sistema-de-Administraci-n-de-Restaurante/
├── config/
│   ├── config.php          # Configuración general
│   └── database.php        # Configuración de base de datos
├── controllers/
│   ├── AuthController.php  # Autenticación
│   ├── DashboardController.php # Dashboard principal
│   ├── TableController.php     # Gestión de mesas
│   ├── WaiterController.php    # Gestión de meseros
│   ├── DishController.php      # Gestión de menú
│   ├── OrderController.php     # Gestión de pedidos
│   └── TicketController.php    # Gestión de tickets
├── core/
│   ├── BaseController.php  # Controlador base
│   └── BaseModel.php       # Modelo base
├── database/
│   ├── schema.sql          # Esquema de base de datos
│   └── sample_data.sql     # Datos de ejemplo
├── models/
│   ├── User.php           # Modelo de usuarios
│   ├── Table.php          # Modelo de mesas
│   ├── Waiter.php         # Modelo de meseros
│   ├── Dish.php           # Modelo de platillos
│   ├── Order.php          # Modelo de pedidos
│   ├── OrderItem.php      # Modelo de items de pedido
│   └── Ticket.php         # Modelo de tickets
├── public/
│   ├── css/
│   │   └── style.css      # Estilos personalizados
│   ├── js/
│   │   └── app.js         # JavaScript personalizado
│   └── images/            # Imágenes del sistema
├── views/
│   ├── layouts/
│   │   ├── header.php     # Header común
│   │   └── footer.php     # Footer común
│   ├── auth/
│   │   └── login.php      # Vista de login
│   ├── dashboard/
│   │   └── index.php      # Dashboard principal
│   └── ...                # Otras vistas
├── .htaccess              # Configuración Apache
├── index.php              # Punto de entrada
└── README.md              # Este archivo
```

## 🔒 Seguridad

- Contraseñas hasheadas con algoritmo seguro (bcrypt)
- Validación de entrada en todos los formularios
- Protección CSRF en formularios
- Control de acceso basado en roles
- Sesiones seguras con timeout
- Sanitización de datos SQL (PDO preparado)
- Headers de seguridad configurados

## 🌟 Funcionalidades Principales

### Dashboard
- Vista personalizada según el rol del usuario
- Estadísticas en tiempo real
- Accesos rápidos a funciones principales
- Reloj en tiempo real

### Gestión de Mesas
- Vista visual del estado de las mesas
- Asignación rápida de meseros
- Cambio de estado en tiempo real
- Historial de ocupación

### Gestión de Pedidos
- Interfaz intuitiva para tomar pedidos
- Búsqueda rápida de platillos
- Cálculo automático de totales
- Seguimiento de estado del pedido

### Sistema de Tickets
- Generación automática de números de ticket
- Cálculo de impuestos
- Múltiples métodos de pago
- Impresión y exportación

## 🛣️ URLs del Sistema

El sistema utiliza URLs amigables:

- `/` - Página de login
- `/dashboard` - Dashboard principal
- `/tables` - Gestión de mesas
- `/waiters` - Gestión de meseros
- `/dishes` - Gestión de menú
- `/orders` - Gestión de pedidos
- `/tickets` - Gestión de tickets
- `/auth/login` - Login
- `/auth/logout` - Logout
- `/auth/changePassword` - Cambiar contraseña

## 🔧 Personalización

### Cambiar Configuración
Edita `config/config.php` para:
- Cambiar nombre de la aplicación
- Modificar configuración de base de datos
- Ajustar timeouts de sesión
- Configurar rutas de archivos

### Personalizar Estilos
Edita `public/css/style.css` para:
- Cambiar colores del tema
- Modificar layout
- Personalizar componentes

### Añadir Funcionalidades
1. Crear nuevo modelo en `models/`
2. Crear controlador en `controllers/`
3. Crear vistas en `views/`
4. Actualizar navegación en `views/layouts/header.php`

## 🐛 Troubleshooting

### Error de Conexión a Base de Datos
1. Verificar credenciales en `config/config.php`
2. Asegurar que MySQL esté corriendo
3. Verificar que la base de datos existe
4. Comprobar permisos del usuario

### Error 404 - Página No Encontrada
1. Verificar que mod_rewrite está habilitado
2. Comprobar que el archivo `.htaccess` existe
3. Verificar permisos de archivos

### Problemas de Sesión
1. Verificar que la carpeta de sesiones tiene permisos de escritura
2. Comprobar configuración de PHP para sesiones
3. Verificar que las cookies están habilitadas

### Errores de JavaScript
1. Verificar que jQuery está cargando
2. Comprobar consola del navegador para errores
3. Verificar que los archivos JS están accesibles

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Para soporte técnico o preguntas:
- Abrir un issue en GitHub
- Revisar la documentación
- Verificar los logs de error de Apache/PHP

## 🔄 Actualizaciones

Para mantener el sistema actualizado:

1. Respaldar base de datos antes de cualquier actualización
2. Revisar el changelog para cambios importantes
3. Probar en ambiente de desarrollo antes de producción
4. Mantener PHP y MySQL actualizados

---

**Desarrollado con ❤️ para la gestión eficiente de restaurantes**