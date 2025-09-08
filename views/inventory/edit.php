<?php $title = 'Editar Producto - ' . $product['name']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> Editar Producto</h1>
    <a href="<?= BASE_URL ?>/inventory/show/<?= $product['id'] ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Detalles
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-form"></i> Información del Producto
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/inventory/edit/<?= $product['id'] ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                       id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" 
                                       placeholder="Ej: Aceite de cocina, Harina, etc." required>
                                <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Categoría</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">Seleccionar categoría...</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" 
                                            <?= $product['category'] === $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">O escribir una nueva categoría:</small>
                                <input type="text" class="form-control mt-1" id="new_category" name="new_category" 
                                       placeholder="Nueva categoría...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Descripción detallada del producto..."><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="unit_measure" class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                                <select class="form-select <?= isset($errors['unit_measure']) ? 'is-invalid' : '' ?>" 
                                        id="unit_measure" name="unit_measure" required>
                                    <option value="">Seleccionar unidad...</option>
                                    <option value="unidad" <?= $product['unit_measure'] === 'unidad' ? 'selected' : '' ?>>Unidad</option>
                                    <option value="kg" <?= $product['unit_measure'] === 'kg' ? 'selected' : '' ?>>Kilogramos (kg)</option>
                                    <option value="g" <?= $product['unit_measure'] === 'g' ? 'selected' : '' ?>>Gramos (g)</option>
                                    <option value="l" <?= $product['unit_measure'] === 'l' ? 'selected' : '' ?>>Litros (l)</option>
                                    <option value="ml" <?= $product['unit_measure'] === 'ml' ? 'selected' : '' ?>>Mililitros (ml)</option>
                                    <option value="pza" <?= $product['unit_measure'] === 'pza' ? 'selected' : '' ?>>Piezas</option>
                                    <option value="caja" <?= $product['unit_measure'] === 'caja' ? 'selected' : '' ?>>Cajas</option>
                                    <option value="paquete" <?= $product['unit_measure'] === 'paquete' ? 'selected' : '' ?>>Paquetes</option>
                                </select>
                                <?php if (isset($errors['unit_measure'])): ?>
                                <div class="invalid-feedback"><?= $errors['unit_measure'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cost_per_unit" class="form-label">Costo por Unidad <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control <?= isset($errors['cost_per_unit']) ? 'is-invalid' : '' ?>" 
                                           id="cost_per_unit" name="cost_per_unit" 
                                           value="<?= htmlspecialchars($product['cost_per_unit']) ?>" 
                                           placeholder="0.00" required>
                                </div>
                                <?php if (isset($errors['cost_per_unit'])): ?>
                                <div class="invalid-feedback"><?= $errors['cost_per_unit'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_stock" class="form-label">Stock Mínimo <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" min="0" 
                                       class="form-control <?= isset($errors['min_stock']) ? 'is-invalid' : '' ?>" 
                                       id="min_stock" name="min_stock" 
                                       value="<?= htmlspecialchars($product['min_stock']) ?>" 
                                       placeholder="0.000" required>
                                <small class="text-muted">Cantidad mínima antes de alertar</small>
                                <?php if (isset($errors['min_stock'])): ?>
                                <div class="invalid-feedback"><?= $errors['min_stock'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_stock" class="form-label">Stock Máximo <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" min="0" 
                                       class="form-control <?= isset($errors['max_stock']) ? 'is-invalid' : '' ?>" 
                                       id="max_stock" name="max_stock" 
                                       value="<?= htmlspecialchars($product['max_stock']) ?>" 
                                       placeholder="0.000" required>
                                <small class="text-muted">Capacidad máxima de almacenamiento</small>
                                <?php if (isset($errors['max_stock'])): ?>
                                <div class="invalid-feedback"><?= $errors['max_stock'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_dish_ingredient" name="is_dish_ingredient" 
                                   <?= $product['is_dish_ingredient'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_dish_ingredient">
                                <strong>Es ingrediente de platillos</strong>
                            </label>
                            <small class="form-text text-muted d-block">
                                Marque esta opción si este producto se usa como ingrediente en la preparación de platillos.
                                Esto permitirá que se descuente automáticamente del inventario cuando se vendan los platillos.
                            </small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/inventory/show/<?= $product['id'] ?>" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Actualizar Producto
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
                    <i class="bi bi-info-circle"></i> Información Actual
                </h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-6">Stock Actual:</dt>
                    <dd class="col-sm-6">
                        <strong><?= number_format($product['current_stock'], 3) ?></strong> <?= htmlspecialchars($product['unit_measure']) ?>
                    </dd>
                    
                    <dt class="col-sm-6">Valor Total:</dt>
                    <dd class="col-sm-6">
                        <strong>$<?= number_format($product['current_stock'] * $product['cost_per_unit'], 2) ?></strong>
                    </dd>
                    
                    <dt class="col-sm-6">Creado:</dt>
                    <dd class="col-sm-6">
                        <?= date('d/m/Y', strtotime($product['created_at'])) ?>
                    </dd>
                    
                    <dt class="col-sm-6">Actualizado:</dt>
                    <dd class="col-sm-6">
                        <?= date('d/m/Y', strtotime($product['updated_at'])) ?>
                    </dd>
                </dl>
                
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle"></i> Importante</h6>
                    <p class="mb-0">
                        No se puede modificar el stock actual desde aquí.
                        Use "Registrar Movimiento" para ajustar el inventario.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightbulb"></i> Consejos
                </h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li><strong>Stock Mínimo:</strong> Configure un valor que le permita reordenar a tiempo.</li>
                    <li><strong>Stock Máximo:</strong> No exceda la capacidad de almacenamiento.</li>
                    <li><strong>Costo:</strong> Mantenga actualizado el costo para reportes precisos.</li>
                    <li><strong>Ingredientes:</strong> Marque productos que se usan en recetas.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar nueva categoría
    const categorySelect = document.getElementById('category');
    const newCategoryInput = document.getElementById('new_category');
    
    newCategoryInput.addEventListener('input', function() {
        if (this.value.trim()) {
            categorySelect.value = '';
        }
    });
    
    categorySelect.addEventListener('change', function() {
        if (this.value) {
            newCategoryInput.value = '';
        }
    });
    
    // Validar que el stock máximo sea mayor al mínimo
    const minStock = document.getElementById('min_stock');
    const maxStock = document.getElementById('max_stock');
    
    function validateStockLimits() {
        const minValue = parseFloat(minStock.value) || 0;
        const maxValue = parseFloat(maxStock.value) || 0;
        
        if (minValue > 0 && maxValue > 0 && maxValue <= minValue) {
            maxStock.setCustomValidity('El stock máximo debe ser mayor al stock mínimo');
        } else {
            maxStock.setCustomValidity('');
        }
    }
    
    minStock.addEventListener('input', validateStockLimits);
    maxStock.addEventListener('input', validateStockLimits);
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

.alert {
    border: none;
    border-radius: 0.5rem;
}

dl {
    margin-bottom: 0;
}

dt {
    font-weight: 600;
}

dd {
    margin-bottom: 0.5rem;
}
</style>