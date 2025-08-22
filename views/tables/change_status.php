<?php $title = 'Cambiar Estado de Mesa'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-arrow-repeat"></i> Cambiar Estado de Mesa</h1>
    <a href="<?= BASE_URL ?>/tables" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Mesa <?= $table['number'] ?>
                    <span class="badge bg-secondary ms-2"><?= $table['capacity'] ?> personas</span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label class="form-label">Estado Actual</label>
                    <div>
                        <span class="badge bg-<?= getStatusColor($table['status']) ?> fs-6">
                            <?= getStatusLabel($table['status']) ?>
                        </span>
                    </div>
                </div>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="status" class="form-label">
                            <i class="bi bi-flag"></i> Nuevo Estado *
                        </label>
                        <select 
                            class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" 
                            id="status" 
                            name="status"
                            required
                        >
                            <option value="">Seleccionar estado...</option>
                            <?php foreach ($statuses as $value => $label): ?>
                                <option value="<?= $value ?>" 
                                    <?= ($old['status'] ?? '') === $value ? 'selected' : '' ?>
                                    data-color="<?= getStatusColor($value) ?>">
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['status'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['status']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text" id="statusHelp">
                            Selecciona el nuevo estado para la mesa
                        </div>
                    </div>

                    <div class="mb-3" id="waiterSection" style="display: none;">
                        <label for="waiter_id" class="form-label">
                            <i class="bi bi-person-badge"></i> Mesero Asignado *
                        </label>
                        <select 
                            class="form-select <?= isset($errors['waiter_id']) ? 'is-invalid' : '' ?>" 
                            id="waiter_id" 
                            name="waiter_id"
                        >
                            <option value="">Seleccionar mesero...</option>
                            <?php foreach ($waiters as $waiter): ?>
                                <option value="<?= $waiter['id'] ?>" 
                                    <?= ($old['waiter_id'] ?? $table['waiter_id']) == $waiter['id'] ? 'selected' : '' ?>>
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
                            Debe seleccionar un mesero para marcar la mesa como ocupada
                        </div>
                    </div>

                    <!-- Status Information Cards -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-success" id="statusInfo-<?= TABLE_AVAILABLE ?>" style="display: none;">
                                <div class="card-body">
                                    <h6 class="card-title text-success">
                                        <i class="bi bi-check-circle"></i> Mesa Disponible
                                    </h6>
                                    <p class="card-text small">
                                        La mesa estará libre y lista para recibir nuevos clientes. 
                                        Se eliminará cualquier asignación de mesero.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="card border-warning" id="statusInfo-<?= TABLE_OCCUPIED ?>" style="display: none;">
                                <div class="card-body">
                                    <h6 class="card-title text-warning">
                                        <i class="bi bi-people"></i> Mesa Ocupada
                                    </h6>
                                    <p class="card-text small">
                                        La mesa tiene clientes y está siendo atendida por un mesero. 
                                        Debe seleccionar el mesero responsable.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="card border-info" id="statusInfo-<?= TABLE_BILL_REQUESTED ?>" style="display: none;">
                                <div class="card-body">
                                    <h6 class="card-title text-info">
                                        <i class="bi bi-receipt"></i> Cuenta Solicitada
                                    </h6>
                                    <p class="card-text small">
                                        Los clientes han solicitado la cuenta y están listos para pagar. 
                                        La mesa mantiene el mesero asignado.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="card border-secondary" id="statusInfo-cerrada" style="display: none;">
                                <div class="card-body">
                                    <h6 class="card-title text-secondary">
                                        <i class="bi bi-lock"></i> Mesa Cerrada
                                    </h6>
                                    <p class="card-text small">
                                        La mesa no está disponible para uso (mantenimiento, reservada, etc.). 
                                        Se eliminará cualquier asignación de mesero.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/tables" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Cambiar Estado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
function getStatusColor($status) {
    switch($status) {
        case TABLE_AVAILABLE:
            return 'success';
        case TABLE_OCCUPIED:
            return 'warning';
        case TABLE_BILL_REQUESTED:
            return 'info';
        case 'cerrada':
            return 'secondary';
        default:
            return 'secondary';
    }
}

function getStatusLabel($status) {
    switch($status) {
        case TABLE_AVAILABLE:
            return 'Disponible';
        case TABLE_OCCUPIED:
            return 'Ocupada';
        case TABLE_BILL_REQUESTED:
            return 'Cuenta Solicitada';
        case 'cerrada':
            return 'Cerrada';
        default:
            return ucfirst($status);
    }
}
?>

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

// Status change handler
document.getElementById('status').addEventListener('change', function() {
    var selectedStatus = this.value;
    var waiterSection = document.getElementById('waiterSection');
    var waiterSelect = document.getElementById('waiter_id');
    var helpText = document.getElementById('statusHelp');
    
    // Hide all status info cards
    var statusCards = document.querySelectorAll('[id^="statusInfo-"]');
    statusCards.forEach(function(card) {
        card.style.display = 'none';
    });
    
    // Show relevant status info card
    if (selectedStatus) {
        var statusCard = document.getElementById('statusInfo-' + selectedStatus);
        if (statusCard) {
            statusCard.style.display = 'block';
        }
    }
    
    // Show/hide waiter section based on status
    if (selectedStatus === '<?= TABLE_OCCUPIED ?>') {
        waiterSection.style.display = 'block';
        waiterSelect.required = true;
        helpText.textContent = 'Mesa ocupada requiere mesero asignado';
        helpText.className = 'form-text text-warning';
    } else {
        waiterSection.style.display = 'none';
        waiterSelect.required = false;
        
        switch(selectedStatus) {
            case '<?= TABLE_AVAILABLE ?>':
                helpText.textContent = 'La mesa se marcará como disponible';
                helpText.className = 'form-text text-success';
                break;
            case '<?= TABLE_BILL_REQUESTED ?>':
                helpText.textContent = 'La cuenta ha sido solicitada por los clientes';
                helpText.className = 'form-text text-info';
                break;
            case 'cerrada':
                helpText.textContent = 'La mesa no estará disponible para uso';
                helpText.className = 'form-text text-secondary';
                break;
            default:
                helpText.textContent = 'Selecciona el nuevo estado para la mesa';
                helpText.className = 'form-text';
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    var statusSelect = document.getElementById('status');
    if (statusSelect.value) {
        statusSelect.dispatchEvent(new Event('change'));
    }
});
</script>