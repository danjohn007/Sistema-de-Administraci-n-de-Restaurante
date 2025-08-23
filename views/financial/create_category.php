<?php $title = 'Crear Nueva Categoría'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-circle"></i> Crear Nueva Categoría</h1>
    <a href="<?= BASE_URL ?>/financial/categories" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Categorías
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-tags"></i> Información de la Categoría
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="categoryForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre de la Categoría <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                               required placeholder="Ej: Suministros, Servicios, Mantenimiento...">
                        <div class="invalid-feedback">
                            Por favor ingresa un nombre para la categoría.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="3" placeholder="Describe qué tipo de gastos incluye esta categoría..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                        <div class="form-text">
                            Opcional: Agrega una descripción para clarificar el uso de esta categoría
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="color" class="form-control form-control-color" id="color" name="color" 
                                       value="<?= isset($_POST['color']) ? htmlspecialchars($_POST['color']) : '#007bff' ?>" 
                                       required title="Elige un color para la categoría">
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm color-preset" style="background-color: #007bff; width: 30px; height: 30px;" data-color="#007bff"></button>
                                    <button type="button" class="btn btn-sm color-preset" style="background-color: #28a745; width: 30px; height: 30px;" data-color="#28a745"></button>
                                    <button type="button" class="btn btn-sm color-preset" style="background-color: #dc3545; width: 30px; height: 30px;" data-color="#dc3545"></button>
                                    <button type="button" class="btn btn-sm color-preset" style="background-color: #ffc107; width: 30px; height: 30px;" data-color="#ffc107"></button>
                                    <button type="button" class="btn btn-sm color-preset" style="background-color: #6f42c1; width: 30px; height: 30px;" data-color="#6f42c1"></button>
                                    <button type="button" class="btn btn-sm color-preset" style="background-color: #fd7e14; width: 30px; height: 30px;" data-color="#fd7e14"></button>
                                    <button type="button" class="btn btn-sm color-preset" style="background-color: #20c997; width: 30px; height: 30px;" data-color="#20c997"></button>
                                    <button type="button" class="btn btn-sm color-preset" style="background-color: #6c757d; width: 30px; height: 30px;" data-color="#6c757d"></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">
                            Este color se usará para identificar la categoría en los reportes y gráficos
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Crear Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Vista previa de la categoría -->
<div class="row justify-content-center mt-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-eye"></i> Vista Previa
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center" id="categoryPreview">
                    <div class="rounded-circle me-3" id="previewColor" 
                         style="width: 40px; height: 40px; background-color: #007bff;"></div>
                    <div>
                        <h6 class="mb-1" id="previewName">Nombre de la Categoría</h6>
                        <small class="text-muted" id="previewDescription">Descripción de la categoría</small>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge fs-6" id="previewBadge" style="background-color: #007bff;">
                        Ejemplo de etiqueta
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Categorías existentes para referencia -->
<div class="row justify-content-center mt-3">
    <div class="col-md-8">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-lightbulb"></i> Sugerencias de Categorías
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Suministros (ingredientes, bebidas)</li>
                            <li>Servicios (luz, agua, gas, internet)</li>
                            <li>Mantenimiento (reparaciones, limpieza)</li>
                            <li>Marketing (publicidad, promociones)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Personal (capacitación, uniformes)</li>
                            <li>Equipamiento (utensilios, mobiliario)</li>
                            <li>Transporte (delivery, compras)</li>
                            <li>Otros (gastos varios)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($success_message = $this->getFlashMessage('success')): ?>
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <?= htmlspecialchars($success_message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error_message = $this->getFlashMessage('error')): ?>
<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    <?= htmlspecialchars($error_message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<script>
// Form validation
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

// Color preset buttons
document.querySelectorAll('.color-preset').forEach(button => {
    button.addEventListener('click', function() {
        const color = this.getAttribute('data-color');
        document.getElementById('color').value = color;
        updatePreview();
    });
});

// Update preview
function updatePreview() {
    const name = document.getElementById('name').value || 'Nombre de la Categoría';
    const description = document.getElementById('description').value || 'Descripción de la categoría';
    const color = document.getElementById('color').value;
    
    document.getElementById('previewName').textContent = name;
    document.getElementById('previewDescription').textContent = description;
    document.getElementById('previewColor').style.backgroundColor = color;
    document.getElementById('previewBadge').style.backgroundColor = color;
    document.getElementById('previewBadge').textContent = name;
}

// Real-time preview updates
document.getElementById('name').addEventListener('input', updatePreview);
document.getElementById('description').addEventListener('input', updatePreview);
document.getElementById('color').addEventListener('input', updatePreview);

// Initialize preview
updatePreview();

// Auto-focus flow
document.getElementById('name').addEventListener('blur', function() {
    if (this.value) {
        document.getElementById('description').focus();
    }
});
</script>