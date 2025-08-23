<?php $title = 'Realizar Corte de Caja'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-journal-check"></i> Realizar Corte de Caja</h1>
    <a href="<?= BASE_URL ?>/financial/closures" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Cortes
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-journal-check"></i> Información del Turno
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="closureForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift_start" class="form-label">Inicio del Turno <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="shift_start" name="shift_start" 
                                       value="<?= isset($_POST['shift_start']) ? htmlspecialchars($_POST['shift_start']) : $shift_start ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor selecciona la fecha y hora de inicio del turno.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift_end" class="form-label">Fin del Turno <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="shift_end" name="shift_end" 
                                       value="<?= isset($_POST['shift_end']) ? htmlspecialchars($_POST['shift_end']) : $shift_end ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor selecciona la fecha y hora de fin del turno.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($branches)): ?>
                    <div class="mb-3">
                        <label for="branch_id" class="form-label">Sucursal</label>
                        <select class="form-select" id="branch_id" name="branch_id">
                            <option value="">Sin sucursal específica</option>
                            <?php foreach ($branches as $branch): ?>
                            <option value="<?= $branch['id'] ?>" 
                                    <?= (isset($_POST['branch_id']) && $_POST['branch_id'] == $branch['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($branch['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            Selecciona la sucursal para este corte (opcional)
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="initial_cash" class="form-label">Efectivo Inicial <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="initial_cash" name="initial_cash" 
                                           step="0.01" min="0" required
                                           value="<?= isset($_POST['initial_cash']) ? htmlspecialchars($_POST['initial_cash']) : '' ?>"
                                           placeholder="0.00">
                                </div>
                                <div class="form-text">
                                    Monto de efectivo con el que se inició el turno
                                </div>
                                <div class="invalid-feedback">
                                    Por favor ingresa el efectivo inicial.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="final_cash" class="form-label">Efectivo Final <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="final_cash" name="final_cash" 
                                           step="0.01" min="0" required
                                           value="<?= isset($_POST['final_cash']) ? htmlspecialchars($_POST['final_cash']) : '' ?>"
                                           placeholder="0.00">
                                </div>
                                <div class="form-text">
                                    Monto de efectivo actual en caja
                                </div>
                                <div class="invalid-feedback">
                                    Por favor ingresa el efectivo final.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas Adicionales</label>
                        <textarea class="form-control" id="notes" name="notes" 
                                  rows="3" placeholder="Observaciones, incidencias o notas del turno..."><?= isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '' ?></textarea>
                        <div class="form-text">
                            Información adicional sobre el turno (opcional)
                        </div>
                    </div>
                    
                    <!-- Vista previa de cálculos automáticos -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="bi bi-calculator"></i> Cálculos Automáticos
                        </h6>
                        <p class="mb-0">
                            El sistema calculará automáticamente:
                        </p>
                        <ul class="mb-0 mt-2">
                            <li><strong>Total de Ventas:</strong> Suma de todos los tickets generados en el período</li>
                            <li><strong>Total de Gastos:</strong> Suma de todos los gastos registrados en el período</li>
                            <li><strong>Total de Retiros:</strong> Suma de todos los retiros autorizados en el período</li>
                            <li><strong>Utilidad Neta:</strong> Ventas - Gastos - Retiros</li>
                        </ul>
                    </div>
                    
                    <!-- Diferencia de efectivo -->
                    <div class="alert alert-warning d-none" id="cashDifferenceAlert">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle"></i> Diferencia de Efectivo
                        </h6>
                        <p class="mb-0" id="cashDifferenceText"></p>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Realizar Corte de Caja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Información de ayuda -->
<div class="row justify-content-center mt-3">
    <div class="col-md-10">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-lightbulb"></i> Instrucciones para el Corte de Caja
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Antes del Corte:</h6>
                        <ul>
                            <li>Cuenta todo el efectivo en caja</li>
                            <li>Verifica que todos los tickets estén registrados</li>
                            <li>Confirma que todos los gastos estén documentados</li>
                            <li>Revisa los retiros autorizados del período</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Durante el Corte:</h6>
                        <ul>
                            <li>Ingresa las fechas exactas del turno</li>
                            <li>Verifica el efectivo inicial y final</li>
                            <li>Agrega notas de cualquier incidencia</li>
                            <li>Revisa los cálculos automáticos antes de guardar</li>
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

// Calculate cash difference
function calculateCashDifference() {
    const initialCash = parseFloat(document.getElementById('initial_cash').value) || 0;
    const finalCash = parseFloat(document.getElementById('final_cash').value) || 0;
    const difference = finalCash - initialCash;
    
    const alert = document.getElementById('cashDifferenceAlert');
    const text = document.getElementById('cashDifferenceText');
    
    if (initialCash > 0 && finalCash > 0) {
        if (difference !== 0) {
            alert.classList.remove('d-none');
            if (difference > 0) {
                text.textContent = `Hay un excedente de $${difference.toFixed(2)} en efectivo.`;
                alert.className = 'alert alert-info';
            } else {
                text.textContent = `Hay un faltante de $${Math.abs(difference).toFixed(2)} en efectivo.`;
                alert.className = 'alert alert-warning';
            }
        } else {
            alert.classList.add('d-none');
        }
    } else {
        alert.classList.add('d-none');
    }
}

// Add event listeners for cash calculation
document.getElementById('initial_cash').addEventListener('input', calculateCashDifference);
document.getElementById('final_cash').addEventListener('input', calculateCashDifference);

// Validate shift times
document.getElementById('shift_start').addEventListener('change', function() {
    const shiftEnd = document.getElementById('shift_end');
    if (this.value && shiftEnd.value && this.value >= shiftEnd.value) {
        alert('La hora de inicio debe ser anterior a la hora de fin');
        this.focus();
    }
});

document.getElementById('shift_end').addEventListener('change', function() {
    const shiftStart = document.getElementById('shift_start');
    if (this.value && shiftStart.value && this.value <= shiftStart.value) {
        alert('La hora de fin debe ser posterior a la hora de inicio');
        this.focus();
    }
});

// Auto-focus flow
document.getElementById('shift_start').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('shift_end').focus();
    }
});

document.getElementById('shift_end').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('initial_cash').focus();
    }
});

document.getElementById('initial_cash').addEventListener('blur', function() {
    if (this.value) {
        document.getElementById('final_cash').focus();
    }
});
</script>