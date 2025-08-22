<?php $title = 'Editar Pedido #' . $order['id']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> Editar Pedido #<?= $order['id'] ?></h1>
    <div>
        <a href="<?= BASE_URL ?>/orders/show/<?= $order['id'] ?>" class="btn btn-outline-info">
            <i class="bi bi-eye"></i> Ver Detalles
        </a>
        <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Pedidos
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/orders/edit/<?= $order['id'] ?>">
    <div class="row">
        <!-- Order Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Información del Pedido
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mesa:</label>
                        <span class="badge bg-info fs-6">Mesa <?= $order['table_id'] ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado:</label>
                        <span class="badge status-<?= $order['status'] ?> fs-6">
                            <?= getOrderStatusText($order['status']) ?>
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas del Pedido</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4" 
                                  placeholder="Instrucciones especiales..."><?= htmlspecialchars($old['notes'] ?? $order['notes']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de Creación:</label>
                        <div><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Current Items -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list"></i> Items Actuales del Pedido
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($items)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-exclamation-circle display-4 text-muted"></i>
                            <p class="text-muted mt-3">No hay items en este pedido</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Platillo</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                        <th>Notas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $grandTotal = 0;
                                    foreach ($items as $item): 
                                        $grandTotal += $item['subtotal'];
                                    ?>
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
                                            <strong>$<?= number_format($item['subtotal'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['notes'])): ?>
                                                <small><?= htmlspecialchars($item['notes']) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="removeItem(<?= $item['id'] ?>)"
                                                    title="Eliminar item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <th colspan="3" class="text-end">Total Actual:</th>
                                        <th>$<?= number_format($grandTotal, 2) ?></th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Add New Items -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-plus-circle"></i> Agregar Nuevos Items
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row" id="addItemsSection">
                        <?php 
                        $currentCategory = '';
                        foreach ($dishes as $dish): 
                            if ($dish['category'] !== $currentCategory):
                                if ($currentCategory !== ''): ?>
                                    </div>
                                <?php endif; ?>
                                <h6 class="text-muted mb-3 mt-4"><?= htmlspecialchars($dish['category']) ?></h6>
                                <div class="row">
                            <?php 
                                $currentCategory = $dish['category'];
                            endif; 
                        ?>
                            <div class="col-md-6 mb-3">
                                <div class="card dish-card h-100" data-dish-id="<?= $dish['id'] ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0"><?= htmlspecialchars($dish['name']) ?></h6>
                                            <span class="badge bg-success">$<?= number_format($dish['price'], 2) ?></span>
                                        </div>
                                        <?php if ($dish['description']): ?>
                                            <p class="text-muted small mb-2"><?= htmlspecialchars($dish['description']) ?></p>
                                        <?php endif; ?>
                                        <div class="input-group input-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-minus" data-dish-id="<?= $dish['id'] ?>">-</button>
                                            <input type="number" 
                                                   class="form-control text-center dish-quantity" 
                                                   value="0" 
                                                   min="0" 
                                                   max="99"
                                                   data-price="<?= $dish['price'] ?>"
                                                   data-dish-id="<?= $dish['id'] ?>"
                                                   data-dish-name="<?= htmlspecialchars($dish['name']) ?>">
                                            <button type="button" class="btn btn-outline-secondary btn-plus" data-dish-id="<?= $dish['id'] ?>">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($currentCategory !== ''): ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-3" id="newItemsPreview" style="display: none;">
                        <h6>Items a Agregar:</h6>
                        <div id="newItemsList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/orders/show/<?= $order['id'] ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newItemsPreview = document.getElementById('newItemsPreview');
    const newItemsList = document.getElementById('newItemsList');
    
    // Handle quantity buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-plus') || e.target.classList.contains('btn-minus')) {
            const dishId = e.target.dataset.dishId;
            const quantityInput = document.querySelector(`input[data-dish-id="${dishId}"]`);
            const dishCard = document.querySelector(`[data-dish-id="${dishId}"]`);
            
            let currentQuantity = parseInt(quantityInput.value) || 0;
            
            if (e.target.classList.contains('btn-plus')) {
                currentQuantity++;
            } else if (e.target.classList.contains('btn-minus') && currentQuantity > 0) {
                currentQuantity--;
            }
            
            quantityInput.value = currentQuantity;
            
            // Update visual feedback
            if (currentQuantity > 0) {
                dishCard.classList.add('border-primary');
            } else {
                dishCard.classList.remove('border-primary');
            }
            
            updateNewItemsPreview();
        }
    });
    
    // Handle quantity input changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('dish-quantity')) {
            const dishId = e.target.dataset.dishId;
            const dishCard = document.querySelector(`[data-dish-id="${dishId}"]`);
            const quantity = parseInt(e.target.value) || 0;
            
            if (quantity > 0) {
                dishCard.classList.add('border-primary');
            } else {
                dishCard.classList.remove('border-primary');
            }
            
            updateNewItemsPreview();
        }
    });
    
    function updateNewItemsPreview() {
        const selectedItems = [];
        document.querySelectorAll('.dish-quantity').forEach(function(input) {
            const quantity = parseInt(input.value) || 0;
            if (quantity > 0) {
                selectedItems.push({
                    id: input.dataset.dishId,
                    name: input.dataset.dishName,
                    quantity: quantity,
                    price: parseFloat(input.dataset.price)
                });
            }
        });
        
        if (selectedItems.length > 0) {
            let html = '<div class="list-group">';
            let total = 0;
            
            selectedItems.forEach(function(item) {
                const subtotal = item.quantity * item.price;
                total += subtotal;
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${item.name}</strong>
                            <br><small class="text-muted">Cantidad: ${item.quantity} x $${item.price.toFixed(2)}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">$${subtotal.toFixed(2)}</span>
                        <input type="hidden" name="new_items[${item.id}][dish_id]" value="${item.id}">
                        <input type="hidden" name="new_items[${item.id}][quantity]" value="${item.quantity}">
                    </div>
                `;
            });
            
            html += `
                <div class="list-group-item list-group-item-success d-flex justify-content-between align-items-center">
                    <strong>Total a Agregar:</strong>
                    <strong>$${total.toFixed(2)}</strong>
                </div>
            </div>`;
            
            newItemsList.innerHTML = html;
            newItemsPreview.style.display = 'block';
        } else {
            newItemsPreview.style.display = 'none';
        }
    }
});

function removeItem(itemId) {
    if (confirm('¿Está seguro de eliminar este item del pedido?')) {
        // This would typically make an AJAX call to remove the item
        // For now, we'll just redirect to a removal URL
        window.location.href = '<?= BASE_URL ?>/orders/removeItem/' + itemId;
    }
}
</script>

<style>
.status-pendiente { background-color: #ffc107; color: #000; }
.status-en_preparacion { background-color: #fd7e14; color: #fff; }
.status-listo { background-color: #198754; color: #fff; }
.status-entregado { background-color: #0dcaf0; color: #000; }

.dish-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.dish-card:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.dish-card.border-primary {
    border-color: #0d6efd !important;
    background-color: #f8f9ff;
}

.btn-minus, .btn-plus {
    width: 35px;
}
</style>

<?php
function getOrderStatusText($status) {
    $statusTexts = [
        ORDER_PENDING => 'Pendiente',
        ORDER_PREPARING => 'En Preparación',
        ORDER_READY => 'Listo',
        ORDER_DELIVERED => 'Entregado'
    ];
    
    return $statusTexts[$status] ?? $status;
}
?>