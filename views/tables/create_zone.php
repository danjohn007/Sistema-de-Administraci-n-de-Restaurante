<?php $title = 'Crear Zona'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-square"></i> Crear Zona</h1>
    <a href="<?= BASE_URL ?>/tables/zones" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información de la Zona</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="bi bi-geo-alt"></i> Nombre de la Zona *
                        </label>
                        <input 
                            type="text" 
                            class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                            id="name" 
                            name="name" 
                            value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                            placeholder="Ej: Salón, Terraza, Alberca..."
                            required
                            maxlength="50"
                        >
                        <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['name']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Nombre único para identificar la zona (máximo 50 caracteres)
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="bi bi-card-text"></i> Descripción
                        </label>
                        <textarea 
                            class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
                            id="description" 
                            name="description" 
                            rows="3"
                            placeholder="Descripción opcional de la zona..."
                        ><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['description']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Descripción detallada de la zona (opcional)
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="color" class="form-label">
                            <i class="bi bi-palette"></i> Color Identificativo
                        </label>
                        <div class="input-group">
                            <input 
                                type="color" 
                                class="form-control form-control-color <?= isset($errors['color']) ? 'is-invalid' : '' ?>" 
                                id="color" 
                                name="color" 
                                value="<?= htmlspecialchars($old['color'] ?? '#007bff') ?>"
                                style="max-width: 60px;"
                            >
                            <input 
                                type="text" 
                                class="form-control" 
                                id="colorHex" 
                                value="<?= htmlspecialchars($old['color'] ?? '#007bff') ?>"
                                readonly
                            >
                        </div>
                        <?php if (isset($errors['color'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['color']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Color que se usará para identificar visualmente la zona
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/tables/zones" class="btn btn-secondary me-md-2">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Zona
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('color');
    const colorHex = document.getElementById('colorHex');
    
    colorInput.addEventListener('input', function() {
        colorHex.value = this.value;
    });
    
    colorHex.addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            colorInput.value = this.value;
        }
    });
});
</script>