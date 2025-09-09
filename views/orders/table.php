<?php $title = 'Pedidos de Mesa ' . ($table['number'] ?? ''); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-grid-3x3-gap"></i> Pedidos de Mesa <?= htmlspecialchars($table['number'] ?? '') ?></h1>
    <div>
        <a href="<?= BASE_URL ?>/orders/create?table_id=<?= $table['id'] ?>" class="btn btn-primary me-2">
            <i class="bi bi-plus-circle"></i> Nuevo Pedido
        </a>
        <a href="<?= BASE_URL ?>/tables" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Mesas
        </a>
    </div>
</div>

<!-- Table Info Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <h6 class="text-muted">Mesa</h6>
                <h4><i class="bi bi-table"></i> Mesa <?= htmlspecialchars($table['number']) ?></h4>
            </div>
            <div class="col-md-3">
                <h6 class="text-muted">Capacidad</h6>
                <p><i class="bi bi-people"></i> <?= htmlspecialchars($table['capacity']) ?> personas</p>
            </div>
            <div class="col-md-3">
                <h6 class="text-muted">Estado</h6>
                <?php
                $statusClass = '';
                $statusText = '';
                switch($table['status']) {
                    case TABLE_AVAILABLE:
                        $statusClass = 'bg-success';
                        $statusText = 'Disponible';
                        break;
                    case TABLE_OCCUPIED:
                        $statusClass = 'bg-warning';
                        $statusText = 'Ocupada';
                        break;
                    case TABLE_BILL_REQUESTED:
                        $statusClass = 'bg-info';
                        $statusText = 'Cuenta Solicitada';
                        break;
                    case TABLE_CLOSED:
                        $statusClass = 'bg-danger';
                        $statusText = 'Cerrada';
                        break;
                    default:
                        $statusClass = 'bg-secondary';
                        $statusText = ucfirst($table['status']);
                }
                ?>
                <span class="badge <?= $statusClass ?>">
                    <?= $statusText ?>
                </span>
            </div>
            <div class="col-md-3">
                <h6 class="text-muted">Mesero Asignado</h6>
                <?php if (isset($table['waiter_name']) && $table['waiter_name']): ?>
                    <p><i class="bi bi-person-badge"></i> <?= htmlspecialchars($table['waiter_name']) ?></p>
                <?php else: ?>
                    <p class="text-muted"><i class="bi bi-person-x"></i> Sin asignar</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Orders List -->
<?php if (empty($orders)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-clipboard-x display-1 text-muted"></i>
            <h3 class="mt-3">No hay pedidos para esta mesa</h3>
            <p class="text-muted">
                Esta mesa no tiene pedidos registrados en el día de hoy.
            </p>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/orders/create?table_id=<?= $table['id'] ?>" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Crear Primer Pedido
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Pedidos de Hoy</h5>
            <span class="badge bg-primary"><?= count($orders) ?> pedido(s)</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($orders as $order): ?>
            <div class="col-md-6 mb-3">
                <div class="card border-start border-4 <?= $order['status'] === ORDER_DELIVERED ? 'border-success' : ($order['status'] === ORDER_READY ? 'border-info' : 'border-warning') ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-receipt"></i> Pedido #<?= $order['id'] ?>
                            </h6>
                            <?php
                            $statusClass = '';
                            $statusText = '';
                            switch($order['status']) {
                                case ORDER_PENDING:
                                    $statusClass = 'bg-warning';
                                    $statusText = 'Pendiente';
                                    break;
                                case ORDER_PREPARING:
                                    $statusClass = 'bg-info';
                                    $statusText = 'En Preparación';
                                    break;
                                case ORDER_READY:
                                    $statusClass = 'bg-primary';
                                    $statusText = 'Listo';
                                    break;
                                case ORDER_DELIVERED:
                                    $statusClass = 'bg-success';
                                    $statusText = 'Entregado';
                                    break;
                                default:
                                    $statusClass = 'bg-secondary';
                                    $statusText = ucfirst($order['status']);
                            }
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                        
                        <div class="row text-sm">
                            <div class="col-6">
                                <small class="text-muted">Hora:</small><br>
                                <small><?= date('H:i', strtotime($order['created_at'])) ?></small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Total:</small><br>
                                <strong class="text-success">$<?= number_format($order['total'], 2) ?></strong>
                            </div>
                        </div>
                        
                        <?php if (isset($order['waiter_name']) && $order['waiter_name']): ?>
                        <div class="mt-2">
                            <small class="text-muted">Mesero:</small>
                            <small><?= htmlspecialchars($order['waiter_name']) ?></small>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($order['customer_name']): ?>
                        <div class="mt-2">
                            <small class="text-muted">Cliente:</small>
                            <small><?= htmlspecialchars($order['customer_name']) ?></small>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($order['notes']): ?>
                        <div class="mt-2">
                            <small class="text-muted">Notas:</small>
                            <small class="d-block"><?= htmlspecialchars($order['notes']) ?></small>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/orders/view/<?= $order['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <?php if ($order['status'] !== ORDER_DELIVERED): ?>
                                <a href="<?= BASE_URL ?>/orders/edit/<?= $order['id'] ?>" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>