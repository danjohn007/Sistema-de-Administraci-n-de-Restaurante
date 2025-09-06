# Configuración de Liberación Automática de Mesas

## Descripción
El sistema incluye una funcionalidad para liberar automáticamente todas las mesas al inicio de cada día y gestionar los pedidos vencidos (pedidos de días anteriores que no han sido entregados).

## Características Implementadas

### 1. Liberación Automática Diaria
- **Todas las mesas se liberan** automáticamente cada día
- **Estado de mesas**: Se resetea a "disponible"
- **Asignación de meseros**: Se remueve la asignación automáticamente
- **Preservación de datos**: Los pedidos y tickets se mantienen para reportes

### 2. Gestión de Pedidos Vencidos
- **Identificación automática**: Pedidos de días anteriores que no están entregados
- **Visualización en dashboard**: Alerta destacada cuando hay pedidos vencidos
- **Acceso directo**: Botón "PEDIDOS VENCIDOS" en el dashboard
- **Lista dedicada**: Vista especial en `/orders/expiredOrders`
- **Código de colores**: Pedidos vencidos se muestran en rojo

### 3. Funcionalidades del Dashboard
- **Alerta para administradores**: Banner rojo cuando hay pedidos vencidos
- **Alerta para meseros**: Banner amarillo para sus pedidos vencidos
- **Contador en tiempo real**: Número de pedidos vencidos por usuario
- **Acceso rápido**: Link directo a la gestión de pedidos vencidos

## Configuración del Cron Job

### Script Automático
El sistema incluye un script de liberación automática en:
```
/scripts/daily_table_liberation.php
```

### Configuración Recomendada
Para configurar la liberación automática diaria, agregue la siguiente línea al crontab del servidor:

```bash
# Liberar mesas todos los días a medianoche
0 0 * * * /usr/bin/php /ruta/completa/al/proyecto/scripts/daily_table_liberation.php >> /var/log/restaurant_liberation.log 2>&1
```

### Ejecución Manual
También puede ejecutar el script manualmente:
```bash
php scripts/daily_table_liberation.php
```

## Funcionamiento del Script

### Lo que hace el script:
1. **Revisa mesas con pedidos vencidos** de días anteriores
2. **Libera todas las mesas** (resetea estado y asignaciones)
3. **Cuenta pedidos vencidos** para reportes
4. **Genera log** con el resultado de la operación
5. **Opcional**: Envía notificaciones por email

### Salida del script:
```
=== Daily Table Liberation Script ===
Starting at: 2024-12-23 00:00:01

1. Checking for tables with expired orders...
   Found 2 tables with expired orders:
   - Mesa 5: 1 pedidos pendientes, monto total: $150.00
   - Mesa 8: 2 pedidos pendientes, monto total: $275.50

2. Liberating all tables...
   ✓ All tables have been liberated successfully.

3. Checking for expired orders...
   Found 3 expired orders from previous days.
   These orders will appear in the 'Pedidos Vencidos' section of the dashboard.

=== Table Liberation Completed Successfully ===
Finished at: 2024-12-23 00:00:02
```

## Características del Sistema de Pedidos Vencidos

### Vista de Pedidos Vencidos (`/orders/expiredOrders`)
- **Estadísticas resumidas**: Total de pedidos, tiempo promedio vencido, monto total
- **Lista detallada**: Tabla con información completa de cada pedido vencido
- **Filtros por rol**: Los meseros solo ven sus propios pedidos vencidos
- **Acciones disponibles**: Ver, editar, generar ticket (si está listo)
- **Código de colores**: Fondo rojo para identificar fácilmente

### Integración con Dashboard
- **Para Administradores**: Alerta roja con total de pedidos vencidos del sistema
- **Para Meseros**: Alerta amarilla con sus propios pedidos vencidos
- **Contador dinámico**: Se actualiza automáticamente cada día
- **Acceso rápido**: Botón directo desde la alerta

## Mantenimiento de la Funcionalidad Auto-liberación

### Verificación de Funcionamiento
1. Revisar que el cron job esté configurado:
   ```bash
   crontab -l | grep liberation
   ```

2. Verificar logs de ejecución:
   ```bash
   tail -f /var/log/restaurant_liberation.log
   ```

3. Probar ejecución manual:
   ```bash
   php scripts/daily_table_liberation.php
   ```

### Personalización
El script puede modificarse para:
- Cambiar horario de liberación
- Agregar notificaciones por email
- Integrar con sistemas de alertas
- Generar reportes adicionales

## Beneficios del Sistema

### Para el Restaurante:
- **Organización diaria**: Mesas libres cada mañana
- **Control de cuentas**: Seguimiento de pedidos no cerrados
- **Eficiencia operativa**: Identificación rápida de problemas
- **Reportes precisos**: Separación clara entre días

### Para el Personal:
- **Alertas visuales**: Identificación inmediata de problemas
- **Acceso rápido**: Solución de pedidos vencidos
- **Responsabilidad clara**: Meseros ven sus propios pendientes
- **Flujo de trabajo**: Mejor organización diaria

## Compatibilidad

### Funcionalidad de Tickets
- **Auto-liberación mantenida**: Al imprimir ticket, la mesa se libera automáticamente
- **Funcionamiento normal**: El sistema actual de tickets no se ve afectado
- **Doble liberación**: Las mesas se pueden liberar por ticket O por proceso diario

### Base de Datos
- **Sin cambios en estructura**: Usa campos existentes
- **Historial preservado**: Todos los pedidos y tickets se mantienen
- **Reportes intactos**: No afecta reportes financieros o de ventas