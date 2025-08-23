<?php $title = 'Crear Nueva Sucursal'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-building"></i> Crear Nueva Sucursal</h1>
    <a href="<?= BASE_URL ?>/financial/branches" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Sucursales
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-building"></i> Información de la Sucursal
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="branchForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre de la Sucursal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                               required placeholder="Ej: Sucursal Centro, Sucursal Norte...">
                        <div class="invalid-feedback">
                            Por favor ingresa un nombre para la sucursal.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Dirección <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" 
                                  rows="3" required placeholder="Dirección completa de la sucursal..."><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                        <div class="invalid-feedback">
                            Por favor ingresa la dirección de la sucursal.
                        </div>
                        <div class="form-text">
                            Incluye calle, número, colonia, ciudad y código postal
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" 
                                       placeholder="Ej: 555-123-4567">
                                <div class="form-text">
                                    Teléfono de contacto de la sucursal (opcional)
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="manager_user_id" class="form-label">Gerente</label>
                                <select class="form-select" id="manager_user_id" name="manager_user_id">
                                    <option value="">Sin gerente asignado</option>
                                    <?php foreach ($managers as $manager): ?>
                                    <option value="<?= $manager['id'] ?>" 
                                            <?= (isset($_POST['manager_user_id']) && $_POST['manager_user_id'] == $manager['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($manager['name']) ?> (<?= htmlspecialchars($manager['email']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Asigna un administrador como gerente (opcional)
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Crear Sucursal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Vista previa de la sucursal -->
<div class="row justify-content-center mt-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-eye"></i> Vista Previa
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="bi bi-building display-6 text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1" id="previewName">Nombre de la Sucursal</h5>
                        <p class="text-muted mb-2" id="previewAddress">Dirección de la sucursal</p>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="bi bi-telephone"></i> 
                                    <span id="previewPhone">Sin teléfono</span>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="bi bi-person-badge"></i> 
                                    <span id="previewManager">Sin gerente</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Información sobre sucursales -->
<div class="row justify-content-center mt-3">
    <div class="col-md-8">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-lightbulb"></i> Beneficios de Gestionar Sucursales
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Organización:</h6>
                        <ul class="mb-0">
                            <li>Separación clara de operaciones</li>
                            <li>Asignación específica de personal</li>
                            <li>Control individual de cada ubicación</li>
                            <li>Reportes segmentados por sucursal</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Control Financiero:</h6>
                        <ul class="mb-0">
                            <li>Gastos específicos por sucursal</li>
                            <li>Retiros controlados por ubicación</li>
                            <li>Cortes de caja independientes</li>
                            <li>Análisis de rentabilidad individual</li>
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

// Update preview
function updatePreview() {
    const name = document.getElementById('name').value || 'Nombre de la Sucursal';
    const address = document.getElementById('address').value || 'Dirección de la sucursal';
    const phone = document.getElementById('phone').value || 'Sin teléfono';
    const managerSelect = document.getElementById('manager_user_id');
    const manager = managerSelect.options[managerSelect.selectedIndex].text || 'Sin gerente';
    
    document.getElementById('previewName').textContent = name;
    document.getElementById('previewAddress').textContent = address;
    document.getElementById('previewPhone').textContent = phone;
    document.getElementById('previewManager').textContent = manager === 'Sin gerente asignado' ? 'Sin gerente' : manager;
}

// Real-time preview updates
document.getElementById('name').addEventListener('input', updatePreview);
document.getElementById('address').addEventListener('input', updatePreview);
document.getElementById('phone').addEventListener('input', updatePreview);
document.getElementById('manager_user_id').addEventListener('change', updatePreview);

// Initialize preview
updatePreview();

// Auto-focus flow
document.getElementById('name').addEventListener('blur', function() {
    if (this.value) {
        document.getElementById('address').focus();
    }
});

document.getElementById('address').addEventListener('blur', function() {
    if (this.value) {
        document.getElementById('phone').focus();
    }
});

// Phone number formatting
document.getElementById('phone').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    if (value.length >= 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
    } else if (value.length >= 3) {
        value = value.replace(/(\d{3})(\d{0,3})/, '$1-$2');
    }
    this.value = value;
    updatePreview();
});

// Confirmation before submit
document.getElementById('branchForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value;
    const address = document.getElementById('address').value;
    
    if (name && address) {
        const confirmation = confirm(`¿Confirmas la creación de la sucursal "${name}"?`);
        if (!confirmation) {
            e.preventDefault();
        }
    }
});
</script>