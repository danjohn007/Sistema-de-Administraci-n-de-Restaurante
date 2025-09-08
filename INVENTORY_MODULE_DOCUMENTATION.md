# MÓDULO DE INVENTARIOS Y CONTROL DE COBRANZA - DOCUMENTACIÓN

## Descripción General

Se ha implementado un módulo completo de inventarios integrado directamente con las ventas y compras del restaurante, junto con funcionalidad para permitir o no permitir cobranza desde el nivel superadministrador.

## Características Implementadas

### 1. Módulo de Inventarios

#### Base de Datos
- **inventory_products**: Gestión de productos de inventario
- **inventory_movements**: Historial de entradas y salidas
- **dish_ingredients**: Recetas (ingredientes por platillo)
- **system_settings**: Configuraciones del sistema

#### Funcionalidades Principales
- **Gestión de Productos**: CRUD completo con categorización
- **Control de Stock**: Mínimos, máximos y alertas automáticas
- **Movimientos**: Registro automático y manual de entradas/salidas
- **Reportes**: Análisis de inventario y valorización
- **Recetas**: Configuración de ingredientes por platillo
- **Integración**: Descuento automático al generar tickets

### 2. Control de Cobranza por Superadmin

#### Nuevo Rol
- **ROLE_SUPERADMIN**: Rol con permisos de configuración del sistema

#### Configuraciones
- **collections_enabled**: Habilita/deshabilita cuentas por cobrar
- **inventory_enabled**: Habilita/deshabilita módulo de inventarios
- **auto_deduct_inventory**: Descuento automático en ventas

## Instalación y Configuración

### Paso 1: Ejecutar Migración de Base de Datos
```sql
mysql -u usuario -p nombre_base_datos < database/migration_inventory_module.sql
```

### Paso 2: Crear Usuario Superadministrador
```sql
UPDATE users SET role = 'superadmin' WHERE id = [ID_DEL_ADMIN_PRINCIPAL];
```

### Paso 3: Configurar el Sistema
1. Iniciar sesión como superadministrador
2. Ir a **Inventario** → **Configuración**
3. Habilitar/deshabilitar módulos según necesidades

## Uso del Módulo de Inventarios

### Gestión de Productos

#### Agregar Producto
1. **Inventario** → **Agregar Producto**
2. Completar información básica:
   - Nombre descriptivo
   - Categoría para organización
   - Unidad de medida
   - Stocks mínimo y máximo
   - Costo por unidad
3. Marcar "Es ingrediente" si se usa en platillos

#### Estados de Stock
- **Normal**: Stock entre mínimo y máximo
- **Stock Bajo**: Por debajo del mínimo (alerta roja)
- **Stock Alto**: Por encima del máximo (alerta amarilla)

### Movimientos de Inventario

#### Tipos de Movimiento
- **Entrada**: Compras, reposiciones
- **Salida**: Ventas, consumos, ajustes
- **Automático**: Por tickets de venta (si está habilitado)
- **Manual**: Ajustes, inventarios físicos

#### Referencias
- **Gasto**: Entrada asociada a compra registrada
- **Ticket**: Salida por venta
- **Ajuste**: Correcciones de inventario
- **Manual**: Movimientos manuales

### Integración con Ventas

#### Configuración de Recetas
1. Ir a **Platillos** → **Editar**
2. Configurar ingredientes necesarios
3. Especificar cantidades por porción

#### Descuento Automático
- Se activa al generar tickets
- Descuenta ingredientes según recetas
- Respeta la configuración del sistema
- Genera movimientos automáticos

## Control de Cuentas por Cobrar

### Configuración
1. **Inventario** → **Configuración** (solo superadmin)
2. Habilitar/deshabilitar "Cuentas por Cobrar"
3. Los cambios son inmediatos

### Efectos de la Configuración
- **Habilitado**: Método "Pendiente por Cobrar" disponible en tickets
- **Deshabilitado**: Método no aparece como opción
- **Validación**: Sistema valida disponibilidad al crear tickets

## Permisos por Rol

| Funcionalidad | SuperAdmin | Admin | Cajero | Mesero |
|---------------|------------|-------|--------|--------|
| Ver Inventario | ✓ | ✓ | ✓ | ✗ |
| Crear/Editar Productos | ✓ | ✓ | ✗ | ✗ |
| Registrar Movimientos | ✓ | ✓ | ✓ | ✗ |
| Ver Reportes | ✓ | ✓ | ✓ | ✗ |
| Configuración Sistema | ✓ | ✗ | ✗ | ✗ |

## Reportes Disponibles

### Dashboard de Inventario
- Total de productos
- Productos con stock bajo
- Valor total del inventario
- Alertas y notificaciones

### Reporte de Movimientos
- Historial filtrable por fecha/producto/tipo
- Resumen de entradas y salidas
- Valorización de movimientos

### Análisis de Stock
- Productos críticos
- Rotación de inventario
- Costos por categoría

## Flujo de Trabajo Recomendado

### Configuración Inicial
1. Crear productos básicos
2. Configurar stocks mínimos/máximos
3. Establecer costos unitarios
4. Configurar recetas de platillos populares

### Operación Diaria
1. Registrar compras como entradas
2. Verificar alertas de stock bajo
3. Revisar movimientos automáticos
4. Ajustar inventarios según necesidad

### Reportes Periódicos
1. Análisis semanal de rotación
2. Valorización mensual
3. Revisión de costos
4. Optimización de stocks

## Consideraciones Técnicas

### Rendimiento
- Índices optimizados para consultas frecuentes
- Paginación en listados largos
- Caché de configuraciones del sistema

### Seguridad
- Validación de permisos en cada operación
- Sanitización de datos de entrada
- Logs de movimientos para auditoría

### Escalabilidad
- Diseño modular para nuevas funcionalidades
- Configuraciones flexibles
- Integración con módulos existentes

## Mantenimiento

### Tareas Regulares
- Verificar consistencia de stocks
- Limpiar logs antiguos
- Actualizar costos unitarios
- Revisar configuración de alertas

### Backup
- Incluir tablas de inventario en respaldos
- Conservar historial de movimientos
- Respaldar configuraciones del sistema

## Soporte

### Archivos Principales
- `models/Product.php`: Modelo de productos
- `models/InventoryMovement.php`: Gestión de movimientos
- `models/SystemSettings.php`: Configuraciones
- `controllers/InventoryController.php`: Lógica de negocio
- `views/inventory/`: Interfaces de usuario

### Logs y Debug
- Errores se registran en logs del sistema
- Movimientos tienen trazabilidad completa
- Validaciones con mensajes descriptivos

---

**Implementado el:** $(date '+%Y-%m-%d')
**Versión:** 1.0.0
**Compatibilidad:** Sistema de Administración de Restaurante v1.2.0+