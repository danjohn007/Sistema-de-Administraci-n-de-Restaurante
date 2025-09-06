<?php $title = 'Ticket ' . $ticket['ticket_number']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-receipt"></i> Ticket <?= htmlspecialchars($ticket['ticket_number']) ?></h1>
    <div>
        <a href="<?= BASE_URL ?>/tickets/print/<?= $ticket['id'] ?>" 
           class="btn btn-success" 
           target="_blank">
            <i class="bi bi-printer"></i> Imprimir
        </a>
        <a href="<?= BASE_URL ?>/tickets" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Tickets
        </a>
    </div>
</div>

<div class="row">
    <!-- Ticket Details -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Información del Ticket
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Número de Ticket:</label>
                    <div class="h5 text-primary"><?= htmlspecialchars($ticket['ticket_number']) ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Mesa:</label>
                    <span class="badge bg-info fs-6">Mesa <?= $ticket['table_number'] ?></span>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Mesero:</label>
                    <div>
                        <?= htmlspecialchars($ticket['waiter_name']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($ticket['employee_code']) ?></small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Cajero:</label>
                    <div><?= htmlspecialchars($ticket['cashier_name']) ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Método de Pago:</label>
                    <div>
                        <span class="badge payment-<?= $ticket['payment_method'] ?> fs-6">
                            <?= getPaymentMethodText($ticket['payment_method']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Fecha y Hora:</label>
                    <div><?= date('d/m/Y H:i:s', strtotime($ticket['created_at'])) ?></div>
                </div>
                
                <?php if (!empty($ticket['order_notes'])): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Notas del Pedido:</label>
                    <div class="alert alert-light mb-0">
                        <?= nl2br(htmlspecialchars($ticket['order_notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Ticket Items -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list"></i> Detalles del Ticket
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($ticket['items'])): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-exclamation-circle display-4 text-muted"></i>
                        <p class="text-muted mt-3">No hay items en este ticket</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Platillo</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ticket['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($item['dish_name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($item['category']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= $item['quantity'] ?></span>
                                    </td>
                                    <td>
                                        $<?= number_format($item['unit_price'], 2) ?>
                                    </td>
                                    <td>
                                        $<?= number_format($item['subtotal'], 2) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['notes'])): ?>
                                            <small><?= htmlspecialchars($item['notes']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Totals -->
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <strong>Subtotal:</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            $<?= number_format($ticket['subtotal'], 2) ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <strong>IVA (16%):</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            $<?= number_format($ticket['tax'], 2) ?>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <h5 class="mb-0">Total:</h5>
                                        </div>
                                        <div class="col-6 text-end">
                                            <h5 class="mb-0 text-success">$<?= number_format($ticket['total'], 2) ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.payment-efectivo { background-color: #198754; color: #fff; }
.payment-tarjeta { background-color: #0d6efd; color: #fff; }
.payment-transferencia { background-color: #6f42c1; color: #fff; }
.payment-intercambio { background-color: #17a2b8; color: #fff; }
.payment-pendiente_por_cobrar { background-color: #dc3545; color: #fff; }
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
?>