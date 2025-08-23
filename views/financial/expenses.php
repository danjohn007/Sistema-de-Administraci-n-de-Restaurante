<?php $title = 'Gestión de Gastos'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-credit-card"></i> Gestión de Gastos</h1>
    <div>
        <a href="<?= BASE_URL ?>/financial/createExpense" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Gasto
        </a>
        <a href="<?= BASE_URL ?>/financial" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="date_from" class="form-label">Fecha Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Fecha Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
            </div>
            <div class="col-md-2">
                <label for="category_id" class="form-label">Categoría</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Todas</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= $selected_category == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($branches)): ?>
            <div class="col-md-2">
                <label for="branch_id" class="form-label">Sucursal</label>
                <select class="form-select" id="branch_id" name="branch_id">
                    <option value="">Todas</option>
                    <?php foreach ($branches as $branch): ?>
                    <option value="<?= $branch['id'] ?>" <?= $selected_branch == $branch['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($branch['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="<?= BASE_URL ?>/financial/expenses" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de gastos -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list"></i> Gastos Registrados
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($expenses)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <?php if (!empty($branches)): ?>
                        <th>Sucursal</th>
                        <?php endif; ?>
                        <th>Usuario</th>
                        <th>Monto</th>
                        <th>Evidencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($expense['expense_date'])) ?></td>
                        <td>
                            <span class="badge" style="background-color: <?= htmlspecialchars($expense['category_color']) ?>">
                                <?= htmlspecialchars($expense['category_name']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($expense['description']) ?></div>
                            <small class="text-muted">
                                Registrado: <?= date('d/m/Y H:i', strtotime($expense['created_at'])) ?>
                            </small>
                        </td>
                        <?php if (!empty($branches)): ?>
                        <td><?= htmlspecialchars($expense['branch_name'] ?: 'No asignada') ?></td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars($expense['user_name']) ?></td>
                        <td>
                            <span class="fw-bold text-danger">$<?= number_format($expense['amount'], 2) ?></span>
                        </td>
                        <td>
                            <?php if ($expense['receipt_file']): ?>
                            <a href="<?= BASE_URL ?>/financial/downloadEvidence/<?= $expense['receipt_file'] ?>" 
                               class="btn btn-sm btn-outline-info" title="Descargar evidencia">
                                <i class="bi bi-download"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">Sin evidencia</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/financial/viewExpense/<?= $expense['id'] ?>" 
                                   class="btn btn-outline-primary" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                                <button class="btn btn-outline-danger" 
                                        onclick="deleteExpense(<?= $expense['id'] ?>)" 
                                        title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Resumen -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total de Gastos</h5>
                        <h3 class="text-danger">
                            $<?php 
                            $total = array_sum(array_column($expenses, 'amount'));
                            echo number_format($total, 2);
                            ?>
                        </h3>
                        <small class="text-muted"><?= count($expenses) ?> gastos registrados</small>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h3 class="mt-3">No hay gastos registrados</h3>
            <p class="text-muted">No se encontraron gastos con los filtros aplicados</p>
            <a href="<?= BASE_URL ?>/financial/createExpense" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Registrar Primer Gasto
            </a>
        </div>
        <?php endif; ?>
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

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar este gasto? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
let expenseToDelete = null;

function deleteExpense(id) {
    expenseToDelete = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (expenseToDelete) {
        window.location.href = '<?= BASE_URL ?>/financial/deleteExpense/' + expenseToDelete;
    }
});
</script>