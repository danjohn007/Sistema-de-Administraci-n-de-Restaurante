<?php $title = 'Top Clientes por Visitas'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people-fill"></i> Top Clientes por Visitas</h1>
    <div>
        <a href="<?= BASE_URL ?>/best_diners" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Filter Controls -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="limit" class="form-label">Número de clientes:</label>
                <select class="form-select" id="limit" name="limit" onchange="this.form.submit()">
                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>Top 10</option>
                    <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>Top 25</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>Top 50</option>
                    <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>Top 100</option>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if (empty($customers)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-people display-1 text-muted"></i>
            <h3 class="mt-3">No hay datos de clientes</h3>
            <p class="text-muted">
                Aún no se han registrado clientes con visitas en el sistema.
            </p>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Summary Stats -->
<div class="row mb-4">
    <?php
    $totalSpent = array_sum(array_column($customers, 'total_spent'));
    $totalVisits = array_sum(array_column($customers, 'total_visits'));
    $avgSpentPerCustomer = $totalSpent / count($customers);
    $avgVisitsPerCustomer = $totalVisits / count($customers);
    ?>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4><?= number_format($totalVisits) ?></h4>
                <small>Visitas Totales</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4>$<?= number_format($totalSpent, 2) ?></h4>
                <small>Consumo Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4><?= number_format($avgVisitsPerCustomer, 1) ?></h4>
                <small>Visitas Promedio</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>$<?= number_format($avgSpentPerCustomer, 2) ?></h4>
                <small>Gasto Promedio</small>
            </div>
        </div>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Top <?= $limit ?> Clientes por Frecuencia</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Ranking</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Total Visitas</th>
                        <th>Total Gastado</th>
                        <th>Promedio por Visita</th>
                        <th>Última Visita</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $index => $customer): ?>
                    <tr>
                        <td>
                            <?php if ($index < 3): ?>
                                <i class="bi bi-award-fill text-success"></i>
                            <?php endif; ?>
                            <strong>#<?= $index + 1 ?></strong>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($customer['name']) ?></strong>
                            <?php if ($customer['birthday']): ?>
                                <br><small class="text-muted">
                                    <i class="bi bi-cake"></i> <?= htmlspecialchars($customer['birthday']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($customer['phone']) ?></span>
                        </td>
                        <td>
                            <span class="h5 text-success"><?= $customer['total_visits'] ?></span>
                            <?php if ($customer['total_visits'] >= 10): ?>
                                <i class="bi bi-star-fill text-warning ms-1" title="Cliente VIP (10+ visitas)"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-primary">$<?= number_format($customer['total_spent'], 2) ?></span>
                        </td>
                        <td>
                            <?php $avgPerVisit = $customer['total_visits'] > 0 ? $customer['total_spent'] / $customer['total_visits'] : 0; ?>
                            <span class="text-info">$<?= number_format($avgPerVisit, 2) ?></span>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= date('d/m/Y', strtotime($customer['updated_at'])) ?>
                            </small>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/best_diners/customerDetail/<?= $customer['id'] ?>" 
                               class="btn btn-sm btn-outline-success">
                                <i class="bi bi-eye"></i> Ver Detalle
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Gráfica de Frecuencia de Visitas</h5>
            </div>
            <div class="card-body">
                <canvas id="visitsChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($customers)): ?>
    const customers = <?= json_encode(array_slice($customers, 0, 10)) ?>;
    
    const ctx = document.getElementById('visitsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: customers.map(c => c.name),
            datasets: [{
                label: 'Número de Visitas',
                data: customers.map(c => parseInt(c.total_visits)),
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
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
                        stepSize: 1
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const customer = customers[context.dataIndex];
                            return [
                                'Visitas: ' + customer.total_visits,
                                'Gastado: $' + parseFloat(customer.total_spent).toFixed(2)
                            ];
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>