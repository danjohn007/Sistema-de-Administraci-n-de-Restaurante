<?php $title = 'Cambiar Contraseña'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-key"></i> Cambiar Contraseña</h1>
    <a href="<?= BASE_URL ?>/profile" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Perfil
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
                <form method="POST" action="<?= BASE_URL ?>/profile/changePassword" class="needs-validation" novalidate>
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
                        <label for="password" class="form-label">Nueva Contraseña *</label>
                        <input type="password" 
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               id="password" 
                               name="password" 
                               minlength="6"
                               required>
                        <small class="text-muted">La contraseña debe tener al menos 6 caracteres.</small>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña *</label>
                        <input type="password" 
                               class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required>
                        <?php if (isset($errors['password_confirmation'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['password_confirmation']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/profile" class="btn btn-secondary me-md-2">
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
        var password = document.getElementById('password');
        var confirmation = document.getElementById('password_confirmation');
        
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