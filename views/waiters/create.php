<?php $title = 'Crear Mesero'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-plus-fill"></i> Crear Mesero</h1>
    <a href="<?= BASE_URL ?>/waiters" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información del Mesero</h5>
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
                                <label for="name" class="form-label">
                                    <i class="bi bi-person"></i> Nombre Completo *
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                    id="name" 
                                    name="name" 
                                    value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                                    placeholder="Ej: Juan Pérez García"
                                    required
                                >
                                <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['name']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Correo Electrónico *
                                </label>
                                <input 
                                    type="email" 
                                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                    id="email" 
                                    name="email" 
                                    value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                                    placeholder="mesero@restaurante.com"
                                    required
                                >
                                <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['email']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_code" class="form-label">
                                    <i class="bi bi-badge-tm"></i> Código de Empleado *
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control <?= isset($errors['employee_code']) ? 'is-invalid' : '' ?>" 
                                    id="employee_code" 
                                    name="employee_code" 
                                    value="<?= htmlspecialchars($old['employee_code'] ?? '') ?>"
                                    placeholder="Ej: MES001, MES002..."
                                    required
                                    maxlength="20"
                                >
                                <?php if (isset($errors['employee_code'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['employee_code']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Código único para identificar al mesero (máximo 20 caracteres)
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    <i class="bi bi-telephone"></i> Teléfono
                                </label>
                                <input 
                                    type="tel" 
                                    class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                    id="phone" 
                                    name="phone" 
                                    value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                                    placeholder="Ej: +1234567890"
                                >
                                <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['phone']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Teléfono de contacto (opcional)
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <h6 class="mb-3">Credenciales de Acceso</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i> Contraseña *
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
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <i class="bi bi-lock-fill"></i> Confirmar Contraseña *
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    placeholder="Repetir contraseña"
                                    required
                                    minlength="6"
                                >
                                <?php if (isset($errors['password_confirmation'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['password_confirmation']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Nota:</strong> Se creará automáticamente un usuario con rol de "Mesero" 
                        para que pueda acceder al sistema.
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/waiters" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Mesero
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

// Employee code formatting
document.getElementById('employee_code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>