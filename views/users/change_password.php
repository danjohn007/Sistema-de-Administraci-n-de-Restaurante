<?php $title = 'Cambiar Contraseña'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-key"></i> Cambiar Contraseña</h1>
    <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person"></i>
                    <?= htmlspecialchars($user['name']) ?>
                    <span class="badge bg-secondary ms-2"><?= htmlspecialchars($user['email']) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>¡Atención!</strong> Cambiarás la contraseña del usuario seleccionado. 
                    Asegúrate de informar al usuario sobre su nueva contraseña.
                </div>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> Nueva Contraseña *
                        </label>
                        <input 
                            type="password" 
                            class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                            id="password" 
                            name="password" 
                            placeholder="Mínimo 6 caracteres"
                            required
                            minlength="6"
                        >
                        <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['password']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text">
                            La contraseña debe tener al menos 6 caracteres.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">
                            <i class="bi bi-lock-fill"></i> Confirmar Nueva Contraseña *
                        </label>
                        <input 
                            type="password" 
                            class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="Repetir nueva contraseña"
                            required
                            minlength="6"
                        >
                        <?php if (isset($errors['password_confirmation'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['password_confirmation']) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmChange" required>
                            <label class="form-check-label" for="confirmChange">
                                Confirmo que deseo cambiar la contraseña de este usuario
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/users" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i> Cambiar Contraseña
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

// Password confirmation validation
document.getElementById('password_confirmation').addEventListener('input', function() {
    var password = document.getElementById('password').value;
    var confirmation = this.value;
    
    if (password !== confirmation) {
        this.setCustomValidity('Las contraseñas no coinciden');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('password').addEventListener('input', function() {
    var confirmation = document.getElementById('password_confirmation');
    if (confirmation.value) {
        var event = new Event('input');
        confirmation.dispatchEvent(event);
    }
});
</script>