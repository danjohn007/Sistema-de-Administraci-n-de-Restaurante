# FIXES PARA ERRORES DE SQL EN MÓDULO FINANCIERO

## Problema Identificado

Se detectaron errores fatales al intentar acceder a los informes de transacciones:

```
Fatal error: Uncaught PDOException: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'w.name' in 'field list'
```

## Causas del Error

1. **Error de SQL JOIN**: Las consultas intentaban acceder a `w.name` (nombre del mesero) pero la tabla `waiters` no tiene columna `name`. El nombre está en la tabla `users` relacionada por `waiters.user_id`.

2. **Métodos de pago faltantes**: Los métodos de pago `'intercambio'` y `'pendiente_por_cobrar'` no estaban incluidos en la definición ENUM de la tabla `tickets`.

## Archivos Afectados

### 1. models/Ticket.php
- Métodos `getPendingPayments()` y `getTicketsByPaymentMethod()`
- Método `updatePaymentMethod()`

### 2. Base de datos
- Tabla `tickets` - columna `payment_method`

## Soluciones Implementadas

### 1. Correción de Consultas SQL

**ANTES:**
```sql
SELECT t.*, 
       tn.number as table_number,
       u.name as cashier_name,
       w.name as waiter_name,  -- ❌ ERROR: w.name no existe
       w.employee_code
FROM tickets t
LEFT JOIN waiters w ON o.waiter_id = w.id
```

**DESPUÉS:**
```sql
SELECT t.*, 
       tn.number as table_number,
       u.name as cashier_name,
       u_waiter.name as waiter_name,  -- ✅ CORRECTO: nombre del usuario
       w.employee_code
FROM tickets t
LEFT JOIN waiters w ON o.waiter_id = w.id
LEFT JOIN users u_waiter ON w.user_id = u_waiter.id  -- ✅ JOIN adicional
```

### 2. Actualización de Métodos de Pago

**ANTES:**
```php
$validMethods = ['efectivo', 'tarjeta', 'transferencia', 'intercambio'];
```

**DESPUÉS:**
```php
$validMethods = ['efectivo', 'tarjeta', 'transferencia', 'intercambio', 'pendiente_por_cobrar'];
```

### 3. Migración de Base de Datos

Se creó el archivo `database/migration_payment_methods.sql`:

```sql
ALTER TABLE tickets 
MODIFY COLUMN payment_method ENUM('efectivo', 'tarjeta', 'transferencia', 'intercambio', 'pendiente_por_cobrar') DEFAULT 'efectivo';
```

## Instrucciones de Aplicación

### Paso 1: Ejecutar Migración de Base de Datos
```bash
mysql -u usuario -p nombre_base_datos < database/migration_payment_methods.sql
```

### Paso 2: Los cambios en código ya están aplicados
- ✅ `models/Ticket.php` - corregido
- ✅ Validaciones actualizadas

### Paso 3: Verificar Funcionamiento
1. Acceder a "Finanzas" → "Pendiente por Cobrar"
2. Acceder a "Finanzas" → "Total Intercambios"

## Rutas Afectadas Reparadas

- `/financial/collections` - Pendiente por Cobrar
- `/financial/intercambios` - Total Intercambios

## Validación de Éxito

✅ **PHP Syntax**: Sin errores de sintaxis
✅ **SQL Structure**: JOINs correctos implementados
✅ **Payment Methods**: Métodos agregados a validación
✅ **Database Migration**: Script de migración creado

## Notas Técnicas

- Los cambios son **mínimos y quirúrgicos**
- No afectan funcionalidad existente
- Compatible con estructura actual de la base de datos
- Mantiene integridad referencial

---
*Documentación generada el: $(date '+%Y-%m-%d %H:%M:%S')*