<?php $title = 'Cortes de Caja'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-journal-check"></i> Cortes de Caja</h1>
    <div>
        <a href="<?= BASE_URL ?>/financial/createClosure" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Nuevo Corte
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
            <?php if (!empty($branches)): ?>
            <div class="col-md-3">
                <label for="branch_id" class="form-label">Sucursal</label>
                <select class="form-select" id="branch_id" name="branch_id">
                    <option value="">Todas las sucursales</option>
                    <?php foreach ($branches as $branch): ?>
                    <option value="<?= $branch['id'] ?>" <?= $selected_branch == $branch['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($branch['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="<?= BASE_URL ?>/financial/closures" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de cortes -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list"></i> Cortes Registrados
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($closures)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha/Turno</th>
                        <th>Cajero</th>
                        <?php if (!empty($branches)): ?>
                        <th>Sucursal</th>
                        <?php endif; ?>
                        <th>Efectivo Inicial</th>
                        <th>Efectivo Final</th>
                        <th>Ventas</th>
                        <th>Gastos</th>
                        <th>Retiros</th>
                        <th>Utilidad Neta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($closures as $closure): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= date('d/m/Y', strtotime($closure['shift_start'])) ?></div>
                            <small class="text-muted">
                                <?= date('H:i', strtotime($closure['shift_start'])) ?> - 
                                <?= date('H:i', strtotime($closure['shift_end'])) ?>
                            </small>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($closure['cashier_name']) ?></div>
                            <small class="text-muted">
                                Corte: <?= date('d/m/Y H:i', strtotime($closure['created_at'])) ?>
                            </small>
                        </td>
                        <?php if (!empty($branches)): ?>
                        <td><?= htmlspecialchars($closure['branch_name'] ?: 'No asignada') ?></td>
                        <?php endif; ?>
                        <td>
                            <span class="text-info">$<?= number_format($closure['initial_cash'], 2) ?></span>
                        </td>
                        <td>
                            <span class="text-primary">$<?= number_format($closure['final_cash'], 2) ?></span>
                        </td>
                        <td>
                            <span class="text-success fw-bold">$<?= number_format($closure['total_sales'], 2) ?></span>
                        </td>
                        <td>
                            <span class="text-danger">$<?= number_format($closure['total_expenses'], 2) ?></span>
                        </td>
                        <td>
                            <span class="text-warning">$<?= number_format($closure['total_withdrawals'], 2) ?></span>
                        </td>
                        <td>
                            <span class="fw-bold <?= $closure['net_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                $<?= number_format($closure['net_profit'], 2) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/financial/viewClosure/<?= $closure['id'] ?>" 
                                   class="btn btn-outline-primary" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/financial/printClosure/<?= $closure['id'] ?>" 
                                   class="btn btn-outline-success" title="Imprimir" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                                <button class="btn btn-outline-danger" 
                                        onclick="deleteClosure(<?= $closure['id'] ?>)" 
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
        
        <!-- Resumen de cortes -->
        <div class="row mt-3">
            <div class="col-md-3">
                <div class="card bg-light text-center">
                    <div class="card-body">
                        <h6 class="card-title">Total Ventas</h6>
                        <h4 class="text-success">
                            $<?php 
                            $totalSales = array_sum(array_column($closures, 'total_sales'));
                            echo number_format($totalSales, 2);
                            ?>
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light text-center">
                    <div class="card-body">
                        <h6 class="card-title">Total Gastos</h6>
                        <h4 class="text-danger">
                            $<?php 
                            $totalExpenses = array_sum(array_column($closures, 'total_expenses'));
                            echo number_format($totalExpenses, 2);
                            ?>
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light text-center">
                    <div class="card-body">
                        <h6 class="card-title">Total Retiros</h6>
                        <h4 class="text-warning">
                            $<?php 
                            $totalWithdrawals = array_sum(array_column($closures, 'total_withdrawals'));
                            echo number_format($totalWithdrawals, 2);
                            ?>
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light text-center">
                    <div class="card-body">
                        <h6 class="card-title">Utilidad Neta Total</h6>
                        <h4 class="<?= ($totalSales - $totalExpenses - $totalWithdrawals) >= 0 ? 'text-success' : 'text-danger' ?>">
                            $<?= number_format($totalSales - $totalExpenses - $totalWithdrawals, 2) ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Resumen del Período</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Número de Cortes:</strong> <?= count($closures) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Promedio de Utilidad por Corte:</strong> 
                                $<?= count($closures) > 0 ? number_format(($totalSales - $totalExpenses - $totalWithdrawals) / count($closures), 2) : '0.00' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-journal-check display-1 text-muted"></i>
            <h3 class="mt-3">No hay cortes de caja registrados</h3>
            <p class="text-muted">No se encontraron cortes con los filtros aplicados</p>
            <a href="<?= BASE_URL ?>/financial/createClosure" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Realizar Primer Corte
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
                ¿Estás seguro de que deseas eliminar este corte de caja? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
let closureToDelete = null;

function deleteClosure(id) {
    closureToDelete = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (closureToDelete) {
        window.location.href = '<?= BASE_URL ?>/financial/deleteClosure/' + closureToDelete;
    }
});
</script>