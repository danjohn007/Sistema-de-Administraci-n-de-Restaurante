<?php $title = 'Nuevo Pedido'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-circle"></i> Nuevo Pedido</h1>
    <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Pedidos
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" id="orderForm">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Información del Pedido
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="table_id" class="form-label">Mesa <span class="text-danger">*</span></label>
                        <select name="table_id" id="table_id" class="form-select" required>
                            <option value="">Seleccionar mesa...</option>
                            <?php foreach ($tables as $table): ?>
                                <option value="<?= $table['id'] ?>" 
                                    <?= isset($old['table_id']) && $old['table_id'] == $table['id'] ? 'selected' : '' ?>>
                                    Mesa <?= $table['number'] ?> (<?= $table['capacity'] ?> personas)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="waiter_id" class="form-label">Mesero <span class="text-danger">*</span></label>
                        <select name="waiter_id" id="waiter_id" class="form-select" required>
                            <option value="">Seleccionar mesero...</option>
                            <?php foreach ($waiters as $waiter): ?>
                                <option value="<?= $waiter['id'] ?>" 
                                    <?= isset($old['waiter_id']) && $old['waiter_id'] == $waiter['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($waiter['name']) ?> (<?= htmlspecialchars($waiter['employee_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas del pedido</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                            placeholder="Instrucciones especiales..."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-ul"></i> Platillos del Pedido
                    </h5>
                </div>
                <div class="card-body">
                    <div id="orderItems">
                        <!-- Order items will be added here dynamically -->
                    </div>
                    
                    <div class="mb-3">
                        <label for="dish_select" class="form-label">Agregar platillo</label>
                        <select id="dish_select" class="form-select">
                            <option value="">Seleccionar platillo...</option>
                            <?php 
                            $currentCategory = '';
                            foreach ($dishes as $dish): 
                                if ($dish['category'] !== $currentCategory):
                                    if ($currentCategory !== ''): ?>
                                        </optgroup>
                                    <?php endif; ?>
                                    <optgroup label="<?= htmlspecialchars($dish['category'] ?: 'Sin categoría') ?>">
                                    <?php $currentCategory = $dish['category']; ?>
                                <?php endif; ?>
                                <option value="<?= $dish['id'] ?>" data-price="<?= $dish['price'] ?>">
                                    <?= htmlspecialchars($dish['name']) ?> - $<?= number_format($dish['price'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($currentCategory !== ''): ?>
                                </optgroup>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <button type="button" id="addDishBtn" class="btn btn-outline-primary">
                        <i class="bi bi-plus"></i> Agregar Platillo
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Total del pedido: $<span id="orderTotal">0.00</span></strong>
                        </div>
                        <div>
                            <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Crear Pedido
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 0;
    
    document.getElementById('addDishBtn').addEventListener('click', function() {
        const dishSelect = document.getElementById('dish_select');
        const selectedOption = dishSelect.options[dishSelect.selectedIndex];
        
        if (!selectedOption.value) {
            alert('Por favor selecciona un platillo');
            return;
        }
        
        const dishId = selectedOption.value;
        const dishName = selectedOption.text;
        const dishPrice = parseFloat(selectedOption.dataset.price);
        
        addOrderItem(dishId, dishName, dishPrice);
        dishSelect.value = '';
    });
    
    function addOrderItem(dishId, dishName, dishPrice) {
        const orderItems = document.getElementById('orderItems');
        const itemDiv = document.createElement('div');
        itemDiv.className = 'mb-3 p-3 border rounded order-item';
        itemDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${dishName}</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" name="items[${itemCount}][quantity]" 
                                class="form-control quantity-input" min="1" value="1" 
                                data-price="${dishPrice}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Notas</label>
                            <input type="text" name="items[${itemCount}][notes]" 
                                class="form-control" placeholder="Instrucciones especiales...">
                        </div>
                    </div>
                    <input type="hidden" name="items[${itemCount}][dish_id]" value="${dishId}">
                    <div class="mt-2">
                        <small class="text-muted">
                            Precio unitario: $${dishPrice.toFixed(2)} | 
                            Subtotal: $<span class="item-subtotal">${dishPrice.toFixed(2)}</span>
                        </small>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        
        orderItems.appendChild(itemDiv);
        itemCount++;
        
        // Add event listeners
        const quantityInput = itemDiv.querySelector('.quantity-input');
        quantityInput.addEventListener('input', updateItemSubtotal);
        
        const removeBtn = itemDiv.querySelector('.remove-item-btn');
        removeBtn.addEventListener('click', function() {
            itemDiv.remove();
            updateOrderTotal();
        });
        
        updateOrderTotal();
    }
    
    function updateItemSubtotal(e) {
        const input = e.target;
        const quantity = parseInt(input.value) || 0;
        const price = parseFloat(input.dataset.price);
        const subtotal = quantity * price;
        
        const subtotalSpan = input.closest('.order-item').querySelector('.item-subtotal');
        subtotalSpan.textContent = subtotal.toFixed(2);
        
        updateOrderTotal();
    }
    
    function updateOrderTotal() {
        let total = 0;
        document.querySelectorAll('.item-subtotal').forEach(function(span) {
            total += parseFloat(span.textContent);
        });
        
        document.getElementById('orderTotal').textContent = total.toFixed(2);
    }
});
</script>