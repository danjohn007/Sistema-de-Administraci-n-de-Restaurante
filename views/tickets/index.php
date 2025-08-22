<?php $title = 'Gestión de Tickets'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-receipt"></i> Gestión de Tickets</h1>
    <div>
        <a href="<?= BASE_URL ?>/tickets/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Generar Ticket
        </a>
        <?php if (in_array($user['role'], [ROLE_ADMIN, ROLE_CASHIER])): ?>
        <a href="<?= BASE_URL ?>/tickets/report" class="btn btn-outline-info">
            <i class="bi bi-graph-up"></i> Reportes
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/tickets" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="date" class="form-label">Fecha:</label>
                <input type="date" 
                       class="form-control" 
                       id="date" 
                       name="date" 
                       value="<?= htmlspecialchars($selectedDate) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
            <div class="col-md-7 text-end">
                <div class="d-flex justify-content-end gap-3">
                    <div class="text-center">
                        <small class="text-muted">Total Tickets</small>
                        <div class="h5 mb-0"><?= $salesReport['totals']['total_tickets'] ?? 0 ?></div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">Total Ventas</small>
                        <div class="h5 mb-0 text-success">$<?= number_format($salesReport['totals']['total_amount'] ?? 0, 2) ?></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (empty($tickets)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-receipt display-1 text-muted"></i>
            <h3 class="mt-3">No hay tickets registrados</h3>
            <p class="text-muted">
                <?php if ($user['role'] === ROLE_CASHIER): ?>
                    No has generado tickets para la fecha seleccionada.
                <?php else: ?>
                    No se han generado tickets para la fecha seleccionada.
                <?php endif; ?>
            </p>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/tickets/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Generar Primer Ticket
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
                        <th>Ticket #</th>
                        <th>Mesa</th>
                        <th>Cajero</th>
                        <th>Subtotal</th>
                        <th>Impuesto</th>
                        <th>Total</th>
                        <th>Método Pago</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($ticket['ticket_number']) ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-info">Mesa <?= $ticket['table_number'] ?></span>
                        </td>
                        <td>
                            <?= htmlspecialchars($ticket['cashier_name']) ?>
                        </td>
                        <td>
                            $<?= number_format($ticket['subtotal'], 2) ?>
                        </td>
                        <td>
                            $<?= number_format($ticket['tax'], 2) ?>
                        </td>
                        <td>
                            <strong>$<?= number_format($ticket['total'], 2) ?></strong>
                        </td>
                        <td>
                            <span class="badge payment-<?= $ticket['payment_method'] ?>">
                                <?= getPaymentMethodText($ticket['payment_method']) ?>
                            </span>
                        </td>
                        <td>
                            <small><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/tickets/show/<?= $ticket['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm" 
                                   title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/tickets/print/<?= $ticket['id'] ?>" 
                                   class="btn btn-outline-success btn-sm" 
                                   title="Imprimir" 
                                   target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <?php if ($user['role'] === ROLE_ADMIN): ?>
                                <a href="<?= BASE_URL ?>/tickets/delete/<?= $ticket['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm" 
                                   title="Eliminar"
                                   onclick="return confirm('¿Está seguro de eliminar este ticket?')">
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

<!-- Payment Method Summary -->
<?php if (!empty($salesReport['by_payment_method'])): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-credit-card"></i> Resumen por Método de Pago
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($salesReport['by_payment_method'] as $method): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-<?= getPaymentMethodIcon($method['payment_method']) ?> text-primary" style="font-size: 2rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1"><?= getPaymentMethodText($method['payment_method']) ?></h6>
                                        <p class="card-text mb-0">
                                            <strong><?= $method['method_count'] ?> tickets</strong><br>
                                            <span class="text-success">$<?= number_format($method['total_amount'], 2) ?></span>
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
<?php endif; ?>

<style>
.payment-efectivo { background-color: #198754; color: #fff; }
.payment-tarjeta { background-color: #0d6efd; color: #fff; }
.payment-transferencia { background-color: #6f42c1; color: #fff; }

.border-left-primary {
    border-left: 4px solid #0d6efd !important;
}
</style>

<?php
function getPaymentMethodText($method) {
    $methods = [
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta',
        'transferencia' => 'Transferencia'
    ];
    
    return $methods[$method] ?? $method;
}

function getPaymentMethodIcon($method) {
    $icons = [
        'efectivo' => 'cash',
        'tarjeta' => 'credit-card',
        'transferencia' => 'bank'
    ];
    
    return $icons[$method] ?? 'cash';
}
?>