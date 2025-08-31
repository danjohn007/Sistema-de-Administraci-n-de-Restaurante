<?php $title = 'Reporte Completo - Mejores Comensales'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-bar-chart"></i> Reporte Completo - Mejores Comensales</h1>
    <div>
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="bi bi-printer"></i> Imprimir
        </button>
        <a href="<?= BASE_URL ?>/best_diners" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Fecha Inicio:</label>
                <input type="date" 
                       class="form-control" 
                       id="start_date" 
                       name="start_date" 
                       value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Fecha Fin:</label>
                <input type="date" 
                       class="form-control" 
                       id="end_date" 
                       name="end_date" 
                       value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
            <div class="col-md-4 text-end">
                <div class="text-muted">
                    <small>Período: <?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?></small>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <?php
    $totalCustomers = count($topBySpending) + count($topByVisits);
    $totalSpending = array_sum(array_column($topBySpending, 'total_spent'));
    $totalVisits = array_sum(array_column($topByVisits, 'total_visits'));
    $avgSpending = $totalCustomers > 0 ? $totalSpending / count($topBySpending) : 0;
    ?>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="bi bi-people-fill mb-2" style="font-size: 2rem;"></i>
                <h4><?= count($topBySpending) ?></h4>
                <small>Top Clientes por Consumo</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="bi bi-currency-dollar mb-2" style="font-size: 2rem;"></i>
                <h4>$<?= number_format($totalSpending, 2) ?></h4>
                <small>Consumo Total Top 10</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check mb-2" style="font-size: 2rem;"></i>
                <h4><?= $totalVisits ?></h4>
                <small>Visitas Total Top 10</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="bi bi-graph-up mb-2" style="font-size: 2rem;"></i>
                <h4>$<?= number_format($avgSpending, 2) ?></h4>
                <small>Promedio por Cliente</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top 10 por Consumo</h5>
            </div>
            <div class="card-body">
                <canvas id="spendingChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top 10 por Visitas</h5>
            </div>
            <div class="card-body">
                <canvas id="visitsChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Customer Growth Chart -->
<?php if (!empty($customerGrowth)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Crecimiento de Clientes (Últimos 12 meses)</h5>
            </div>
            <div class="card-body">
                <canvas id="growthChart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Monthly Statistics Table -->
<?php if (!empty($monthlyStats)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Estadísticas Mensuales</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Mes</th>
                                <th>Clientes Únicos</th>
                                <th>Total Pedidos</th>
                                <th>Ingresos</th>
                                <th>Ticket Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthlyStats as $stat): ?>
                            <tr>
                                <td><?= date('M Y', strtotime($stat['month'] . '-01')) ?></td>
                                <td><?= $stat['unique_customers'] ?></td>
                                <td><?= $stat['total_orders'] ?></td>
                                <td>$<?= number_format($stat['total_revenue'], 2) ?></td>
                                <td>$<?= number_format($stat['avg_order_value'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Top Tables -->
<div class="row">
    <!-- Top by Spending -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top 10 por Consumo</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Consumo</th>
                                <th>Visitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($topBySpending, 0, 10) as $index => $customer): ?>
                            <tr>
                                <td>
                                    <?php if ($index < 3): ?>
                                        <i class="bi bi-trophy-fill text-warning"></i>
                                    <?php endif; ?>
                                    <?= $index + 1 ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($customer['name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($customer['phone']) ?></small>
                                </td>
                                <td><strong>$<?= number_format($customer['total_spent'], 2) ?></strong></td>
                                <td><?= $customer['total_visits'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top by Visits -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top 10 por Visitas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Visitas</th>
                                <th>Consumo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($topByVisits, 0, 10) as $index => $customer): ?>
                            <tr>
                                <td>
                                    <?php if ($index < 3): ?>
                                        <i class="bi bi-award-fill text-success"></i>
                                    <?php endif; ?>
                                    <?= $index + 1 ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($customer['name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($customer['phone']) ?></small>
                                </td>
                                <td><strong><?= $customer['total_visits'] ?></strong></td>
                                <td>$<?= number_format($customer['total_spent'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Footer -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <p class="text-muted mb-0">
                    Reporte generado el <?= date('d/m/Y H:i') ?> | 
                    Sistema de Administración de Restaurante |
                    Datos del período: <?= date('d/m/Y', strtotime($startDate)) ?> al <?= date('d/m/Y', strtotime($endDate)) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Spending Chart
    const spendingCtx = document.getElementById('spendingChart').getContext('2d');
    const spendingData = <?= json_encode(array_slice($topBySpending, 0, 10)) ?>;
    
    new Chart(spendingCtx, {
        type: 'bar',
        data: {
            labels: spendingData.map(c => c.name),
            datasets: [{
                label: 'Consumo ($)',
                data: spendingData.map(c => parseFloat(c.total_spent)),
                backgroundColor: 'rgba(25, 135, 84, 0.6)',
                borderColor: 'rgba(25, 135, 84, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
    
    // Visits Chart
    const visitsCtx = document.getElementById('visitsChart').getContext('2d');
    const visitsData = <?= json_encode(array_slice($topByVisits, 0, 10)) ?>;
    
    new Chart(visitsCtx, {
        type: 'doughnut',
        data: {
            labels: visitsData.map(c => c.name),
            datasets: [{
                data: visitsData.map(c => parseInt(c.total_visits)),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(199, 199, 199, 0.8)',
                    'rgba(83, 102, 255, 0.8)',
                    'rgba(255, 99, 255, 0.8)',
                    'rgba(99, 255, 132, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Customer Growth Chart
    <?php if (!empty($customerGrowth)): ?>
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    const growthData = <?= json_encode($customerGrowth) ?>;
    
    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: growthData.map(d => {
                const [year, month] = d.month.split('-');
                const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
                                   'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                return monthNames[parseInt(month) - 1] + ' ' + year;
            }),
            datasets: [{
                label: 'Nuevos Clientes',
                data: growthData.map(d => parseInt(d.new_customers)),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<style>
@media print {
    .btn, .card-header .btn, .d-flex .btn {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
    
    .row {
        page-break-inside: avoid;
    }
}</style>