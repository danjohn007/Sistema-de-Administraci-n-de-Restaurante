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

<form method="POST" action="<?= BASE_URL ?>/orders/create" id="orderForm">
    <div class="row">
        <!-- Order Details -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Detalles del Pedido
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="table_id" class="form-label">Mesa *</label>
                        <select class="form-select <?= isset($errors['table_id']) ? 'is-invalid' : '' ?>" 
                                id="table_id" 
                                name="table_id" 
                                required>
                            <option value="">Seleccionar mesa...</option>
                            <?php foreach ($tables as $table): ?>
                                <option value="<?= $table['id'] ?>" 
                                        <?= (($old['table_id'] ?? '') == $table['id']) ? 'selected' : '' ?>>
                                    Mesa <?= $table['number'] ?> 
                                    (Cap: <?= $table['capacity'] ?>) 
                                    - <?= ucfirst($table['status']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['table_id'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['table_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($user['role'] === ROLE_ADMIN || $user['role'] === ROLE_CASHIER): ?>
                    <div class="mb-3">
                        <label for="waiter_id" class="form-label">Mesero *</label>
                        <select class="form-select <?= isset($errors['waiter_id']) ? 'is-invalid' : '' ?>" 
                                id="waiter_id" 
                                name="waiter_id" 
                                required>
                            <option value="">Seleccionar mesero...</option>
                            <?php foreach ($waiters as $waiter): ?>
                                <option value="<?= $waiter['id'] ?>" 
                                        <?= (($old['waiter_id'] ?? '') == $waiter['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($waiter['name']) ?> 
                                    (<?= htmlspecialchars($waiter['employee_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['waiter_id'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['waiter_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas del Pedido</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Instrucciones especiales..."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Total del Pedido</h6>
                            <h3 class="text-primary mb-0" id="orderTotal">$0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Menu Items -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cup-hot"></i> Seleccionar Platillos
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($errors['items'])): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($errors['items']) ?>
                        </div>
                    <?php endif; ?>
                    
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
                            <div class="card dish-card" data-dish-id="<?= $dish['id'] ?>">
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
                                               name="items[<?= $dish['id'] ?>][dish_id]" 
                                               value="<?= $dish['id'] ?>"
                                               style="display: none;">
                                        <input type="number" 
                                               class="form-control text-center dish-quantity" 
                                               name="items[<?= $dish['id'] ?>][quantity]" 
                                               value="0" 
                                               min="0" 
                                               max="99"
                                               data-price="<?= $dish['price'] ?>"
                                               data-dish-id="<?= $dish['id'] ?>">
                                        <button type="button" class="btn btn-outline-secondary btn-plus" data-dish-id="<?= $dish['id'] ?>">+</button>
                                    </div>
                                    <input type="text" 
                                           class="form-control form-control-sm mt-2" 
                                           name="items[<?= $dish['id'] ?>][notes]" 
                                           placeholder="Notas especiales..."
                                           style="display: none;">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($currentCategory !== ''): ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/orders" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Crear Pedido
                </button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('orderForm');
    const totalElement = document.getElementById('orderTotal');
    
    // Handle quantity buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-plus') || e.target.classList.contains('btn-minus')) {
            const dishId = e.target.dataset.dishId;
            const quantityInput = document.querySelector(`input[name="items[${dishId}][quantity]"]`);
            const notesInput = document.querySelector(`input[name="items[${dishId}][notes]"]`);
            const dishCard = document.querySelector(`[data-dish-id="${dishId}"]`);
            
            let currentQuantity = parseInt(quantityInput.value) || 0;
            
            if (e.target.classList.contains('btn-plus')) {
                currentQuantity++;
            } else if (e.target.classList.contains('btn-minus') && currentQuantity > 0) {
                currentQuantity--;
            }
            
            quantityInput.value = currentQuantity;
            
            // Show/hide notes input
            if (currentQuantity > 0) {
                notesInput.style.display = 'block';
                dishCard.classList.add('border-primary');
            } else {
                notesInput.style.display = 'none';
                dishCard.classList.remove('border-primary');
                notesInput.value = '';
            }
            
            updateTotal();
        }
    });
    
    // Handle quantity input changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('dish-quantity')) {
            const dishId = e.target.dataset.dishId;
            const notesInput = document.querySelector(`input[name="items[${dishId}][notes]"]`);
            const dishCard = document.querySelector(`[data-dish-id="${dishId}"]`);
            const quantity = parseInt(e.target.value) || 0;
            
            if (quantity > 0) {
                notesInput.style.display = 'block';
                dishCard.classList.add('border-primary');
            } else {
                notesInput.style.display = 'none';
                dishCard.classList.remove('border-primary');
                notesInput.value = '';
            }
            
            updateTotal();
        }
    });
    
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.dish-quantity[name*="[quantity]"]').forEach(function(input) {
            const quantity = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            total += quantity * price;
        });
        
        totalElement.textContent = '$' + total.toFixed(2);
    }
});
</script>

<style>
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