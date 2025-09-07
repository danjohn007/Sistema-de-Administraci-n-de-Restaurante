<?php $title = 'Detalle del Cliente - ' . $customer['name']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-circle"></i> <?= htmlspecialchars($customer['name']) ?></h1>
    <div>
        <a href="<?= BASE_URL ?>/best_diners" class="btn btn-outline-secondary">
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
                <i class="bi bi-graph-up mb-2" style="font-size: 2rem;"></i>
                <h4>$<?= number_format($customer['avg_order_value'] ?? 0, 2) ?></h4>
                <small>Promedio por Pedido</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="bi bi-clock mb-2" style="font-size: 2rem;"></i>
                <h4><?= date('d/m/Y', strtotime($customer['last_visit'] ?? $customer['updated_at'])) ?></h4>
                <small>Última Visita</small>
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
                    <i class="bi bi-info-circle"></i> Información del Cliente
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
                    <?php if ($customer['email']): ?>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?= htmlspecialchars($customer['email']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php 
                    if (!empty($customer['birthday_day']) && !empty($customer['birthday_month'])): 
                        $birthdayFormatted = sprintf('%02d/%02d', $customer['birthday_day'], $customer['birthday_month']);
                    ?>
                    <tr>
                        <td><strong>Cumpleaños:</strong></td>
                        <td>
                            <i class="bi bi-cake text-warning"></i> <?= htmlspecialchars($birthdayFormatted) ?>
                        </td>
                    </tr>
                    <?php elseif (!empty($customer['birthday'])): ?>
                    <tr>
                        <td><strong>Cumpleaños:</strong></td>
                        <td>
                            <i class="bi bi-cake text-warning"></i> <?= htmlspecialchars($customer['birthday']) ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td><strong>Cliente desde:</strong></td>
                        <td><?= date('d/m/Y', strtotime($customer['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            <?php if ($customer['total_visits'] >= 10): ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-star-fill"></i> Cliente VIP
                                </span>
                            <?php elseif ($customer['total_visits'] >= 5): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-heart-fill"></i> Cliente Frecuente
                                </span>
                            <?php else: ?>
                                <span class="badge bg-primary">
                                    <i class="bi bi-person"></i> Cliente Regular
                                </span>
                            <?php endif; ?>
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
                <canvas id="customerStatsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Order History -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-clipboard-check"></i> Historial de Pedidos
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <div class="text-center py-4">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3">No hay pedidos registrados</h4>
                <p class="text-muted">Este cliente aún no tiene pedidos asociados.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Pedido #</th>
                            <th>Fecha</th>
                            <th>Mesa</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Items</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?= $order['id'] ?></strong></td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                <?php if ($order['is_pickup']): ?>
                                    <br><small class="text-info">
                                        <i class="bi bi-bag"></i> Pickup
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($order['table_number']): ?>
                                    <span class="badge bg-info">Mesa <?= $order['table_number'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Sin mesa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'pendiente_confirmacion' => 'warning',
                                    'pendiente' => 'primary',
                                    'en_preparacion' => 'info',
                                    'listo' => 'success',
                                    'entregado' => 'secondary'
                                ];
                                $statusText = [
                                    'pendiente_confirmacion' => 'Pendiente Confirmación',
                                    'pendiente' => 'Pendiente',
                                    'en_preparacion' => 'En Preparación',
                                    'listo' => 'Listo',
                                    'entregado' => 'Entregado'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusClass[$order['status']] ?? 'secondary' ?>">
                                    <?= $statusText[$order['status']] ?? ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <strong>$<?= number_format($order['total'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark"><?= $order['items_count'] ?> items</span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/orders/show/<?= $order['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Order Summary Stats -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <h5><?= count($orders) ?></h5>
                        <small class="text-muted">Pedidos Totales</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5>$<?= number_format(array_sum(array_column($orders, 'total')), 2) ?></h5>
                        <small class="text-muted">Total Gastado</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5><?= array_sum(array_column($orders, 'items_count')) ?></h5>
                        <small class="text-muted">Items Totales</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5>$<?= count($orders) > 0 ? number_format(array_sum(array_column($orders, 'total')) / count($orders), 2) : '0.00' ?></h5>
                        <small class="text-muted">Promedio por Pedido</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Customer stats chart
    const ctx = document.getElementById('customerStatsChart').getContext('2d');
    
    const monthlyData = [];
    const monthlySpending = [];
    
    // Process order data to create monthly stats
    <?php if (!empty($orders)): ?>
    const orders = <?= json_encode($orders) ?>;
    const monthlyStats = {};
    
    orders.forEach(order => {
        const date = new Date(order.created_at);
        const monthKey = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
        
        if (!monthlyStats[monthKey]) {
            monthlyStats[monthKey] = { orders: 0, spending: 0 };
        }
        
        monthlyStats[monthKey].orders++;
        monthlyStats[monthKey].spending += parseFloat(order.total);
    });
    
    // Sort by month and prepare data
    const sortedMonths = Object.keys(monthlyStats).sort();
    const months = sortedMonths.map(month => {
        const [year, monthNum] = month.split('-');
        const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
                           'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return monthNames[parseInt(monthNum) - 1] + ' ' + year;
    });
    
    const ordersData = sortedMonths.map(month => monthlyStats[month].orders);
    const spendingData = sortedMonths.map(month => monthlyStats[month].spending);
    <?php else: ?>
    const months = ['Sin datos'];
    const ordersData = [0];
    const spendingData = [0];
    <?php endif; ?>
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Pedidos',
                data: ordersData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                yAxisID: 'y'
            }, {
                label: 'Gasto ($)',
                data: spendingData,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Mes'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Número de Pedidos'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Gasto ($)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
});
</script>