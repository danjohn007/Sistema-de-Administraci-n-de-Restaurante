<?php $title = 'Crear Usuario'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-plus"></i> Crear Usuario</h1>
    <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información del Usuario</h5>
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
                                    placeholder="usuario@ejemplo.com"
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

                    <div class="mb-3">
                        <label for="role" class="form-label">
                            <i class="bi bi-shield-check"></i> Rol *
                        </label>
                        <select 
                            class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" 
                            id="role" 
                            name="role"
                            required
                        >
                            <option value="">Seleccionar rol...</option>
                            <?php foreach ($roles as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($old['role'] ?? '') === $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['role']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text">
                            <small>
                                <strong>Administrador:</strong> Acceso total al sistema<br>
                                <strong>Mesero:</strong> Gestión de pedidos y mesas<br>
                                <strong>Cajero:</strong> Gestión de tickets y cobros
                            </small>
                        </div>
                    </div>

                    <hr class="my-4">

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

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/users" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Usuario
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