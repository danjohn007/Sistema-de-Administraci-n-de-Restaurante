<?php $title = 'Agregar Producto'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-circle"></i> Agregar Producto</h1>
    <a href="<?= BASE_URL ?>/inventory" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Inventario
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-plus"></i> Nuevo Producto
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/inventory/create">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                       id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="category" name="category" 
                                       value="<?= htmlspecialchars($old['category'] ?? '') ?>" 
                                       list="category-list" placeholder="Ej: Carnes, Verduras, Bebidas">
                                <datalist id="category-list">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Descripción del producto..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="unit_measure" class="form-label">Unidad de Medida *</label>
                                <select class="form-select <?= isset($errors['unit_measure']) ? 'is-invalid' : '' ?>" 
                                        id="unit_measure" name="unit_measure" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="unidades" <?= ($old['unit_measure'] ?? '') === 'unidades' ? 'selected' : '' ?>>Unidades</option>
                                    <option value="kg" <?= ($old['unit_measure'] ?? '') === 'kg' ? 'selected' : '' ?>>Kilogramos (kg)</option>
                                    <option value="litros" <?= ($old['unit_measure'] ?? '') === 'litros' ? 'selected' : '' ?>>Litros</option>
                                    <option value="gramos" <?= ($old['unit_measure'] ?? '') === 'gramos' ? 'selected' : '' ?>>Gramos</option>
                                    <option value="ml" <?= ($old['unit_measure'] ?? '') === 'ml' ? 'selected' : '' ?>>Mililitros (ml)</option>
                                    <option value="piezas" <?= ($old['unit_measure'] ?? '') === 'piezas' ? 'selected' : '' ?>>Piezas</option>
                                    <option value="cajas" <?= ($old['unit_measure'] ?? '') === 'cajas' ? 'selected' : '' ?>>Cajas</option>
                                    <option value="bolsas" <?= ($old['unit_measure'] ?? '') === 'bolsas' ? 'selected' : '' ?>>Bolsas</option>
                                </select>
                                <?php if (isset($errors['unit_measure'])): ?>
                                <div class="invalid-feedback"><?= $errors['unit_measure'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="current_stock" class="form-label">Stock Inicial</label>
                                <input type="number" step="0.001" class="form-control" id="current_stock" 
                                       name="current_stock" value="<?= htmlspecialchars($old['current_stock'] ?? '0') ?>" 
                                       min="0" placeholder="0.000">
                                <small class="text-muted">Stock actual del producto</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cost_per_unit" class="form-label">Costo por Unidad *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control <?= isset($errors['cost_per_unit']) ? 'is-invalid' : '' ?>" 
                                           id="cost_per_unit" name="cost_per_unit" 
                                           value="<?= htmlspecialchars($old['cost_per_unit'] ?? '') ?>" 
                                           min="0" placeholder="0.00" required>
                                    <?php if (isset($errors['cost_per_unit'])): ?>
                                    <div class="invalid-feedback"><?= $errors['cost_per_unit'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_stock" class="form-label">Stock Mínimo *</label>
                                <input type="number" step="0.001" class="form-control <?= isset($errors['min_stock']) ? 'is-invalid' : '' ?>" 
                                       id="min_stock" name="min_stock" 
                                       value="<?= htmlspecialchars($old['min_stock'] ?? '') ?>" 
                                       min="0" placeholder="0.000" required>
                                <small class="text-muted">Cantidad mínima para alertas</small>
                                <?php if (isset($errors['min_stock'])): ?>
                                <div class="invalid-feedback"><?= $errors['min_stock'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_stock" class="form-label">Stock Máximo *</label>
                                <input type="number" step="0.001" class="form-control <?= isset($errors['max_stock']) ? 'is-invalid' : '' ?>" 
                                       id="max_stock" name="max_stock" 
                                       value="<?= htmlspecialchars($old['max_stock'] ?? '') ?>" 
                                       min="0" placeholder="0.000" required>
                                <small class="text-muted">Capacidad máxima de almacenamiento</small>
                                <?php if (isset($errors['max_stock'])): ?>
                                <div class="invalid-feedback"><?= $errors['max_stock'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_dish_ingredient" 
                                   name="is_dish_ingredient" value="1" 
                                   <?= isset($old['is_dish_ingredient']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_dish_ingredient">
                                <strong>Es ingrediente de platillos</strong>
                            </label>
                            <div class="form-text">
                                Marque esta opción si este producto se usa como ingrediente en la preparación de platillos.
                                Esto permitirá configurar recetas y descuentos automáticos de inventario.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/inventory" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Guardar Producto
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
                    <i class="bi bi-info-circle"></i> Ayuda
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-lightbulb"></i> Consejos</h6>
                    <ul class="mb-0">
                        <li><strong>Nombre:</strong> Sea específico (ej: "Pollo (kg)" en lugar de solo "Pollo")</li>
                        <li><strong>Categoría:</strong> Agrupe productos similares para facilitar la búsqueda</li>
                        <li><strong>Stock Mínimo:</strong> Configure alertas para evitar quedarse sin producto</li>
                        <li><strong>Costo:</strong> Use el costo promedio de compra</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle"></i> Importante</h6>
                    <p class="mb-0">
                        Los productos marcados como <strong>ingredientes</strong> pueden ser utilizados 
                        en recetas de platillos para descuento automático de inventario.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list"></i> Categorías Existentes
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                <p class="text-muted">No hay categorías aún</p>
                <?php else: ?>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($categories as $cat): ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($cat) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de stock mínimo vs máximo
    const minStock = document.getElementById('min_stock');
    const maxStock = document.getElementById('max_stock');
    
    function validateStocks() {
        const minVal = parseFloat(minStock.value) || 0;
        const maxVal = parseFloat(maxStock.value) || 0;
        
        if (minVal >= maxVal && maxVal > 0) {
            maxStock.setCustomValidity('El stock máximo debe ser mayor al stock mínimo');
        } else {
            maxStock.setCustomValidity('');
        }
    }
    
    minStock.addEventListener('input', validateStocks);
    maxStock.addEventListener('input', validateStocks);
});
</script>

<style>
.form-label {
    font-weight: 600;
}

.form-text {
    font-size: 0.875em;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.badge {
    font-size: 0.75em;
}

.input-group-text {
    font-weight: 600;
}
</style>