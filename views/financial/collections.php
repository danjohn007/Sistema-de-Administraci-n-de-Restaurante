<?php $title = 'Gestión de Cobranza'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-cash-coin"></i> Gestión de Cobranza</h1>
    <div class="btn-group">
        <a href="<?= BASE_URL ?>/financial" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
</div>

<?php if (empty($pending_tickets)): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i>
    <strong>¡Excelente!</strong> No hay cuentas pendientes por cobrar.
</div>
<?php else: ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-clock-history"></i> Cuentas Pendientes por Cobrar
            <span class="badge bg-danger"><?= count($pending_tickets) ?></span>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Mesa</th>
                        <th>Mesero</th>
                        <th>Cajero</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_tickets as $ticket): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($ticket['ticket_number']) ?></strong>
                        </td>
                        <td>
                            <?php if ($ticket['table_number']): ?>
                                <span class="badge bg-info">Mesa <?= $ticket['table_number'] ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ticket['waiter_name']): ?>
                                <?= htmlspecialchars($ticket['waiter_name']) ?>
                                <small class="text-muted">(<?= htmlspecialchars($ticket['employee_code']) ?>)</small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($ticket['cashier_name']) ?></td>
                        <td>
                            <strong class="text-danger">$<?= number_format($ticket['total'], 2) ?></strong>
                        </td>
                        <td>
                            <small>
                                <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
                            </small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success" 
                                        onclick="showPaymentModal(<?= $ticket['id'] ?>, '<?= htmlspecialchars($ticket['ticket_number']) ?>', <?= $ticket['total'] ?>)">
                                    <i class="bi bi-check-circle"></i> Marcar como Pagado
                                </button>
                                <a href="<?= BASE_URL ?>/tickets/show/<?= $ticket['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver Detalles
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Total de cuentas pendientes:</strong> <?= count($pending_tickets) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-warning">
                        <strong>Monto total pendiente:</strong> 
                        $<?= number_format(array_sum(array_column($pending_tickets, 'total')), 2) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Payment Method Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marcar como Pagado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ticket:</label>
                        <div id="ticketInfo" class="fw-bold"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Método de Pago *</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="intercambio">Intercambio</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Al marcar como pagado, esta cuenta se sumará a los ingresos del día.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Confirmar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showPaymentModal(ticketId, ticketNumber, total) {
    document.getElementById('ticketInfo').innerHTML = 
        ticketNumber + ' - <span class="text-success">$' + total.toFixed(2) + '</span>';
    document.getElementById('paymentForm').action = 
        '<?= BASE_URL ?>/financial/updatePaymentStatus/' + ticketId;
    
    var modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}
</script>