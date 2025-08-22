<?php $title = 'Iniciar Sesión'; ?>

<div class="login-container d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shop text-primary" style="font-size: 3rem;"></i>
                            <h3 class="mt-2"><?= APP_NAME ?></h3>
                            <p class="text-muted">Ingresa tus credenciales para continuar</p>
                        </div>
                        
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?= BASE_URL ?>/auth/login" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Correo Electrónico
                                </label>
                                <input 
                                    type="email" 
                                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                    id="email" 
                                    name="email" 
                                    value="<?= htmlspecialchars($old_email ?? '') ?>"
                                    placeholder="usuario@ejemplo.com"
                                    required
                                >
                                <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['email']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i> Contraseña
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Tu contraseña"
                                    required
                                >
                                <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['password']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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
</script>