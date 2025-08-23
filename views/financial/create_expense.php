<?php $title = 'Registrar Nuevo Gasto'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-circle"></i> Registrar Nuevo Gasto</h1>
    <a href="<?= BASE_URL ?>/financial/expenses" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Gastos
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-credit-card"></i> Información del Gasto
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="expenseForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Seleccionar categoría</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            data-color="<?= htmlspecialchars($category['color']) ?>"
                                            <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor selecciona una categoría.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Monto <span class="text-danger">*</span></label>
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
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_date" class="form-label">Fecha del Gasto <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="expense_date" name="expense_date" 
                                       value="<?= isset($_POST['expense_date']) ? htmlspecialchars($_POST['expense_date']) : $expense_date ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor selecciona la fecha del gasto.
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($branches)): ?>
                        <div class="col-md-6">
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
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="3" required placeholder="Describe el gasto realizado..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                        <div class="invalid-feedback">
                            Por favor proporciona una descripción del gasto.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="receipt_file" class="form-label">Comprobante/Evidencia</label>
                        <input type="file" class="form-control" id="receipt_file" name="receipt_file" 
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <div class="form-text">
                            Tipos permitidos: JPG, PNG, PDF, DOC, DOCX. Tamaño máximo: 5MB
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Registrar Gasto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Preview de categoría seleccionada -->
<div class="row justify-content-center mt-3">
    <div class="col-md-8">
        <div class="card d-none" id="categoryPreview">
            <div class="card-body text-center">
                <span class="badge fs-6" id="categoryBadge">Categoría Seleccionada</span>
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

// Category preview
document.getElementById('category_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const preview = document.getElementById('categoryPreview');
    const badge = document.getElementById('categoryBadge');
    
    if (selectedOption.value) {
        const color = selectedOption.getAttribute('data-color');
        const name = selectedOption.text;
        
        badge.style.backgroundColor = color;
        badge.textContent = name;
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});

// File size validation
document.getElementById('receipt_file').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            alert('El archivo es demasiado grande. El tamaño máximo permitido es 5MB.');
            this.value = '';
        }
    }
});

// Auto-focus on amount when category is selected
document.getElementById('category_id').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('amount').focus();
    }
});
</script>