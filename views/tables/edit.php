<?php $title = 'Editar Mesa'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil-square"></i> Editar Mesa</h1>
    <a href="<?= BASE_URL ?>/tables" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Editando: Mesa <?= $table['number'] ?>
                    <span class="badge bg-<?= getStatusColor($table['status']) ?> ms-2">
                        <?= getStatusLabel($table['status']) ?>
                    </span>
                </h5>
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
                                <label for="number" class="form-label">
                                    <i class="bi bi-hash"></i> Número de Mesa *
                                </label>
                                <input 
                                    type="number" 
                                    class="form-control <?= isset($errors['number']) ? 'is-invalid' : '' ?>" 
                                    id="number" 
                                    name="number" 
                                    value="<?= htmlspecialchars($old['number'] ?? $table['number']) ?>"
                                    placeholder="Ej: 1, 2, 3..."
                                    required
                                    min="1"
                                    max="999"
                                >
                                <?php if (isset($errors['number'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['number']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Número único para identificar la mesa
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="capacity" class="form-label">
                                    <i class="bi bi-people"></i> Capacidad *
                                </label>
                                <input 
                                    type="number" 
                                    class="form-control <?= isset($errors['capacity']) ? 'is-invalid' : '' ?>" 
                                    id="capacity" 
                                    name="capacity" 
                                    value="<?= htmlspecialchars($old['capacity'] ?? $table['capacity']) ?>"
                                    placeholder="Número de personas"
                                    required
                                    min="1"
                                    max="20"
                                >
                                <?php if (isset($errors['capacity'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['capacity']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Número máximo de comensales (1-20)
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="waiter_id" class="form-label">
                            <i class="bi bi-person-badge"></i> Mesero Asignado
                        </label>
                        <select 
                            class="form-select <?= isset($errors['waiter_id']) ? 'is-invalid' : '' ?>" 
                            id="waiter_id" 
                            name="waiter_id"
                        >
                            <option value="">Sin asignar</option>
                            <?php foreach ($waiters as $waiter): ?>
                                <option value="<?= $waiter['id'] ?>" 
                                    <?= ($old['waiter_id'] ?? $table['waiter_id']) == $waiter['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($waiter['name']) ?> (<?= htmlspecialchars($waiter['employee_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['waiter_id'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['waiter_id']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Para cambiar el estado de la mesa, usa la opción "Cambiar Estado"
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Nota:</strong> Esta función solo permite editar la información básica de la mesa.
                        Para cambiar el estado (disponible, ocupada, etc.), utiliza la opción "Cambiar Estado" 
                        desde la lista de mesas.
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Información adicional</label>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Estado actual:</strong> 
                                    <span class="badge bg-<?= getStatusColor($table['status']) ?>">
                                        <?= getStatusLabel($table['status']) ?>
                                    </span>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Creada:</strong> <?= date('d/m/Y H:i', strtotime($table['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/tables" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <a href="<?= BASE_URL ?>/tables/changeStatus/<?= $table['id'] ?>" class="btn btn-outline-info">
                            <i class="bi bi-arrow-repeat"></i> Cambiar Estado
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Actualizar Mesa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
function getStatusColor($status) {
    switch($status) {
        case TABLE_AVAILABLE:
            return 'success';
        case TABLE_OCCUPIED:
            return 'warning';
        case TABLE_BILL_REQUESTED:
            return 'info';
        case 'cerrada':
            return 'secondary';
        default:
            return 'secondary';
    }
}

function getStatusLabel($status) {
    switch($status) {
        case TABLE_AVAILABLE:
            return 'Disponible';
        case TABLE_OCCUPIED:
            return 'Ocupada';
        case TABLE_BILL_REQUESTED:
            return 'Cuenta Solicitada';
        case 'cerrada':
            return 'Cerrada';
        default:
            return ucfirst($status);
    }
}
?>

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