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
                    
                    <!-- Customer Search Section -->
                    <div class="mb-3">
                        <label class="form-label">Cliente *</label>
                        <div class="input-group mb-2">
                            <input type="text" 
                                   class="form-control <?= isset($errors['customer_id']) ? 'is-invalid' : '' ?>" 
                                   id="customer_search" 
                                   placeholder="Buscar por nombre o teléfono..."
                                   autocomplete="off"
                                   required>
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    id="clear_customer">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        
                        <!-- Customer Results -->
                        <div id="customer_results" class="list-group mb-2" style="display: none;"></div>
                        
                        <!-- Selected Customer Display -->
                        <div id="selected_customer" class="alert alert-info" style="display: none;">
                            <strong>Cliente seleccionado:</strong> 
                            <span id="customer_display"></span>
                            <input type="hidden" id="customer_id" name="customer_id" value="<?= htmlspecialchars($old['customer_id'] ?? '') ?>">
                        </div>
                        
                        <!-- New Customer Form -->
                        <div class="row" id="new_customer_form" style="display: none;">
                            <div class="col-md-6">
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       id="new_customer_name" 
                                       name="new_customer_name" 
                                       placeholder="Nombre completo *"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <input type="tel" 
                                       class="form-control form-control-sm" 
                                       id="new_customer_phone" 
                                       name="new_customer_phone" 
                                       placeholder="Teléfono *"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-text">
                            Debe seleccionar un cliente existente o crear uno nuevo
                        </div>
                        <?php if (isset($errors['customer_id'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['customer_id']) ?>
                            </div>
                        <?php endif; ?>
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
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-cup-hot"></i> Seleccionar Platillos
                        </h5>
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="dish_search" 
                                   placeholder="Buscar platillos..."
                                   autocomplete="off">
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    id="clear_dish_search">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
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
                <button type="submit" class="btn btn-primary" id="create-order-btn">
                    <i class="bi bi-check-circle"></i> Crear Pedido
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Fixed Create Order Button -->
<div class="fixed-bottom bg-white border-top p-3 shadow-lg" id="fixed-order-section" style="display: none;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0 me-3">Total del Pedido:</h5>
                    <h4 class="text-primary mb-0" id="fixedOrderTotal">$0.00</h4>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="submit" form="orderForm" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle"></i> CREAR PEDIDO
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('orderForm');
    const totalElement = document.getElementById('orderTotal');
    
    // Customer search functionality
    const customerSearch = document.getElementById('customer_search');
    const customerResults = document.getElementById('customer_results');
    const selectedCustomer = document.getElementById('selected_customer');
    const customerDisplay = document.getElementById('customer_display');
    const customerIdInput = document.getElementById('customer_id');
    const clearCustomerBtn = document.getElementById('clear_customer');
    const newCustomerForm = document.getElementById('new_customer_form');
    
    let searchTimeout;
    
    // Customer search input handler
    customerSearch.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length < 2) {
            customerResults.style.display = 'none';
            return;
        }
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchCustomers(query);
        }, 300);
    });
    
    // Clear customer selection
    clearCustomerBtn.addEventListener('click', function() {
        clearCustomerSelection();
    });
    
    // Search customers via AJAX
    function searchCustomers(query) {
        fetch('<?= BASE_URL ?>/orders/searchCustomers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ query: query })
        })
        .then(response => response.json())
        .then(data => {
            displayCustomerResults(data.customers || []);
        })
        .catch(error => {
            console.error('Error searching customers:', error);
            customerResults.innerHTML = '<div class="list-group-item text-danger">Error al buscar clientes</div>';
            customerResults.style.display = 'block';
        });
    }
    
    // Display customer search results
    function displayCustomerResults(customers) {
        customerResults.innerHTML = '';
        
        if (customers.length === 0) {
            customerResults.innerHTML = `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>No se encontraron clientes</span>
                        <button type="button" class="btn btn-sm btn-primary" onclick="showNewCustomerForm()">
                            <i class="bi bi-plus"></i> Crear Nuevo
                        </button>
                    </div>
                </div>
            `;
        } else {
            customers.forEach(customer => {
                const item = document.createElement('div');
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${customer.name}</strong><br>
                            <small class="text-muted">${customer.phone} | Visitas: ${customer.total_visits} | Gastado: $${parseFloat(customer.total_spent).toFixed(2)}</small>
                        </div>
                    </div>
                `;
                item.addEventListener('click', () => selectCustomer(customer));
                customerResults.appendChild(item);
            });
        }
        
        customerResults.style.display = 'block';
    }
    
    // Select a customer
    function selectCustomer(customer) {
        customerIdInput.value = customer.id;
        customerDisplay.textContent = `${customer.name} (${customer.phone})`;
        selectedCustomer.style.display = 'block';
        customerResults.style.display = 'none';
        newCustomerForm.style.display = 'none';
        customerSearch.value = customer.name;
    }
    
    // Clear customer selection
    function clearCustomerSelection() {
        customerIdInput.value = '';
        selectedCustomer.style.display = 'none';
        customerResults.style.display = 'none';
        newCustomerForm.style.display = 'none';
        customerSearch.value = '';
    }
    
    // Show new customer form
    window.showNewCustomerForm = function() {
        newCustomerForm.style.display = 'block';
        customerResults.style.display = 'none';
        document.getElementById('new_customer_name').value = customerSearch.value;
    }
    
    // Hide customer results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#customer_search') && !e.target.closest('#customer_results')) {
            customerResults.style.display = 'none';
        }
    });
    
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
        
        // Update fixed button total
        const fixedTotal = document.getElementById('fixedOrderTotal');
        if (fixedTotal) {
            fixedTotal.textContent = '$' + total.toFixed(2);
        }
        
        // Show/hide fixed button based on total
        const fixedSection = document.getElementById('fixed-order-section');
        if (total > 0) {
            fixedSection.style.display = 'block';
        } else {
            fixedSection.style.display = 'none';
        }
    }
    
    // Product search functionality
    const dishSearch = document.getElementById('dish_search');
    const clearDishSearch = document.getElementById('clear_dish_search');
    
    if (dishSearch) {
        dishSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const dishCards = document.querySelectorAll('.dish-card');
            
            dishCards.forEach(function(card) {
                const dishName = card.querySelector('.card-title').textContent.toLowerCase();
                const dishDescription = card.querySelector('.card-text') ? card.querySelector('.card-text').textContent.toLowerCase() : '';
                
                if (dishName.includes(query) || dishDescription.includes(query)) {
                    card.closest('.col-md-6').style.display = 'block';
                } else {
                    card.closest('.col-md-6').style.display = 'none';
                }
            });
            
            // Show/hide category headers
            const categories = document.querySelectorAll('h6.text-muted');
            categories.forEach(function(category) {
                const nextRow = category.nextElementSibling;
                if (nextRow && nextRow.classList.contains('row')) {
                    const visibleCards = nextRow.querySelectorAll('.col-md-6[style="display: block;"], .col-md-6:not([style*="display: none"])');
                    if (visibleCards.length > 0) {
                        category.style.display = 'block';
                    } else {
                        category.style.display = 'none';
                    }
                }
            });
        });
        
        clearDishSearch.addEventListener('click', function() {
            dishSearch.value = '';
            const dishCards = document.querySelectorAll('.dish-card');
            dishCards.forEach(function(card) {
                card.closest('.col-md-6').style.display = 'block';
            });
            
            const categories = document.querySelectorAll('h6.text-muted');
            categories.forEach(function(category) {
                category.style.display = 'block';
            });
        });
    }
    
    // Form validation for customer requirement (relaxed - customer is optional)
    form.addEventListener('submit', function(e) {
        const customerId = document.getElementById('customer_id').value;
        const newCustomerName = document.getElementById('new_customer_name').value;
        const newCustomerPhone = document.getElementById('new_customer_phone').value;
        
        // Only validate if user started entering new customer info but didn't complete it
        if (!customerId && newCustomerName && !newCustomerPhone) {
            e.preventDefault();
            alert('Si va a crear un cliente nuevo, debe proporcionar tanto el nombre como el teléfono.');
            return false;
        }
        
        if (!customerId && !newCustomerName && newCustomerPhone) {
            e.preventDefault();
            alert('Si va a crear un cliente nuevo, debe proporcionar tanto el nombre como el teléfono.');
            return false;
        }
        
        return true;
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

.dish-card.border-primary {
    border-color: #0d6efd !important;
    background-color: #f8f9ff;
}

.btn-minus, .btn-plus {
    width: 35px;
}
</style>