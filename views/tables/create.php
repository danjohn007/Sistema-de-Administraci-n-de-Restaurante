<?php $title = 'Crear Mesa'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-square"></i> Crear Mesa</h1>
    <a href="<?= BASE_URL ?>/tables" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información de la Mesa</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="number" class="form-label">
                                    <i class="bi bi-hash"></i> Número de Mesa *
                                </label>
                                <input 
                                    type="number" 
                                    class="form-control <?= isset($errors['number']) ? 'is-invalid' : '' ?>" 
                                    id="number" 
                                    name="number" 
                                    value="<?= htmlspecialchars($old['number'] ?? '') ?>"
                                    placeholder="Ej: 1, 2, 3..."
                                    required
                                    min="1"
                                    max="999"
                                >
                                <?php if (isset($errors['number'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['number']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Número único para identificar la mesa
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="capacity" class="form-label">
                                    <i class="bi bi-people"></i> Capacidad *
                                </label>
                                <input 
                                    type="number" 
                                    class="form-control <?= isset($errors['capacity']) ? 'is-invalid' : '' ?>" 
                                    id="capacity" 
                                    name="capacity" 
                                    value="<?= htmlspecialchars($old['capacity'] ?? '4') ?>"
                                    placeholder="Número de personas"
                                    required
                                    min="1"
                                    max="20"
                                >
                                <?php if (isset($errors['capacity'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['capacity']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Número máximo de comensales (1-20)
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="zone" class="form-label">
                            <i class="bi bi-geo-alt"></i> Zona *
                        </label>
                        <select 
                            class="form-select <?= isset($errors['zone']) ? 'is-invalid' : '' ?>" 
                            id="zone" 
                            name="zone"
                            required
                        >
                            <option value="">Seleccionar zona...</option>
                            <?php foreach ($zones as $zone): ?>
                                <option value="<?= htmlspecialchars($zone['name']) ?>" 
                                        <?= ($old['zone'] ?? 'Salón') == $zone['name'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($zone['name']) ?>
                                    <?php if (!empty($zone['description'])): ?>
                                        - <?= htmlspecialchars($zone['description']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['zone'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['zone']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Área donde se ubicará la mesa
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="waiter_id" class="form-label">
                            <i class="bi bi-person-badge"></i> Mesero Asignado
                        </label>
                        <select 
                            class="form-select <?= isset($errors['waiter_id']) ? 'is-invalid' : '' ?>" 
                            id="waiter_id" 
                            name="waiter_id"
                        >
                            <option value="">Sin asignar (mesa disponible)</option>
                            <?php foreach ($waiters as $waiter): ?>
                                <option value="<?= $waiter['id'] ?>" <?= ($old['waiter_id'] ?? '') == $waiter['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($waiter['name']) ?> (<?= htmlspecialchars($waiter['employee_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['waiter_id'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['waiter_id']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Si asignas un mesero, la mesa se marcará como ocupada
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Información:</strong>
                        <ul class="mb-0 mt-2">
                            <li>La mesa se creará con estado "Disponible" por defecto</li>
                            <li>Si asignas un mesero, el estado cambiará automáticamente a "Ocupada"</li>
                            <li>Puedes cambiar el estado y mesero asignado después de crear la mesa</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/tables" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Mesa
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

// Update form help text based on waiter selection
document.getElementById('waiter_id').addEventListener('change', function() {
    var helpText = this.nextElementSibling.nextElementSibling;
    if (this.value) {
        helpText.textContent = 'La mesa se marcará como ocupada con este mesero asignado';
        helpText.className = 'form-text text-warning';
    } else {
        helpText.textContent = 'La mesa se creará como disponible sin mesero asignado';
        helpText.className = 'form-text';
    }
});
</script>