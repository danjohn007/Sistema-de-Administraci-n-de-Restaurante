<?php $title = 'Registrar Nuevo Retiro'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-cash-coin"></i> Registrar Nuevo Retiro</h1>
    <a href="<?= BASE_URL ?>/financial/withdrawals" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Retiros
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-cash-coin"></i> Información del Retiro
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="withdrawalForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Monto a Retirar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="amount" name="amount" 
                                           step="0.01" min="0.01" required
                                           value="<?= isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : '' ?>"
                                           placeholder="0.00">
                                </div>
                                <div class="invalid-feedback">
                                    Por favor ingresa un monto válido.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="withdrawal_date" class="form-label">Fecha del Retiro <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="withdrawal_date" name="withdrawal_date" 
                                       value="<?= isset($_POST['withdrawal_date']) ? htmlspecialchars($_POST['withdrawal_date']) : $withdrawal_date ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor selecciona la fecha del retiro.
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
                            Selecciona la sucursal donde se realiza el retiro (opcional)
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Motivo del Retiro <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" 
                                  rows="4" required placeholder="Describe detalladamente el motivo del retiro..."><?= isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : '' ?></textarea>
                        <div class="invalid-feedback">
                            Por favor proporciona el motivo del retiro.
                        </div>
                        <div class="form-text">
                            Proporciona una descripción clara y detallada del motivo del retiro
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="evidence_file" class="form-label">Evidencia/Comprobante</label>
                        <input type="file" class="form-control" id="evidence_file" name="evidence_file" 
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <div class="form-text">
                            <strong>Recomendado:</strong> Adjunta evidencia del retiro (recibo, comprobante, etc.).<br>
                            Tipos permitidos: JPG, PNG, PDF, DOC, DOCX. Tamaño máximo: 5MB
                        </div>
                    </div>
                    
                    <!-- Información de autorización -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Información de Autorización
                        </h6>
                        <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                        <p class="mb-0">
                            Como administrador, este retiro será autorizado automáticamente.
                        </p>
                        <?php else: ?>
                        <p class="mb-0">
                            Este retiro requerirá autorización de un administrador antes de ser efectivo.
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Registrar Retiro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Información adicional -->
<div class="row justify-content-center mt-3">
    <div class="col-md-8">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-lightbulb"></i> Consejos para registrar retiros
                </h6>
                <ul class="mb-0">
                    <li>Siempre proporciona un motivo claro y detallado</li>
                    <li>Adjunta evidencia cuando sea posible (recibos, comprobantes)</li>
                    <li>Verifica que el monto sea correcto antes de enviar</li>
                    <li>Los retiros pendientes aparecerán en el dashboard hasta ser autorizados</li>
                </ul>
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

// File size validation
document.getElementById('evidence_file').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            alert('El archivo es demasiado grande. El tamaño máximo permitido es 5MB.');
            this.value = '';
        }
    }
});

// Focus on reason when amount is filled
document.getElementById('amount').addEventListener('blur', function() {
    if (this.value) {
        document.getElementById('reason').focus();
    }
});

// Confirmation before submit
document.getElementById('withdrawalForm').addEventListener('submit', function(e) {
    const amount = document.getElementById('amount').value;
    const reason = document.getElementById('reason').value;
    
    if (amount && reason) {
        const confirmation = confirm(`¿Confirmas el retiro de $${amount}?\n\nMotivo: ${reason.substring(0, 100)}${reason.length > 100 ? '...' : ''}`);
        if (!confirmation) {
            e.preventDefault();
        }
    }
});
</script>