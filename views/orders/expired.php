<?php $title = 'Pedidos Vencidos'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-exclamation-triangle text-danger"></i> Pedidos Vencidos</h1>
    <div>
        <a href="<?= BASE_URL ?>/orders" class="btn btn-secondary me-2">
            <i class="bi bi-calendar-day"></i> Pedidos de Hoy
        </a>
        <a href="<?= BASE_URL ?>/orders/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Pedido
        </a>
    </div>
</div>

<div class="alert alert-warning">
    <i class="bi bi-info-circle"></i> 
    <strong>Pedidos Vencidos:</strong> Estos son pedidos creados en días anteriores que aún no han sido entregados. 
    Requieren atención inmediata para cerrar las cuentas pendientes.
</div>

<?php if (empty($orders)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-check-circle display-1 text-success"></i>
            <h3 class="mt-3 text-success">¡Excelente! No hay pedidos vencidos</h3>
            <p class="text-muted">
                <?php if ($user['role'] === ROLE_WAITER): ?>
                    No tienes pedidos pendientes de días anteriores.
                <?php else: ?>
                    No hay pedidos pendientes de días anteriores en el sistema.
                <?php endif; ?>
            </p>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/orders" class="btn btn-primary">
                    <i class="bi bi-calendar-day"></i> Ver Pedidos de Hoy
                </a>
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-danger">
            <div class="card-body">
                <i class="bi bi-exclamation-triangle display-6 text-danger"></i>
                <h6 class="mt-2">Pedidos Vencidos</h6>
                <h4 class="text-danger">
                    <?= count($orders) ?>
                </h4>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-warning">
            <div class="card-body">
                <i class="bi bi-clock display-6 text-warning"></i>
                <h6 class="mt-2">Tiempo Promedio</h6>
                <h4 class="text-warning">
                    <?php 
                    $totalHours = 0;
                    foreach ($orders as $order) {
                        $totalHours += (int)$order['hours_since_created'];
                    }
                    $avgHours = count($orders) > 0 ? round($totalHours / count($orders)) : 0;
                    echo $avgHours . 'h';
                    ?>
                </h4>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-info">
            <div class="card-body">
                <i class="bi bi-currency-dollar display-6 text-info"></i>
                <h6 class="mt-2">Monto Total</h6>
                <h4 class="text-info">
                    $<?php 
                    $totalAmount = 0;
                    foreach ($orders as $order) {
                        $totalAmount += (float)$order['total'];
                    }
                    echo number_format($totalAmount, 2);
                    ?>
                </h4>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-secondary">
            <div class="card-body">
                <i class="bi bi-grid-3x3-gap display-6 text-secondary"></i>
                <h6 class="mt-2">Mesas Afectadas</h6>
                <h4 class="text-secondary">
                    <?php 
                    $tables = array_unique(array_column($orders, 'table_number'));
                    echo count($tables);
                    ?>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Orders List -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Pedidos Vencidos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mesa</th>
                        <th>Mesero</th>
                        <th>Cliente</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Creado</th>
                        <th>Tiempo Vencido</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr class="table-danger">
                        <td>
                            <strong>#<?= $order['id'] ?></strong>
                        </td>
                        <td>
                            <?php if ($order['table_number']): ?>
                                <span class="badge bg-secondary">Mesa <?= $order['table_number'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-info">Pickup</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($order['waiter_name']): ?>
                                <small>
                                    <?= htmlspecialchars($order['waiter_name']) ?><br>
                                    <span class="text-muted"><?= htmlspecialchars($order['employee_code']) ?></span>
                                </small>
                            <?php else: ?>
                                <span class="text-muted">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <i class="bi bi-person-fill text-info"></i> 
                            <small class="text-muted">Cliente:</small><br>
                            <?= htmlspecialchars($order['customer_name'] ?: $order['order_customer_name'] ?: 'Público') ?>
                            <?php if (!empty($order['customer_phone'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($order['customer_phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?= $order['items_count'] ?> items</span>
                        </td>
                        <td>
                            <strong class="text-danger">$<?= number_format($order['total'], 2) ?></strong>
                        </td>
                        <td>
                            <small>
                                <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-danger">
                                <?= $order['hours_since_created'] ?>h vencido
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= getStatusColor($order['status']) ?>">
                                <?= getStatusLabel($order['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/orders/show/<?= $order['id'] ?>" 
                                   class="btn btn-outline-primary" title="Ver Detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <?php if ($user['role'] !== ROLE_WAITER || $order['waiter_id'] == $waiter['id']): ?>
                                <a href="<?= BASE_URL ?>/orders/edit/<?= $order['id'] ?>" 
                                   class="btn btn-outline-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <?php if ($order['status'] === ORDER_READY): ?>
                                <a href="<?= BASE_URL ?>/tickets/create?order_id=<?= $order['id'] ?>" 
                                   class="btn btn-outline-success" title="Generar Ticket">
                                    <i class="bi bi-receipt"></i>
                                </a>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Helper functions for status display
function getStatusColor($status) {
    switch($status) {
        case ORDER_PENDING_CONFIRMATION: return 'warning';
        case ORDER_PENDING: return 'secondary';
        case ORDER_PREPARING: return 'info';
        case ORDER_READY: return 'success';
        case ORDER_DELIVERED: return 'primary';
        default: return 'dark';
    }
}

function getStatusLabel($status) {
    switch($status) {
        case ORDER_PENDING_CONFIRMATION: return 'Pendiente Confirmación';
        case ORDER_PENDING: return 'Pendiente';
        case ORDER_PREPARING: return 'En Preparación';
        case ORDER_READY: return 'Listo';
        case ORDER_DELIVERED: return 'Entregado';
        default: return ucfirst($status);
    }
}
?>

<style>
.table-danger {
    background-color: rgba(248, 215, 218, 0.3) !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>