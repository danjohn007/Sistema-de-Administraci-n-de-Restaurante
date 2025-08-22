<?php $title = 'Dashboard'; ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
        <p class="text-muted mb-0">Bienvenido, <?= htmlspecialchars($user['name']) ?></p>
    </div>
    <div class="col-md-4 text-end">
        <div class="text-muted">
            <i class="bi bi-clock"></i> <span id="current-time"><?= date('d/m/Y H:i:s') ?></span>
        </div>
    </div>
</div>

<?php if ($user['role'] === ROLE_ADMIN): ?>
    <!-- Admin Dashboard -->
    <div class="row mb-4">
        <!-- Quick Stats Cards -->
        <div class="col-md-3 mb-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Total Mesas</h6>
                            <h3 class="mb-0"><?= $total_tables ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-grid-3x3-gap" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Ventas Diarias</h6>
                            <h3 class="mb-0"><?= formatCurrency($daily_sales['total_sales'] ?? 0) ?></h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Pedidos Pendientes</h6>
                            <h3 class="mb-0"><?= $pending_orders ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Pedidos Listos</h6>
                            <h3 class="mb-0"><?= $ready_orders ?></h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Table Status -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pie-chart"></i> Estado de Mesas
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($table_stats)): ?>
                        <?php foreach ($table_stats as $status => $stat): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="badge table-status status-<?= $status ?>">
                                    <?= getStatusText($status) ?>
                                </span>
                            </div>
                            <div>
                                <strong><?= $stat['count'] ?></strong> 
                                <small class="text-muted">(<?= $stat['percentage'] ?>%)</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No hay datos de mesas disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Popular Dishes -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-star"></i> Platillos Populares
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($popular_dishes)): ?>
                        <?php foreach ($popular_dishes as $dish): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?= htmlspecialchars($dish['name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($dish['category']) ?></small>
                            </div>
                            <div class="text-end">
                                <div><strong><?= $dish['total_quantity'] ?? 0 ?></strong> vendidos</div>
                                <small class="text-muted"><?= formatCurrency($dish['price']) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No hay datos de platillos disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($user['role'] === ROLE_WAITER): ?>
    <!-- Waiter Dashboard -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Mesas Asignadas</h6>
                            <h3 class="mb-0"><?= count($assigned_tables) ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-grid-3x3-gap" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Pedidos Hoy</h6>
                            <h3 class="mb-0"><?= $waiter_stats['total_orders'] ?? 0 ?></h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-clipboard-check" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Ventas Hoy</h6>
                            <h3 class="mb-0"><?= formatCurrency($waiter_stats['total_sales'] ?? 0) ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Pendientes</h6>
                            <h3 class="mb-0"><?= count($pending_orders) ?></h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-grid-3x3-gap"></i> Mis Mesas Asignadas
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($assigned_tables)): ?>
                        <div class="row">
                            <?php foreach ($assigned_tables as $table): ?>
                            <div class="col-md-2 mb-3">
                                <div class="card table-card <?= $table['status'] ?>" onclick="location.href='<?= BASE_URL ?>/orders/table/<?= $table['id'] ?>'">
                                    <div class="card-body text-center p-3">
                                        <h4 class="mb-1">Mesa <?= $table['number'] ?></h4>
                                        <span class="badge table-status status-<?= $table['status'] ?>">
                                            <?= getStatusText($table['status']) ?>
                                        </span>
                                        <div class="mt-2">
                                            <small class="text-muted">Capacidad: <?= $table['capacity'] ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No tienes mesas asignadas actualmente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($user['role'] === ROLE_CASHIER): ?>
    <!-- Cashier Dashboard -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Tickets Hoy</h6>
                            <h3 class="mb-0"><?= count($today_tickets) ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-receipt" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Total Vendido</h6>
                            <h3 class="mb-0"><?= formatCurrency($sales_report['totals']['total_amount'] ?? 0) ?></h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Listos para Cobro</h6>
                            <h3 class="mb-0"><?= count($ready_orders) ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Cuentas Solicitadas</h6>
                            <h3 class="mb-0"><?= count($bill_requested_tables) ?></h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-receipt-cutoff" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning"></i> Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($user['role'] === ROLE_ADMIN): ?>
                        <div class="col-md-2 mb-2">
                            <a href="<?= BASE_URL ?>/tables" class="btn btn-outline-primary w-100">
                                <i class="bi bi-grid-3x3-gap"></i><br>Gestionar Mesas
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="<?= BASE_URL ?>/dishes" class="btn btn-outline-success w-100">
                                <i class="bi bi-cup-hot"></i><br>Gestionar Menú
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="<?= BASE_URL ?>/waiters" class="btn btn-outline-info w-100">
                                <i class="bi bi-person-badge"></i><br>Gestionar Meseros
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-warning w-100">
                                <i class="bi bi-clipboard-check"></i><br>Ver Pedidos
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="<?= BASE_URL ?>/tickets" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-receipt"></i><br>Ver Tickets
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="<?= BASE_URL ?>/users/create" class="btn btn-outline-dark w-100">
                                <i class="bi bi-person-plus"></i><br>Nuevo Usuario
                            </a>
                        </div>
                    <?php elseif ($user['role'] === ROLE_WAITER): ?>
                        <div class="col-md-3 mb-2">
                            <a href="<?= BASE_URL ?>/orders/create" class="btn btn-outline-primary w-100">
                                <i class="bi bi-plus-circle"></i><br>Nuevo Pedido
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-success w-100">
                                <i class="bi bi-clipboard-check"></i><br>Mis Pedidos
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= BASE_URL ?>/tables" class="btn btn-outline-info w-100">
                                <i class="bi bi-grid-3x3-gap"></i><br>Ver Mesas
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= BASE_URL ?>/dishes" class="btn btn-outline-warning w-100">
                                <i class="bi bi-cup-hot"></i><br>Ver Menú
                            </a>
                        </div>
                    <?php elseif ($user['role'] === ROLE_CASHIER): ?>
                        <div class="col-md-4 mb-2">
                            <a href="<?= BASE_URL ?>/tickets/create" class="btn btn-outline-primary w-100">
                                <i class="bi bi-receipt-cutoff"></i><br>Generar Ticket
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="<?= BASE_URL ?>/tickets" class="btn btn-outline-success w-100">
                                <i class="bi bi-receipt"></i><br>Ver Tickets
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-info w-100">
                                <i class="bi bi-clipboard-check"></i><br>Pedidos Listos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function for formatting currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Helper function for status text
function getStatusText($status) {
    $statusTexts = [
        'disponible' => 'Disponible',
        'ocupada' => 'Ocupada',
        'cuenta_solicitada' => 'Cuenta Solicitada',
        'cerrada' => 'Cerrada',
        'pendiente' => 'Pendiente',
        'en_preparacion' => 'En Preparación',
        'listo' => 'Listo',
        'entregado' => 'Entregado'
    ];
    
    return $statusTexts[$status] ?? $status;
}
?>