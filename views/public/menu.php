<?php $title = 'Menú Público'; ?>

<div class="row">
    <div class="col-12">
        <h1 class="text-center mb-4">
            <i class="bi bi-cup-hot"></i> Nuestro Menú
        </h1>
        <p class="text-center lead mb-5">Haga su pedido para recoger en nuestro restaurante o para mesa</p>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/public/order" id="publicOrderForm">
    <div class="row">
        <!-- Order Details -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Información del Pedido
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Nombre Completo *</label>
                        <input type="text" 
                               class="form-control <?= isset($errors['customer_name']) ? 'is-invalid' : '' ?>" 
                               id="customer_name" 
                               name="customer_name" 
                               value="<?= htmlspecialchars($old['customer_name'] ?? '') ?>"
                               required>
                        <?php if (isset($errors['customer_name'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['customer_name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="customer_phone" class="form-label">Teléfono *</label>
                        <input type="tel" 
                               class="form-control <?= isset($errors['customer_phone']) ? 'is-invalid' : '' ?>" 
                               id="customer_phone" 
                               name="customer_phone" 
                               value="<?= htmlspecialchars($old['customer_phone'] ?? '') ?>"
                               required>
                        <?php if (isset($errors['customer_phone'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['customer_phone']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
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
                                    (Capacidad: <?= $table['capacity'] ?> personas)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['table_id'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['table_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_pickup" 
                                   name="is_pickup"
                                   <?= isset($old['is_pickup']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_pickup">
                                <strong>Pedido para llevar (Pickup)</strong>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="pickup_datetime_container" style="display: none;">
                        <label for="pickup_datetime" class="form-label">Fecha y Hora de Pickup</label>
                        <input type="datetime-local" 
                               class="form-control <?= isset($errors['pickup_datetime']) ? 'is-invalid' : '' ?>" 
                               id="pickup_datetime" 
                               name="pickup_datetime"
                               value="<?= htmlspecialchars($old['pickup_datetime'] ?? '') ?>">
                        <?php if (isset($errors['pickup_datetime'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['pickup_datetime']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Seleccione cuándo desea recoger su pedido (hasta 30 días en adelante)
                            <br><small id="timezone-info" class="text-muted"></small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="customer_birthday" class="form-label">Fecha de Cumpleaños (Opcional)</label>
                        <input type="date" 
                               class="form-control <?= isset($errors['customer_birthday']) ? 'is-invalid' : '' ?>" 
                               id="customer_birthday" 
                               name="customer_birthday" 
                               value="<?= htmlspecialchars($old['customer_birthday'] ?? '') ?>">
                        <?php if (isset($errors['customer_birthday'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['customer_birthday']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Ayúdanos a celebrar contigo y obtener ofertas especiales
                        </div>
                    </div>
                    
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
                            <h3 class="text-success mb-0" id="orderTotal">$0.00</h3>
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
                            <div class="card dish-card h-100" data-dish-id="<?= $dish['id'] ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0"><?= htmlspecialchars($dish['name']) ?></h6>
                                        <span class="badge bg-success fs-6">$<?= number_format($dish['price'], 2) ?></span>
                                    </div>
                                    <?php if ($dish['description']): ?>
                                        <p class="text-muted small mb-3"><?= htmlspecialchars($dish['description']) ?></p>
                                    <?php endif; ?>
                                    <div class="input-group input-group-sm">
                                        <button type="button" class="btn btn-outline-secondary btn-minus" data-dish-id="<?= $dish['id'] ?>">-</button>
                                        <input type="hidden" 
                                               name="items[<?= $dish['id'] ?>][dish_id]" 
                                               value="<?= $dish['id'] ?>">
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
    
    <div class="row mt-4 mb-5">
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-check-circle"></i> Realizar Pedido
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('publicOrderForm');
    const totalElement = document.getElementById('orderTotal');
    const pickupCheckbox = document.getElementById('is_pickup');
    const pickupContainer = document.getElementById('pickup_datetime_container');
    const pickupDatetime = document.getElementById('pickup_datetime');
    
    // Handle pickup checkbox
    pickupCheckbox.addEventListener('change', function() {
        if (this.checked) {
            pickupContainer.style.display = 'block';
            pickupDatetime.required = true;
            // Set minimum datetime to now + 30 minutes (in user's local timezone)
            const now = new Date();
            now.setMinutes(now.getMinutes() + 30);
            pickupDatetime.min = now.toISOString().slice(0, 16);
            
            // Set maximum datetime to 30 days from now
            const maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 30);
            pickupDatetime.max = maxDate.toISOString().slice(0, 16);
            
            // Show timezone info to user
            updateTimezoneInfo();
        } else {
            pickupContainer.style.display = 'none';
            pickupDatetime.required = false;
        }
    });
    
    // Trigger on load if already checked
    if (pickupCheckbox.checked) {
        pickupCheckbox.dispatchEvent(new Event('change'));
    }
    
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
                dishCard.classList.add('border-success');
            } else {
                notesInput.style.display = 'none';
                dishCard.classList.remove('border-success');
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
                dishCard.classList.add('border-success');
            } else {
                notesInput.style.display = 'none';
                dishCard.classList.remove('border-success');
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
    
    // Function to update timezone information
    function updateTimezoneInfo() {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const now = new Date();
        const timeString = now.toLocaleString('es-MX', {
            timeZone: timezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            timeZoneName: 'short'
        });
        
        const timezoneInfo = document.getElementById('timezone-info');
        if (timezoneInfo) {
            timezoneInfo.textContent = `Su zona horaria: ${timezone} | Hora actual: ${timeString}`;
        }
    }
    
    // Add additional validation for pickup datetime
    pickupDatetime.addEventListener('change', function() {
        if (this.value) {
            const selectedTime = new Date(this.value);
            const now = new Date();
            const minTime = new Date(now.getTime() + 30 * 60000); // 30 minutes from now
            const maxTime = new Date(now.getTime() + 30 * 24 * 60 * 60000); // 30 days from now
            
            if (selectedTime < minTime) {
                alert('La hora de pickup debe ser al menos 30 minutos en adelante.');
                this.value = '';
                return;
            }
            
            if (selectedTime > maxTime) {
                alert('La hora de pickup no puede ser más de 30 días en adelante.');
                this.value = '';
                return;
            }
        }
    });
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

.dish-card.border-success {
    border-color: #198754 !important;
    background-color: #f8fff8;
}

.btn-minus, .btn-plus {
    width: 35px;
}

.sticky-top {
    z-index: 1020;
}
</style>