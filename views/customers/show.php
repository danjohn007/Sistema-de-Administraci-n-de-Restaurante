<?php $title = 'Detalles del Cliente - ' . $customer['name']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-circle"></i> <?= htmlspecialchars($customer['name']) ?></h1>
    <div>
        <a href="<?= BASE_URL ?>/customers/edit/<?= $customer['id'] ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="<?= BASE_URL ?>/customers" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Customer Info Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="bi bi-currency-dollar mb-2" style="font-size: 2rem;"></i>
                <h4>$<?= number_format($customer['total_spent'], 2) ?></h4>
                <small>Total Gastado</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check mb-2" style="font-size: 2rem;"></i>
                <h4><?= $customer['total_visits'] ?></h4>
                <small>Total Visitas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="bi bi-calculator mb-2" style="font-size: 2rem;"></i>
                <h4>$<?= $customer['total_visits'] > 0 ? number_format($customer['total_spent'] / $customer['total_visits'], 2) : '0.00' ?></h4>
                <small>Promedio por Visita</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="bi bi-graph-up mb-2" style="font-size: 2rem;"></i>
                <h4><?= isset($customer['order_count']) ? $customer['order_count'] : $customer['total_visits'] ?></h4>
                <small>Órdenes Totales</small>
            </div>
        </div>
    </div>
</div>

<!-- Customer Details -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-badge"></i> Información Personal
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Nombre:</strong></td>
                        <td><?= htmlspecialchars($customer['name']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Teléfono:</strong></td>
                        <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($customer['phone']) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?= $customer['email'] ? htmlspecialchars($customer['email']) : '<span class="text-muted">No registrado</span>' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Cumpleaños:</strong></td>
                        <td>
                            <?php 
                            if (!empty($customer['birthday_day']) && !empty($customer['birthday_month'])): 
                                $birthdayFormatted = sprintf('%02d/%02d', $customer['birthday_day'], $customer['birthday_month']);
                            ?>
                                <i class="bi bi-cake text-warning"></i> <?= htmlspecialchars($birthdayFormatted) ?>
                            <?php elseif (!empty($customer['birthday'])): ?>
                                <i class="bi bi-cake text-warning"></i> <?= htmlspecialchars($customer['birthday']) ?>
                            <?php else: ?>
                                <span class="text-muted">No registrado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Cliente desde:</strong></td>
                        <td><?= date('d/m/Y', strtotime($customer['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Última visita:</strong></td>
                        <td>
                            <?= isset($customer['last_visit']) && $customer['last_visit'] 
                                ? date('d/m/Y H:i', strtotime($customer['last_visit'])) 
                                : '<span class="text-muted">Nunca</span>' ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart"></i> Estadísticas
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h5 class="text-primary"><?= $customer['total_visits'] ?></h5>
                            <small class="text-muted">Visitas</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h5 class="text-success">$<?= number_format($customer['total_spent'], 2) ?></h5>
                        <small class="text-muted">Gastado</small>
                    </div>
                    <div class="col-6">
                        <div class="border-end">
                            <h5 class="text-info">$<?= isset($customer['avg_order_value']) ? number_format($customer['avg_order_value'], 2) : '0.00' ?></h5>
                            <small class="text-muted">Promedio por Orden</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h5 class="text-warning"><?= isset($customer['order_count']) ? $customer['order_count'] : '0' ?></h5>
                        <small class="text-muted">Órdenes</small>
                    </div>
                </div>
                
                <?php if ($customer['total_visits'] > 0): ?>
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Frecuencia de visitas</span>
                        <?php 
                        $daysSinceFirst = (time() - strtotime($customer['created_at'])) / (60 * 60 * 24);
                        $frequency = $daysSinceFirst > 0 ? round($customer['total_visits'] / ($daysSinceFirst / 30), 1) : 0;
                        ?>
                        <span class="badge bg-info"><?= $frequency ?> visitas/mes</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Order History -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-clock-history"></i> Historial de Pedidos
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($orders)): ?>
        <div class="text-center py-4">
            <i class="bi bi-clipboard-x display-4 text-muted"></i>
            <h5 class="mt-3">Sin pedidos registrados</h5>
            <p class="text-muted">Este cliente aún no ha realizado ningún pedido.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Mesa</th>
                        <th>Mesero</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <small><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
                        </td>
                        <td>
                            <?= $order['table_number'] ? 'Mesa ' . $order['table_number'] : '<span class="text-muted">Para llevar</span>' ?>
                        </td>
                        <td>
                            <?= $order['waiter_name'] ? htmlspecialchars($order['waiter_name']) : '<span class="text-muted">-</span>' ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= $order['items_count'] ?> items</span>
                        </td>
                        <td>
                            <strong>$<?= number_format($order['total'], 2) ?></strong>
                        </td>
                        <td>
                            <?php
                            $statusClasses = [
                                'pendiente_confirmacion' => 'bg-warning',
                                'pendiente' => 'bg-info',
                                'en_preparacion' => 'bg-primary',
                                'listo' => 'bg-success',
                                'entregado' => 'bg-dark'
                            ];
                            $statusNames = [
                                'pendiente_confirmacion' => 'Pendiente Confirmación',
                                'pendiente' => 'Pendiente',
                                'en_preparacion' => 'En Preparación',
                                'listo' => 'Listo',
                                'entregado' => 'Entregado'
                            ];
                            $statusClass = $statusClasses[$order['status']] ?? 'bg-secondary';
                            $statusName = $statusNames[$order['status']] ?? ucfirst($order['status']);
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $statusName ?></span>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/orders/show/<?= $order['id'] ?>" 
                               class="btn btn-outline-primary btn-sm" 
                               title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>