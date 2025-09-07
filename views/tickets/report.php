<?php $title = 'Reportes de Ventas - Tickets'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-graph-up"></i> Reportes de Ventas</h1>
    <div>
        <a href="<?= BASE_URL ?>/tickets" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Tickets
        </a>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/tickets/report" class="row g-3 align-items-end">
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
                    <i class="bi bi-search"></i> Generar Reporte
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

<?php if (empty($salesData)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-graph-down display-1 text-muted"></i>
            <h3 class="mt-3">No hay datos de ventas</h3>
            <p class="text-muted">
                No se encontraron tickets en el rango de fechas seleccionado.
            </p>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/tickets/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Generar Primer Ticket
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Summary Cards -->
<?php
// Calculate totals
$totalTickets = 0;
$totalAmount = 0;
$totalSubtotal = 0;
$totalTax = 0;
$paymentMethods = [];

foreach ($salesData as $row) {
    $totalTickets += $row['total_tickets'];
    $totalAmount += $row['total_amount'];
    $totalSubtotal += $row['total_subtotal'];
    $totalTax += $row['total_tax'];
    
    $method = $row['payment_method'];
    if (!isset($paymentMethods[$method])) {
        $paymentMethods[$method] = [
            'count' => 0,
            'amount' => 0
        ];
    }
    $paymentMethods[$method]['count'] += $row['method_count'];
    $paymentMethods[$method]['amount'] += $row['total_amount'];
}
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-1">Total Tickets</h6>
                        <h3 class="mb-0"><?= $totalTickets ?></h3>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-receipt" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-1">Total Ventas</h6>
                        <h3 class="mb-0 text-success">$<?= number_format($totalAmount, 2) ?></h3>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-cash-coin" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-1">Subtotal</h6>
                        <h3 class="mb-0 text-info">$<?= number_format($totalSubtotal, 2) ?></h3>
                    </div>
                    <div class="text-info">
                        <i class="bi bi-calculator" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-1">Impuestos</h6>
                        <h3 class="mb-0 text-warning">$<?= number_format($totalTax, 2) ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-percent" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Methods Summary -->
<?php if (!empty($paymentMethods)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-credit-card"></i> Resumen por Método de Pago
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($paymentMethods as $method => $data): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-<?= getPaymentMethodColor($method) ?>">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-<?= getPaymentMethodIcon($method) ?> text-<?= getPaymentMethodColor($method) ?>" style="font-size: 2rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1"><?= getPaymentMethodText($method) ?></h6>
                                        <p class="card-text mb-0">
                                            <strong><?= $data['count'] ?> tickets</strong><br>
                                            <span class="text-success">$<?= number_format($data['amount'], 2) ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Daily Sales Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-table"></i> Detalle de Ventas por Día
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Método de Pago</th>
                        <th>Tickets</th>
                        <th>Subtotal</th>
                        <th>Impuestos</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $dailyTotals = [];
                    foreach ($salesData as $row): 
                        $date = $row['date'];
                        if (!isset($dailyTotals[$date])) {
                            $dailyTotals[$date] = [
                                'tickets' => 0,
                                'subtotal' => 0,
                                'tax' => 0,
                                'total' => 0
                            ];
                        }
                        $dailyTotals[$date]['tickets'] += $row['total_tickets'];
                        $dailyTotals[$date]['subtotal'] += $row['total_subtotal'];
                        $dailyTotals[$date]['tax'] += $row['total_tax'];
                        $dailyTotals[$date]['total'] += $row['total_amount'];
                    ?>
                    <tr>
                        <td>
                            <strong><?= date('d/m/Y', strtotime($row['date'])) ?></strong>
                        </td>
                        <td>
                            <span class="badge payment-<?= $row['payment_method'] ?>">
                                <?= getPaymentMethodText($row['payment_method']) ?>
                            </span>
                        </td>
                        <td><?= $row['total_tickets'] ?></td>
                        <td>$<?= number_format($row['total_subtotal'], 2) ?></td>
                        <td>$<?= number_format($row['total_tax'], 2) ?></td>
                        <td><strong>$<?= number_format($row['total_amount'], 2) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="2">TOTALES</th>
                        <th><?= $totalTickets ?></th>
                        <th>$<?= number_format($totalSubtotal, 2) ?></th>
                        <th>$<?= number_format($totalTax, 2) ?></th>
                        <th>$<?= number_format($totalAmount, 2) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Daily Summary -->
<?php if (count($dailyTotals) > 1): ?>
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-calendar-week"></i> Resumen por Día
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Total Tickets</th>
                        <th>Total Ventas</th>
                        <th>Promedio por Ticket</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailyTotals as $date => $totals): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($date)) ?></td>
                        <td><?= $totals['tickets'] ?></td>
                        <td>$<?= number_format($totals['total'], 2) ?></td>
                        <td>$<?= $totals['tickets'] > 0 ? number_format($totals['total'] / $totals['tickets'], 2) : '0.00' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<style>
.payment-efectivo { background-color: #198754; color: #fff; }
.payment-tarjeta { background-color: #0d6efd; color: #fff; }
.payment-transferencia { background-color: #6f42c1; color: #fff; }
.payment-intercambio { background-color: #17a2b8; color: #fff; }
.payment-pendiente_por_cobrar { background-color: #dc3545; color: #fff; }

.border-left-primary { border-left: 4px solid #0d6efd !important; }
.border-left-success { border-left: 4px solid #198754 !important; }
.border-left-warning { border-left: 4px solid #ffc107 !important; }

.stat-card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
}

.stat-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.stat-card.primary {
    border-left: 4px solid #0d6efd;
}

.stat-card.success {
    border-left: 4px solid #198754;
}

.stat-card.info {
    border-left: 4px solid #0dcaf0;
}

.stat-card.warning {
    border-left: 4px solid #ffc107;
}
</style>

<?php
function getPaymentMethodText($method) {
    $methods = [
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta',
        'transferencia' => 'Transferencia',
        'intercambio' => 'Intercambio',
        'pendiente_por_cobrar' => 'Pendiente por Cobrar'
    ];
    
    return $methods[$method] ?? $method;
}

function getPaymentMethodIcon($method) {
    $icons = [
        'efectivo' => 'cash',
        'tarjeta' => 'credit-card',
        'transferencia' => 'bank',
        'intercambio' => 'arrow-left-right',
        'pendiente_por_cobrar' => 'clock-history'
    ];
    
    return $icons[$method] ?? 'cash';
}

function getPaymentMethodColor($method) {
    $colors = [
        'efectivo' => 'success',
        'tarjeta' => 'primary',
        'transferencia' => 'warning',
        'intercambio' => 'info',
        'pendiente_por_cobrar' => 'danger'
    ];
    
    return $colors[$method] ?? 'secondary';
}
?>