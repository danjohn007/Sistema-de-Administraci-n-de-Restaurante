<?php $title = 'Generar Ticket de Pedidos Vencidos'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-receipt-cutoff text-warning"></i> Generar Ticket de Pedidos Vencidos</h1>
    <div>
        <a href="<?= BASE_URL ?>/tickets" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Tickets
        </a>
        <a href="<?= BASE_URL ?>/orders/expiredOrders" class="btn btn-outline-warning">
            <i class="bi bi-exclamation-triangle"></i> Ver Todos los Vencidos
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (isset($errors['selection'])): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($errors['selection']) ?>
    </div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <strong>Pedidos Vencidos:</strong> Los tickets generados desde pedidos vencidos se registrarán con la fecha de hoy para mantener la consistencia en los reportes financieros.
</div>

<?php if (empty($orders)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-check-circle display-1 text-success"></i>
            <h3 class="mt-3">¡Excelente! No hay pedidos vencidos listos</h3>
            <p class="text-muted">
                No hay pedidos vencidos en estado "Listo" disponibles para generar tickets.<br>
                Los pedidos vencidos deben estar marcados como "Listo" antes de poder generar un ticket.
            </p>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/orders/expiredOrders" class="btn btn-outline-warning">
                    <i class="bi bi-exclamation-triangle"></i> Ver Pedidos Vencidos
                </a>
                <a href="<?= BASE_URL ?>/tickets" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-left"></i> Volver a Tickets
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-warning">
            <div class="card-body">
                <i class="bi bi-exclamation-triangle display-6 text-warning"></i>
                <h6 class="mt-2">Pedidos Listos</h6>
                <h4 class="text-warning">
                    <?= count($orders) ?>
                </h4>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-danger">
            <div class="card-body">
                <i class="bi bi-currency-dollar display-6 text-danger"></i>
                <h6 class="mt-2">Monto Total</h6>
                <h4 class="text-danger">
                    $<?php 
                    $totalAmount = 0;
                    foreach ($orders as $order) {
                        $totalAmount += (float)$order['total'];
                    }
                    echo number_format($totalAmount, 2);
                    ?>
                </h4>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-info">
            <div class="card-body">
                <i class="bi bi-calendar-date display-6 text-info"></i>
                <h6 class="mt-2">Más Antiguo</h6>
                <h4 class="text-info">
                    <?php 
                    if (!empty($orders)) {
                        $oldest = end($orders);
                        echo $oldest['hours_since_created'] . 'h';
                    } else {
                        echo '0h';
                    }
                    ?>
                </h4>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-secondary">
            <div class="card-body">
                <i class="bi bi-grid-3x3-gap display-6 text-secondary"></i>
                <h6 class="mt-2">Mesas Afectadas</h6>
                <h4 class="text-secondary">
                    <?php 
                    $tables = array_unique(array_column($orders, 'table_number'));
                    echo count(array_filter($tables));
                    ?>
                </h4>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="<?= BASE_URL ?>/tickets/createExpiredTicket">
    <div class="row">
        <!-- Order Selection -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-check"></i> Seleccionar Pedido Vencido
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($errors['order_id'])): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($errors['order_id']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Mesa</th>
                                    <th>Mesero</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Creado</th>
                                    <th>Tiempo Vencido</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr class="table-warning">
                                    <td>
                                        <div class="form-check">
                                            <input type="radio" 
                                                   class="form-check-input" 
                                                   name="order_id" 
                                                   value="<?= $order['id'] ?>"
                                                   id="order_<?= $order['id'] ?>"
                                                   <?= (($old['order_id'] ?? '') == $order['id']) ? 'checked' : '' ?>
                                                   required>
                                            <label class="form-check-label" for="order_<?= $order['id'] ?>"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>#<?= $order['id'] ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($order['table_number']): ?>
                                            <span class="badge bg-secondary">Mesa <?= $order['table_number'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Pickup</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($order['waiter_name']): ?>
                                            <small>
                                                <?= htmlspecialchars($order['waiter_name']) ?><br>
                                                <span class="text-muted"><?= htmlspecialchars($order['employee_code']) ?></span>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Sin asignar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= $order['items_count'] ?> items</span>
                                    </td>
                                    <td>
                                        <strong class="text-danger">$<?= number_format($order['total'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <small>
                                            <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <?= $order['hours_since_created'] ?>h vencido
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ticket Details -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-receipt"></i> Detalles del Ticket
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Método de Pago *</label>
                        <select class="form-select <?= isset($errors['payment_method']) ? 'is-invalid' : '' ?>" 
                                id="payment_method" 
                                name="payment_method" 
                                required>
                            <option value="">Seleccionar método...</option>
                            <option value="efectivo" <?= (($old['payment_method'] ?? '') === 'efectivo') ? 'selected' : '' ?>>
                                <i class="bi bi-cash"></i> Efectivo
                            </option>
                            <option value="tarjeta" <?= (($old['payment_method'] ?? '') === 'tarjeta') ? 'selected' : '' ?>>
                                <i class="bi bi-credit-card"></i> Tarjeta
                            </option>
                            <option value="transferencia" <?= (($old['payment_method'] ?? '') === 'transferencia') ? 'selected' : '' ?>>
                                <i class="bi bi-bank"></i> Transferencia
                            </option>
                            <option value="intercambio" <?= (($old['payment_method'] ?? '') === 'intercambio') ? 'selected' : '' ?>>
                                <i class="bi bi-arrow-left-right"></i> Intercambio
                            </option>
                            <option value="pendiente_por_cobrar" <?= (($old['payment_method'] ?? '') === 'pendiente_por_cobrar') ? 'selected' : '' ?>>
                                <i class="bi bi-clock-history"></i> Pendiente por Cobrar
                            </option>
                        </select>
                        <?php if (isset($errors['payment_method'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['payment_method']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card bg-light mb-3" id="ticketPreview" style="display: none;">
                        <div class="card-body">
                            <h6 class="card-title">Preview del Ticket</h6>
                            <div id="previewContent">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i>
                        <strong>Importante:</strong><br>
                        Este ticket se registrará con la fecha de hoy para mantener la consistencia en los reportes financieros. Los precios ya incluyen 16% de IVA que se mostrará desglosado.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/tickets" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-check-circle"></i> Generar Ticket Vencido
                </button>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ticketPreview = document.getElementById('ticketPreview');
    const previewContent = document.getElementById('previewContent');
    
    // Handle order selection
    document.addEventListener('change', function(e) {
        if (e.target.name === 'order_id') {
            const orderId = e.target.value;
            const orderData = <?= json_encode($orders) ?>;
            const selectedOrder = orderData.find(o => o.id == orderId);
            
            if (selectedOrder) {
                const totalWithTax = parseFloat(selectedOrder.total);
                const subtotal = totalWithTax / 1.16;
                const tax = totalWithTax - subtotal;
                const total = totalWithTax;
                
                const previewHtml = `
                    <div class="row mb-2">
                        <div class="col-6"><strong>Pedido:</strong></div>
                        <div class="col-6 text-end">#${selectedOrder.id}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Mesa:</strong></div>
                        <div class="col-6 text-end">${selectedOrder.table_number ? 'Mesa ' + selectedOrder.table_number : 'Pickup'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Items:</strong></div>
                        <div class="col-6 text-end">${selectedOrder.items_count}</div>
                    </div>
                    <hr>
                    <div class="row mb-2">
                        <div class="col-6">Subtotal:</div>
                        <div class="col-6 text-end">$${subtotal.toFixed(2)}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">IVA (16%):</div>
                        <div class="col-6 text-end">$${tax.toFixed(2)}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6"><strong>Total:</strong></div>
                        <div class="col-6 text-end"><strong>$${total.toFixed(2)}</strong></div>
                    </div>
                `;
                
                previewContent.innerHTML = previewHtml;
                ticketPreview.style.display = 'block';
            }
        }
    });
    
    // Initial setup if there's a pre-selected order
    const selectedOrder = document.querySelector('input[name="order_id"]:checked');
    if (selectedOrder) {
        selectedOrder.dispatchEvent(new Event('change'));
    }
});
</script>

<style>
.table-warning {
    background-color: rgba(255, 243, 205, 0.3) !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

tr.table-warning:hover {
    background-color: rgba(255, 243, 205, 0.5) !important;
}
</style>