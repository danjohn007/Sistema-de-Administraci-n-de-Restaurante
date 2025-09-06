<?php $title = 'Crear Platillo'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-circle-fill"></i> Crear Platillo</h1>
    <a href="<?= BASE_URL ?>/dishes" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información del Platillo</h5>
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
                            value="<?= htmlspecialchars($old['name'] ?? '') ?>"
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
                        ><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
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
                                        value="<?= htmlspecialchars($old['price'] ?? '') ?>"
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
                                <div class="form-text">
                                    Precio del platillo en pesos mexicanos
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">
                                    <i class="bi bi-tags"></i> Categoría
                                </label>
                                <div class="input-group">
                                    <select 
                                        class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>" 
                                        id="categorySelect"
                                    >
                                        <option value="">Seleccionar categoría existente...</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat) ?>" 
                                                <?= ($old['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleNewCategory()">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                                
                                <input 
                                    type="text" 
                                    class="form-control mt-2 <?= isset($errors['category']) ? 'is-invalid' : '' ?>" 
                                    id="category" 
                                    name="category" 
                                    value="<?= htmlspecialchars($old['category'] ?? '') ?>"
                                    placeholder="O escribir nueva categoría..."
                                    maxlength="100"
                                    style="display: none;"
                                >
                                
                                <?php if (isset($errors['category'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['category']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Categoría para organizar el menú (opcional)
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Validity Period Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-calendar-range"></i> Período de Validez
                                <small class="text-muted">(Opcional)</small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="has_validity" name="has_validity" 
                                           <?= ($old['has_validity'] ?? '0') == '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="has_validity">
                                        <strong>Configurar disponibilidad específica</strong>
                                    </label>
                                </div>
                                <div class="form-text">
                                    Por defecto, los platillos están siempre disponibles. Activa esta opción para configurar períodos específicos.
                                </div>
                            </div>

                            <div id="validity-settings" style="display: <?= ($old['has_validity'] ?? '0') == '1' ? 'block' : 'none' ?>;">
                                <!-- Date Range -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="validity_start" class="form-label">
                                            <i class="bi bi-calendar-check"></i> Fecha de Inicio
                                        </label>
                                        <input 
                                            type="date" 
                                            class="form-control <?= isset($errors['validity_start']) ? 'is-invalid' : '' ?>" 
                                            id="validity_start" 
                                            name="validity_start" 
                                            value="<?= htmlspecialchars($old['validity_start'] ?? '') ?>"
                                        >
                                        <?php if (isset($errors['validity_start'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['validity_start']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="form-text">Opcional: Fecha desde la cual el platillo estará disponible</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="validity_end" class="form-label">
                                            <i class="bi bi-calendar-x"></i> Fecha de Finalización
                                        </label>
                                        <input 
                                            type="date" 
                                            class="form-control <?= isset($errors['validity_end']) ? 'is-invalid' : '' ?>" 
                                            id="validity_end" 
                                            name="validity_end" 
                                            value="<?= htmlspecialchars($old['validity_end'] ?? '') ?>"
                                        >
                                        <?php if (isset($errors['validity_end'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['validity_end']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="form-text">Opcional: Fecha hasta la cual el platillo estará disponible</div>
                                    </div>
                                </div>

                                <!-- Day of Week Selection -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-calendar-week"></i> Días de la Semana Disponible
                                    </label>
                                    <div class="row">
                                        <?php 
                                        $days = [
                                            '1' => 'Lunes',
                                            '2' => 'Martes', 
                                            '3' => 'Miércoles',
                                            '4' => 'Jueves',
                                            '5' => 'Viernes',
                                            '6' => 'Sábado',
                                            '0' => 'Domingo'
                                        ];
                                        $selectedDays = $old['availability_days'] ?? '';
                                        ?>
                                        <?php foreach ($days as $dayNum => $dayName): ?>
                                        <div class="col-md-3 col-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="<?= $dayNum ?>" 
                                                       id="day_<?= $dayNum ?>" name="availability_days[]"
                                                       <?= strpos($selectedDays, $dayNum) !== false ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="day_<?= $dayNum ?>">
                                                    <?= $dayName ?>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="form-text">Si no seleccionas ningún día, el platillo estará disponible todos los días dentro del rango de fechas.</div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Importante:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Si no configuras fechas, la disponibilidad por días se aplicará indefinidamente</li>
                                        <li>Si configuras fechas pero no días, el platillo estará disponible todos los días en ese rango</li>
                                        <li>La validación de disponibilidad se realiza automáticamente al crear pedidos</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Información:</strong>
                        <ul class="mb-0 mt-2">
                            <li>El nombre del platillo debe ser único</li>
                            <li>La descripción es opcional pero recomendada</li>
                            <li>Puedes crear nuevas categorías o usar las existentes</li>
                            <li>El platillo estará disponible inmediatamente después de crearlo</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/dishes" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Platillo
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

// Category selection handling
document.getElementById('categorySelect').addEventListener('change', function() {
    var categoryInput = document.getElementById('category');
    categoryInput.value = this.value;
    
    if (this.value) {
        categoryInput.style.display = 'none';
    }
});

function toggleNewCategory() {
    var categorySelect = document.getElementById('categorySelect');
    var categoryInput = document.getElementById('category');
    
    if (categoryInput.style.display === 'none') {
        categoryInput.style.display = 'block';
        categoryInput.focus();
        categorySelect.value = '';
    } else {
        categoryInput.style.display = 'none';
        categoryInput.value = '';
    }
}

// Character counter for description
document.getElementById('description').addEventListener('input', function() {
    var maxLength = 1000;
    var currentLength = this.value.length;
    var helpText = this.nextElementSibling.nextElementSibling;
    
    if (currentLength > maxLength * 0.8) {
        helpText.textContent = `${currentLength}/${maxLength} caracteres`;
        helpText.className = currentLength >= maxLength ? 'form-text text-danger' : 'form-text text-warning';
    } else {
        helpText.textContent = 'Descripción opcional del platillo (máximo 1000 caracteres)';
        helpText.className = 'form-text';
    }
});

// Validity period handling
document.getElementById('has_validity').addEventListener('change', function() {
    var validitySettings = document.getElementById('validity-settings');
    validitySettings.style.display = this.checked ? 'block' : 'none';
});
</script>