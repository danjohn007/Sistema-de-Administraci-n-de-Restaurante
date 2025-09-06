<?php $title = 'Mejores Comensales'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-star-fill"></i> Mejores Comensales</h1>
    <div>
        <a href="<?= BASE_URL ?>/best_diners/report" class="btn btn-primary">
            <i class="bi bi-graph-up"></i> Reporte Completo
        </a>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<div class="row">
    <!-- Quick Stats Cards -->
    <div class="col-md-6 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Top por Consumo</h5>
                        <p class="card-text">Los clientes que más han gastado</p>
                    </div>
                    <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                </div>
                <a href="<?= BASE_URL ?>/best_diners/bySpending" class="btn btn-outline-light btn-sm">
                    Ver Lista <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Top por Visitas</h5>
                        <p class="card-text">Los clientes más frecuentes</p>
                    </div>
                    <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                </div>
                <a href="<?= BASE_URL ?>/best_diners/byVisits" class="btn btn-outline-light btn-sm">
                    Ver Lista <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart"></i> Top 5 por Consumo
                </h5>
            </div>
            <div class="card-body">
                <canvas id="spendingChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Top 5 por Visitas
                </h5>
            </div>
            <div class="card-body">
                <canvas id="visitsChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Quick Access Menu -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Accesos Rápidos</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="<?= BASE_URL ?>/best_diners/bySpending?limit=25" class="btn btn-outline-primary w-100">
                            <i class="bi bi-trophy"></i><br>
                            Top 25 Gastadores
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= BASE_URL ?>/best_diners/byVisits?limit=25" class="btn btn-outline-success w-100">
                            <i class="bi bi-award"></i><br>
                            Top 25 Visitantes
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= BASE_URL ?>/best_diners/report" class="btn btn-outline-info w-100">
                            <i class="bi bi-file-earmark-bar-graph"></i><br>
                            Reporte Detallado
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= BASE_URL ?>/best_diners" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-people"></i><br>
                            Inicio Clientes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load spending chart
    fetch('<?= BASE_URL ?>/best_diners/analytics?type=spending&limit=5')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                createSpendingChart(data.data);
            }
        })
        .catch(error => console.error('Error loading spending chart:', error));
    
    // Load visits chart
    fetch('<?= BASE_URL ?>/best_diners/analytics?type=visits&limit=5')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                createVisitsChart(data.data);
            }
        })
        .catch(error => console.error('Error loading visits chart:', error));
});

function createSpendingChart(data) {
    const ctx = document.getElementById('spendingChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.name),
            datasets: [{
                label: 'Total Gastado ($)',
                data: data.map(item => item.value),
                backgroundColor: 'rgba(13, 110, 253, 0.5)',
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
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const customer = data[context.dataIndex];
                            return [
                                'Gastado: $' + customer.value.toFixed(2),
                                'Visitas: ' + customer.visits
                            ];
                        }
                    }
                }
            }
        }
    });
}

function createVisitsChart(data) {
    const ctx = document.getElementById('visitsChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(item => item.name),
            datasets: [{
                data: data.map(item => item.value),
                backgroundColor: [
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(13, 202, 240, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const customer = data[context.dataIndex];
                            return [
                                customer.name + ': ' + customer.value + ' visitas',
                                'Gastado: $' + customer.spending.toFixed(2)
                            ];
                        }
                    }
                }
            }
        }
    });
}
</script>