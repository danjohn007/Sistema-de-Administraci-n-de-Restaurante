<?php $title = 'Asignar Mesas'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-grid-3x3-gap"></i> Asignar Mesas a Mesero</h1>
    <a href="<?= BASE_URL ?>/waiters" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-badge"></i> 
                    <?= htmlspecialchars($waiter['name']) ?>
                    <span class="badge bg-primary ms-2"><?= htmlspecialchars($waiter['employee_code']) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">
                                <i class="bi bi-table"></i> Mesas Disponibles
                                <span class="badge bg-success"><?= count($available_tables) ?></span>
                            </h6>
                            
                            <?php if (empty($available_tables)): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    No hay mesas disponibles para asignar
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th width="50">
                                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                                </th>
                                                <th>Mesa #</th>
                                                <th>Capacidad</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($available_tables as $table): ?>
                                            <tr>
                                                <td>
                                                    <input 
                                                        type="checkbox" 
                                                        class="form-check-input table-checkbox" 
                                                        name="table_ids[]" 
                                                        value="<?= $table['id'] ?>"
                                                        id="table_<?= $table['id'] ?>"
                                                    >
                                                </td>
                                                <td>
                                                    <label for="table_<?= $table['id'] ?>" class="form-label mb-0">
                                                        <strong>Mesa <?= $table['number'] ?></strong>
                                                    </label>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?= $table['capacity'] ?> personas
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">Disponible</span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="mb-3">
                                <i class="bi bi-check-circle"></i> Mesas Actualmente Asignadas
                                <span class="badge bg-primary"><?= count($assigned_tables) ?></span>
                            </h6>
                            
                            <?php if (empty($assigned_tables)): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Este mesero no tiene mesas asignadas
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Mesa #</th>
                                                <th>Capacidad</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assigned_tables as $table): ?>
                                            <tr>
                                                <td>
                                                    <strong>Mesa <?= $table['number'] ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?= $table['capacity'] ?> personas
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = '';
                                                    $statusText = '';
                                                    switch($table['status']) {
                                                        case TABLE_AVAILABLE:
                                                            $statusClass = 'bg-success';
                                                            $statusText = 'Disponible';
                                                            break;
                                                        case TABLE_OCCUPIED:
                                                            $statusClass = 'bg-warning';
                                                            $statusText = 'Ocupada';
                                                            break;
                                                        case TABLE_BILL_REQUESTED:
                                                            $statusClass = 'bg-info';
                                                            $statusText = 'Cuenta Solicitada';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-secondary';
                                                            $statusText = ucfirst($table['status']);
                                                    }
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= $statusText ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>¡Atención!</strong> Al guardar los cambios, se desasignarán todas las mesas 
                        actualmente asignadas y se asignarán solo las mesas seleccionadas.
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/waiters" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Guardar Asignación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    var checkboxes = document.querySelectorAll('.table-checkbox');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = this.checked;
    }, this);
});

// Update select all when individual checkboxes change
document.querySelectorAll('.table-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        var allCheckboxes = document.querySelectorAll('.table-checkbox');
        var checkedCheckboxes = document.querySelectorAll('.table-checkbox:checked');
        var selectAllCheckbox = document.getElementById('selectAll');
        
        if (checkedCheckboxes.length === allCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCheckboxes.length > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    });
});

// Initialize select all state
(function() {
    var allCheckboxes = document.querySelectorAll('.table-checkbox');
    var checkedCheckboxes = document.querySelectorAll('.table-checkbox:checked');
    var selectAllCheckbox = document.getElementById('selectAll');
    
    if (checkedCheckboxes.length === allCheckboxes.length && allCheckboxes.length > 0) {
        selectAllCheckbox.checked = true;
    } else if (checkedCheckboxes.length > 0) {
        selectAllCheckbox.indeterminate = true;
    }
})();
</script>