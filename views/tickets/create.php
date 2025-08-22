<?php $title = 'Generar Ticket'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-receipt-cutoff"></i> Generar Ticket</h1>
    <a href="<?= BASE_URL ?>/tickets" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Tickets
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (empty($orders)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
            <h3 class="mt-3">No hay pedidos listos</h3>
            <p class="text-muted">
                No hay pedidos en estado "Listo" disponibles para generar tickets.<br>
                Los pedidos deben estar marcados como "Listo" antes de poder generar un ticket.
            </p>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-primary">
                    <i class="bi bi-clipboard-check"></i> Ver Pedidos
                </a>
                <a href="<?= BASE_URL ?>/tickets" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-left"></i> Volver a Tickets
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<form method="POST" action="<?= BASE_URL ?>/tickets/create">
    <div class="row">
        <!-- Order Selection -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-check"></i> Seleccionar Pedido
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
                                    <th width="50">Seleccionar</th>
                                    <th>Pedido #</th>
                                    <th>Mesa</th>
                                    <th>Mesero</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr class="order-row" data-order-id="<?= $order['id'] ?>">
                                    <td>
                                        <input type="radio" 
                                               class="form-check-input" 
                                               name="order_id" 
                                               value="<?= $order['id'] ?>"
                                               <?= (($old['order_id'] ?? '') == $order['id']) ? 'checked' : '' ?>
                                               required>
                                    </td>
                                    <td>
                                        <strong>#<?= $order['id'] ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Mesa <?= $order['table_number'] ?></span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($order['waiter_name']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($order['employee_code']) ?></small>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($order['total'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/orders/show/<?= $order['id'] ?>" 
                                           class="btn btn-outline-primary btn-sm" 
                                           target="_blank"
                                           title="Ver detalles del pedido">
                                            <i class="bi bi-eye"></i>
                                        </a>
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
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Información:</strong><br>
                        Se aplicará un 16% de IVA al subtotal del pedido.
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
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Generar Ticket
                </button>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderRows = document.querySelectorAll('.order-row');
    const ticketPreview = document.getElementById('ticketPreview');
    const previewContent = document.getElementById('previewContent');
    
    // Handle order selection
    document.addEventListener('change', function(e) {
        if (e.target.name === 'order_id') {
            const orderId = e.target.value;
            const orderRow = document.querySelector(`[data-order-id="${orderId}"]`);
            
            if (orderRow) {
                const tableNumber = orderRow.querySelector('.badge').textContent;
                const total = orderRow.querySelector('strong:last-of-type').textContent;
                const totalAmount = parseFloat(total.replace('$', '').replace(',', ''));
                
                const subtotal = totalAmount;
                const tax = subtotal * 0.16;
                const finalTotal = subtotal + tax;
                
                const previewHtml = `
                    <div class="row mb-2">
                        <div class="col-6">Mesa:</div>
                        <div class="col-6 text-end">${tableNumber}</div>
                    </div>
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
                        <div class="col-6 text-end"><strong>$${finalTotal.toFixed(2)}</strong></div>
                    </div>
                `;
                
                previewContent.innerHTML = previewHtml;
                ticketPreview.style.display = 'block';
            }
            
            // Highlight selected row
            orderRows.forEach(row => row.classList.remove('table-primary'));
            orderRow.classList.add('table-primary');
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
.order-row {
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.order-row:hover {
    background-color: #f8f9fa;
}

.table-primary {
    background-color: #b3d4fc !important;
}
</style>