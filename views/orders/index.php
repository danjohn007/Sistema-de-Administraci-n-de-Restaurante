<?php $title = 'Gestión de Pedidos'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-clipboard-check"></i> Gestión de Pedidos</h1>
    <div>
        <a href="<?= BASE_URL ?>/orders/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Pedido
        </a>
    </div>
</div>

<?php if (empty($orders)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-clipboard-check display-1 text-muted"></i>
            <h3 class="mt-3">No hay pedidos registrados</h3>
            <p class="text-muted">
                <?php if ($user['role'] === ROLE_WAITER): ?>
                    No tienes pedidos asignados actualmente.
                <?php else: ?>
                    Aún no se han registrado pedidos en el sistema.
                <?php endif; ?>
            </p>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/orders/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear Primer Pedido
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
                        <th>Mesero</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Items</th>
                        <th>Fecha</th>
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
                            <small class="text-muted"><?= htmlspecialchars($order['employee_code']) ?></small><br>
                            <?= htmlspecialchars($order['waiter_name']) ?>
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
                            <small><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/orders/show/<?= $order['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm" 
                                   title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($order['status'] !== ORDER_DELIVERED): ?>
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

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center border-primary">
            <div class="card-body">
                <i class="bi bi-clock-history display-4 text-primary"></i>
                <h5 class="mt-3">Pendientes</h5>
                <h3 class="text-primary">
                    <?= count(array_filter($orders, function($o) { return $o['status'] === ORDER_PENDING; })) ?>
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-warning">
            <div class="card-body">
                <i class="bi bi-gear display-4 text-warning"></i>
                <h5 class="mt-3">En Preparación</h5>
                <h3 class="text-warning">
                    <?= count(array_filter($orders, function($o) { return $o['status'] === ORDER_PREPARING; })) ?>
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-success">
            <div class="card-body">
                <i class="bi bi-check-circle display-4 text-success"></i>
                <h5 class="mt-3">Listos</h5>
                <h3 class="text-success">
                    <?= count(array_filter($orders, function($o) { return $o['status'] === ORDER_READY; })) ?>
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-info">
            <div class="card-body">
                <i class="bi bi-truck display-4 text-info"></i>
                <h5 class="mt-3">Entregados</h5>
                <h3 class="text-info">
                    <?= count(array_filter($orders, function($o) { return $o['status'] === ORDER_DELIVERED; })) ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<style>
.status-pendiente { background-color: #ffc107; color: #000; }
.status-en_preparacion { background-color: #fd7e14; color: #fff; }
.status-listo { background-color: #198754; color: #fff; }
.status-entregado { background-color: #0dcaf0; color: #000; }
</style>

<?php
function getOrderStatusText($status) {
    $statusTexts = [
        ORDER_PENDING => 'Pendiente',
        ORDER_PREPARING => 'En Preparación',
        ORDER_READY => 'Listo',
        ORDER_DELIVERED => 'Entregado'
    ];
    
    return $statusTexts[$status] ?? $status;
}
?>