<?php $title = 'Cambiar Contraseña'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-key"></i> Cambiar Contraseña</h1>
    <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Dashboard
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-lock"></i> Cambiar Contraseña
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/auth/changePassword" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual *</label>
                        <input type="password" 
                               class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                               id="current_password" 
                               name="current_password" 
                               required>
                        <?php if (isset($errors['current_password'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['current_password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña *</label>
                        <input type="password" 
                               class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                               id="new_password" 
                               name="new_password" 
                               minlength="6"
                               required>
                        <small class="text-muted">La contraseña debe tener al menos 6 caracteres.</small>
                        <?php if (isset($errors['new_password'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['new_password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña *</label>
                        <input type="password" 
                               class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['confirm_password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary me-md-2">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
        
        // Password confirmation validation
        var password = document.getElementById('new_password');
        var confirmation = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmation.value) {
                confirmation.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmation.setCustomValidity('');
            }
        }
        
        password.onchange = validatePassword;
        confirmation.onkeyup = validatePassword;
    }, false);
})();
</script>