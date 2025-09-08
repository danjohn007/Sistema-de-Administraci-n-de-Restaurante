<?php $title = 'Registrar Movimiento de Inventario'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-circle"></i> Registrar Movimiento</h1>
    <a href="<?= BASE_URL ?>/inventory/movements" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Movimientos
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-form"></i> Información del Movimiento
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/inventory/addMovement">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Producto <span class="text-danger">*</span></label>
                                <select class="form-select <?= isset($errors['product_id']) ? 'is-invalid' : '' ?>" 
                                        id="product_id" name="product_id" required>
                                    <option value="">Seleccionar producto...</option>
                                    <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>" 
                                            <?= ($old['product_id'] ?? '') == $product['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($product['name']) ?> 
                                        (<?= htmlspecialchars($product['unit_measure']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['product_id'])): ?>
                                <div class="invalid-feedback"><?= $errors['product_id'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="movement_type" class="form-label">Tipo de Movimiento <span class="text-danger">*</span></label>
                                <select class="form-select <?= isset($errors['movement_type']) ? 'is-invalid' : '' ?>" 
                                        id="movement_type" name="movement_type" required>
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="entrada" <?= ($old['movement_type'] ?? '') === 'entrada' ? 'selected' : '' ?>>
                                        <i class="bi bi-arrow-down"></i> Entrada
                                    </option>
                                    <option value="salida" <?= ($old['movement_type'] ?? '') === 'salida' ? 'selected' : '' ?>>
                                        <i class="bi bi-arrow-up"></i> Salida
                                    </option>
                                </select>
                                <?php if (isset($errors['movement_type'])): ?>
                                <div class="invalid-feedback"><?= $errors['movement_type'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" min="0.001" 
                                       class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>" 
                                       id="quantity" name="quantity" 
                                       value="<?= htmlspecialchars($old['quantity'] ?? '') ?>" 
                                       placeholder="0.000" required>
                                <?php if (isset($errors['quantity'])): ?>
                                <div class="invalid-feedback"><?= $errors['quantity'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cost_per_unit" class="form-label">Costo por Unidad</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control <?= isset($errors['cost_per_unit']) ? 'is-invalid' : '' ?>" 
                                           id="cost_per_unit" name="cost_per_unit" 
                                           value="<?= htmlspecialchars($old['cost_per_unit'] ?? '') ?>" 
                                           placeholder="0.00">
                                </div>
                                <small class="text-muted">Opcional. Solo se requiere para entradas.</small>
                                <?php if (isset($errors['cost_per_unit'])): ?>
                                <div class="invalid-feedback"><?= $errors['cost_per_unit'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="movement_date" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                                <input type="datetime-local" 
                                       class="form-control <?= isset($errors['movement_date']) ? 'is-invalid' : '' ?>" 
                                       id="movement_date" name="movement_date" 
                                       value="<?= htmlspecialchars($old['movement_date'] ?? date('Y-m-d\TH:i')) ?>" 
                                       required>
                                <?php if (isset($errors['movement_date'])): ?>
                                <div class="invalid-feedback"><?= $errors['movement_date'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="description" name="description" 
                                       value="<?= htmlspecialchars($old['description'] ?? '') ?>" 
                                       placeholder="Motivo del movimiento...">
                                <small class="text-muted">Opcional. Describe el motivo del movimiento.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/inventory/movements" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Registrar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Información
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-lightbulb"></i> Consejos</h6>
                    <ul class="mb-0">
                        <li><strong>Entradas:</strong> Incrementan el stock del producto.</li>
                        <li><strong>Salidas:</strong> Disminuyen el stock del producto.</li>
                        <li><strong>Costo:</strong> Solo es necesario para entradas de mercancía.</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle"></i> Importante</h6>
                    <p class="mb-0">
                        Los movimientos no se pueden eliminar una vez registrados.
                        Asegúrate de que la información sea correcta.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list"></i> Tipos de Movimiento
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-2">
                        <span class="badge bg-success w-100 p-2">
                            <i class="bi bi-arrow-down"></i> Entrada
                        </span>
                        <small class="text-muted d-block mt-1">
                            Compras, devoluciones, ajustes positivos
                        </small>
                    </div>
                    <div class="col-12">
                        <span class="badge bg-danger w-100 p-2">
                            <i class="bi bi-arrow-up"></i> Salida
                        </span>
                        <small class="text-muted d-block mt-1">
                            Ventas, mermas, ajustes negativos
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const movementType = document.getElementById('movement_type');
    const costField = document.getElementById('cost_per_unit');
    const costContainer = costField.closest('.mb-3');
    
    function toggleCostField() {
        if (movementType.value === 'entrada') {
            costContainer.style.display = 'block';
        } else {
            costContainer.style.display = 'none';
            costField.value = '';
        }
    }
    
    movementType.addEventListener('change', toggleCostField);
    toggleCostField(); // Ejecutar al cargar la página
});
</script>

<style>
.form-label {
    font-weight: 600;
}

.text-danger {
    color: #dc3545 !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.badge {
    font-size: 0.75em;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}
</style>