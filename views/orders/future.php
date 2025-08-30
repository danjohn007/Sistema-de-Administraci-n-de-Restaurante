<?php $title = 'Pedidos Futuros'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-calendar-plus"></i> Pedidos Futuros</h1>
    <div>
        <a href="<?= BASE_URL ?>/orders" class="btn btn-secondary me-2">
            <i class="bi bi-calendar-day"></i> Pedidos de Hoy
        </a>
        <a href="<?= BASE_URL ?>/orders/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Pedido
        </a>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    Mostrando pedidos pickup programados para fechas futuras (después de hoy).
</div>

<?php if (empty($orders)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-calendar-x display-1 text-muted"></i>
            <h3 class="mt-3">No hay pedidos futuros</h3>
            <p class="text-muted">
                <?php if ($user['role'] === ROLE_WAITER): ?>
                    No tienes pedidos pickup programados para fechas futuras.
                <?php else: ?>
                    No hay pedidos pickup programados para fechas futuras en el sistema.
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
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mesa</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Items</th>
                        <th>Fecha Pickup</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td>
                            <span class="badge bg-info">Mesa <?= $order['table_number'] ?></span>
                        </td>
                        <td>
                            <i class="bi bi-person-fill text-info"></i> 
                            <small class="text-muted">Cliente:</small><br>
                            <?= htmlspecialchars($order['customer_name'] ?? 'Público') ?>
                            <br><span class="badge bg-warning">Pickup Futuro</span>
                            <?php if (!empty($order['customer_phone'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($order['customer_phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge status-<?= $order['status'] ?>">
                                <?= getOrderStatusText($order['status']) ?>
                            </span>
                        </td>
                        <td>
                            <strong>$<?= number_format($order['total'], 2) ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= $order['items_count'] ?> items</span>
                        </td>
                        <td>
                            <strong><?= date('d/m/Y', strtotime($order['pickup_datetime'])) ?></strong>
                            <br><small class="text-muted"><?= date('H:i', strtotime($order['pickup_datetime'])) ?></small>
                            <br><small class="text-success">
                                <?php 
                                $days = floor((strtotime($order['pickup_datetime']) - time()) / (60 * 60 * 24));
                                echo $days > 0 ? "En $days días" : "Hoy";
                                ?>
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/orders/show/<?= $order['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm" 
                                   title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($order['status'] === ORDER_PENDING_CONFIRMATION && ($user['role'] === ROLE_ADMIN || $user['role'] === ROLE_CASHIER)): ?>
                                <a href="<?= BASE_URL ?>/orders/confirmPublicOrder/<?= $order['id'] ?>" 
                                   class="btn btn-success btn-sm" 
                                   title="Confirmar pedido público">
                                    <i class="bi bi-check-circle"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($order['status'] !== ORDER_DELIVERED && $order['status'] !== ORDER_PENDING_CONFIRMATION): ?>
                                <a href="<?= BASE_URL ?>/orders/edit/<?= $order['id'] ?>" 
                                   class="btn btn-outline-warning btn-sm" 
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($user['role'] === ROLE_ADMIN): ?>
                                <a href="<?= BASE_URL ?>/orders/delete/<?= $order['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm" 
                                   title="Eliminar"
                                   onclick="return confirm('¿Está seguro de eliminar este pedido?')">
                                    <i class="bi bi-trash"></i>
                                </a>
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

<!-- Quick Stats for Future Orders -->
<div class="row mt-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-warning">
            <div class="card-body">
                <i class="bi bi-calendar-plus display-6 text-warning"></i>
                <h6 class="mt-2">Total Futuros</h6>
                <h4 class="text-warning">
                    <?= count($orders) ?>
                </h4>
            </div>
        </div>
    </div>
    <?php if ($user['role'] === ROLE_ADMIN || $user['role'] === ROLE_CASHIER): ?>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-danger">
            <div class="card-body">
                <i class="bi bi-exclamation-triangle display-6 text-danger"></i>
                <h6 class="mt-2">Sin Confirmar</h6>
                <h4 class="text-danger">
                    <?= count(array_filter($orders, function($o) { return $o['status'] === ORDER_PENDING_CONFIRMATION; })) ?>
                </h4>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-success">
            <div class="card-body">
                <i class="bi bi-currency-dollar display-6 text-success"></i>
                <h6 class="mt-2">Ventas Futuras</h6>
                <h4 class="text-success">
                    $<?= number_format(array_sum(array_column($orders, 'total')), 2) ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-info">
            <div class="card-body">
                <i class="bi bi-calendar-range display-6 text-info"></i>
                <h6 class="mt-2">Días Cubiertos</h6>
                <h4 class="text-info">
                    <?php 
                    $dates = array_unique(array_map(function($order) {
                        return date('Y-m-d', strtotime($order['pickup_datetime']));
                    }, $orders));
                    echo count($dates);
                    ?>
                </h4>
            </div>
        </div>
    </div>
</div>

<style>
.status-pendiente_confirmacion { background-color: #dc3545; color: #fff; }
.status-pendiente { background-color: #ffc107; color: #000; }
.status-en_preparacion { background-color: #fd7e14; color: #fff; }
.status-listo { background-color: #198754; color: #fff; }
.status-entregado { background-color: #0dcaf0; color: #000; }
</style>

<?php
function getOrderStatusText($status) {
    $statusTexts = [
        ORDER_PENDING_CONFIRMATION => 'Pendiente Confirmación',
        ORDER_PENDING => 'Pendiente',
        ORDER_PREPARING => 'En Preparación',
        ORDER_READY => 'Listo',
        ORDER_DELIVERED => 'Entregado'
    ];
    
    return $statusTexts[$status] ?? $status;
}
?>