<?php $title = 'Cuentas Pendientes por Cobrar'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-clock-history text-warning"></i> Cuentas Pendientes por Cobrar</h1>
    <div>
        <a href="<?= BASE_URL ?>/tickets" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Tickets
        </a>
        <a href="<?= BASE_URL ?>/tickets/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Generar Ticket
        </a>
    </div>
</div>

<!-- Search Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/tickets/pendingPayments" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar:</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       placeholder="Cliente, teléfono, email, mesa..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <?php if (!empty($_GET['search'])): ?>
                    <a href="<?= BASE_URL ?>/tickets/pendingPayments" class="btn btn-outline-secondary ms-1">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    <strong>Cuentas Pendientes:</strong> Estos son tickets generados con método de pago "Pendiente por Cobrar" que necesitan ser cobrados.
</div>

<?php if (empty($tickets)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-check-circle display-1 text-success"></i>
            <h3 class="mt-3 text-success">¡Excelente! No hay cuentas pendientes</h3>
            <p class="text-muted">
                No hay tickets pendientes por cobrar en el sistema.
            </p>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/tickets" class="btn btn-primary">
                    <i class="bi bi-receipt"></i> Ver Tickets
                </a>
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card text-center border-warning">
            <div class="card-body">
                <i class="bi bi-clock-history display-6 text-warning"></i>
                <h6 class="mt-2">Cuentas Pendientes</h6>
                <h4 class="text-warning">
                    <?= count($tickets) ?>
                </h4>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card text-center border-danger">
            <div class="card-body">
                <i class="bi bi-currency-dollar display-6 text-danger"></i>
                <h6 class="mt-2">Monto Total</h6>
                <h4 class="text-danger">
                    $<?php 
                    $totalAmount = 0;
                    foreach ($tickets as $ticket) {
                        $totalAmount += (float)$ticket['total'];
                    }
                    echo number_format($totalAmount, 2);
                    ?>
                </h4>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card text-center border-info">
            <div class="card-body">
                <i class="bi bi-calendar-date display-6 text-info"></i>
                <h6 class="mt-2">Más Antigua</h6>
                <h4 class="text-info">
                    <?php 
                    if (!empty($tickets)) {
                        $oldest = end($tickets);
                        $days = (time() - strtotime($oldest['created_at'])) / (60 * 60 * 24);
                        echo floor($days) . ' días';
                    } else {
                        echo '0 días';
                    }
                    ?>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Tickets List -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Cuentas Pendientes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Mesa</th>
                        <th>Cliente</th>
                        <th>Mesero</th>
                        <th>Cajero</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Días Pendiente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr class="table-warning">
                        <td>
                            <strong><?= htmlspecialchars($ticket['ticket_number']) ?></strong>
                        </td>
                        <td>
                            <?php if ($ticket['table_number']): ?>
                                <span class="badge bg-secondary">Mesa <?= $ticket['table_number'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-info">Pickup</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <i class="bi bi-person-fill text-info"></i> 
                            <small class="text-muted">Cliente:</small><br>
                            <?= htmlspecialchars($ticket['customer_name'] ?: $ticket['order_customer_name'] ?: 'Público') ?>
                            <?php if (!empty($ticket['customer_phone'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($ticket['customer_phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ticket['waiter_name']): ?>
                                <small>
                                    <?= htmlspecialchars($ticket['waiter_name']) ?><br>
                                    <span class="text-muted"><?= htmlspecialchars($ticket['employee_code']) ?></span>
                                </small>
                            <?php else: ?>
                                <span class="text-muted">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?= htmlspecialchars($ticket['cashier_name']) ?></small>
                        </td>
                        <td>
                            <strong class="text-danger">$<?= number_format($ticket['total'], 2) ?></strong>
                        </td>
                        <td>
                            <small>
                                <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
                            </small>
                        </td>
                        <td>
                            <?php 
                            $days = floor((time() - strtotime($ticket['created_at'])) / (60 * 60 * 24));
                            $badgeClass = $days > 7 ? 'bg-danger' : ($days > 3 ? 'bg-warning' : 'bg-info');
                            ?>
                            <span class="badge <?= $badgeClass ?>">
                                <?= $days ?> días
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/tickets/show/<?= $ticket['id'] ?>" 
                                   class="btn btn-outline-primary" title="Ver Ticket">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <button type="button" 
                                        class="btn btn-outline-success" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#markPaidModal"
                                        data-ticket-id="<?= $ticket['id'] ?>"
                                        data-ticket-number="<?= htmlspecialchars($ticket['ticket_number']) ?>"
                                        data-total="<?= number_format($ticket['total'], 2) ?>"
                                        title="Marcar como Cobrado">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marcar como Cobrado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="markPaidForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Ticket:</strong> <span id="modalTicketNumber"></span><br>
                        <strong>Total:</strong> $<span id="modalTotal"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Método de Pago Recibido *</label>
                        <select class="form-select" name="payment_method" required>
                            <option value="">Seleccionar método...</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="intercambio">Intercambio</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Marcar como Cobrado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.table-warning {
    background-color: rgba(255, 243, 205, 0.3) !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const markPaidModal = document.getElementById('markPaidModal');
    
    markPaidModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const ticketId = button.getAttribute('data-ticket-id');
        const ticketNumber = button.getAttribute('data-ticket-number');
        const total = button.getAttribute('data-total');
        
        document.getElementById('modalTicketNumber').textContent = ticketNumber;
        document.getElementById('modalTotal').textContent = total;
        
        const form = document.getElementById('markPaidForm');
        form.action = '<?= BASE_URL ?>/tickets/markAsPaid/' + ticketId;
    });
});
</script>