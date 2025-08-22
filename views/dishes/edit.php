<?php $title = 'Editar Platillo'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil-square"></i> Editar Platillo</h1>
    <a href="<?= BASE_URL ?>/dishes" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Editando: <?= htmlspecialchars($dish['name']) ?>
                    <?php if ($dish['category']): ?>
                        <span class="badge bg-secondary ms-2"><?= htmlspecialchars($dish['category']) ?></span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="bi bi-cup-hot"></i> Nombre del Platillo *
                        </label>
                        <input 
                            type="text" 
                            class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                            id="name" 
                            name="name" 
                            value="<?= htmlspecialchars($old['name'] ?? $dish['name']) ?>"
                            placeholder="Ej: Tacos al Pastor, Ensalada César..."
                            required
                            maxlength="255"
                        >
                        <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['name']) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="bi bi-text-paragraph"></i> Descripción
                        </label>
                        <textarea 
                            class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
                            id="description" 
                            name="description" 
                            rows="3"
                            placeholder="Descripción detallada del platillo, ingredientes, etc."
                            maxlength="1000"
                        ><?= htmlspecialchars($old['description'] ?? $dish['description']) ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['description']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Descripción opcional del platillo (máximo 1000 caracteres)
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">
                                    <i class="bi bi-currency-dollar"></i> Precio *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input 
                                        type="number" 
                                        class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" 
                                        id="price" 
                                        name="price" 
                                        value="<?= htmlspecialchars($old['price'] ?? $dish['price']) ?>"
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0.01"
                                        max="999999.99"
                                        required
                                    >
                                    <?php if (isset($errors['price'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['price']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">
                                    <i class="bi bi-tags"></i> Categoría
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control <?= isset($errors['category']) ? 'is-invalid' : '' ?>" 
                                    id="category" 
                                    name="category" 
                                    value="<?= htmlspecialchars($old['category'] ?? $dish['category']) ?>"
                                    placeholder="Categoría del platillo..."
                                    maxlength="100"
                                    list="categoryList"
                                >
                                <datalist id="categoryList">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                                <?php if (isset($errors['category'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['category']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Información adicional</label>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($dish['created_at'])) ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Última actualización:</strong> <?= date('d/m/Y H:i', strtotime($dish['updated_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/dishes" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Actualizar Platillo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Bootstrap form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>