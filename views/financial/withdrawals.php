<?php $title = 'Gestión de Retiros'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-cash-coin"></i> Gestión de Retiros</h1>
    <div>
        <a href="<?= BASE_URL ?>/financial/createWithdrawal" class="btn btn-warning">
            <i class="bi bi-plus-circle"></i> Nuevo Retiro
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
                <a href="<?= BASE_URL ?>/financial/withdrawals" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de retiros -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list"></i> Retiros Registrados
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($withdrawals)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Responsable</th>
                        <th>Motivo</th>
                        <?php if (!empty($branches)): ?>
                        <th>Sucursal</th>
                        <?php endif; ?>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Evidencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($withdrawals as $withdrawal): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($withdrawal['withdrawal_date'])) ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($withdrawal['responsible_name']) ?></div>
                            <small class="text-muted">
                                Registrado: <?= date('d/m/Y H:i', strtotime($withdrawal['created_at'])) ?>
                            </small>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($withdrawal['reason']) ?>">
                                <?= htmlspecialchars($withdrawal['reason']) ?>
                            </div>
                        </td>
                        <?php if (!empty($branches)): ?>
                        <td><?= htmlspecialchars($withdrawal['branch_name'] ?: 'No asignada') ?></td>
                        <?php endif; ?>
                        <td>
                            <span class="fw-bold text-warning">$<?= number_format($withdrawal['amount'], 2) ?></span>
                        </td>
                        <td>
                            <?php if ($withdrawal['authorized_by_user_id']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Autorizado
                                </span>
                                <br>
                                <small class="text-muted">Por: <?= htmlspecialchars($withdrawal['authorized_by_name']) ?></small>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> Pendiente
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($withdrawal['evidence_file']): ?>
                            <a href="<?= BASE_URL ?>/financial/downloadEvidence/<?= $withdrawal['evidence_file'] ?>" 
                               class="btn btn-sm btn-outline-info" title="Descargar evidencia">
                                <i class="bi bi-download"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">Sin evidencia</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/financial/viewWithdrawal/<?= $withdrawal['id'] ?>" 
                                   class="btn btn-outline-primary" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($_SESSION['user_role'] === ROLE_ADMIN && !$withdrawal['authorized_by_user_id']): ?>
                                <button class="btn btn-outline-success" 
                                        onclick="authorizeWithdrawal(<?= $withdrawal['id'] ?>)" 
                                        title="Autorizar">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                                <button class="btn btn-outline-danger" 
                                        onclick="deleteWithdrawal(<?= $withdrawal['id'] ?>)" 
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
                        <h5 class="card-title">Total de Retiros</h5>
                        <h3 class="text-warning">
                            $<?php 
                            $total = array_sum(array_column($withdrawals, 'amount'));
                            echo number_format($total, 2);
                            ?>
                        </h3>
                        <small class="text-muted"><?= count($withdrawals) ?> retiros registrados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title">Estado de Autorizaciones</h5>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-success">
                                    <strong><?php 
                                    $authorized = array_filter($withdrawals, function($w) { return $w['authorized_by_user_id']; });
                                    echo count($authorized);
                                    ?></strong>
                                    <br><small>Autorizados</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-warning">
                                    <strong><?php 
                                    $pending = array_filter($withdrawals, function($w) { return !$w['authorized_by_user_id']; });
                                    echo count($pending);
                                    ?></strong>
                                    <br><small>Pendientes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-cash-coin display-1 text-muted"></i>
            <h3 class="mt-3">No hay retiros registrados</h3>
            <p class="text-muted">No se encontraron retiros con los filtros aplicados</p>
            <a href="<?= BASE_URL ?>/financial/createWithdrawal" class="btn btn-warning">
                <i class="bi bi-plus-circle"></i> Registrar Primer Retiro
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

<!-- Modal de confirmación para autorizar -->
<div class="modal fade" id="authorizeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Autorizar Retiro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas autorizar este retiro?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmAuthorize">Autorizar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar este retiro? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
let withdrawalToAuthorize = null;
let withdrawalToDelete = null;

function authorizeWithdrawal(id) {
    withdrawalToAuthorize = id;
    const modal = new bootstrap.Modal(document.getElementById('authorizeModal'));
    modal.show();
}

function deleteWithdrawal(id) {
    withdrawalToDelete = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmAuthorize').addEventListener('click', function() {
    if (withdrawalToAuthorize) {
        window.location.href = '<?= BASE_URL ?>/financial/authorizeWithdrawal/' + withdrawalToAuthorize;
    }
});

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (withdrawalToDelete) {
        window.location.href = '<?= BASE_URL ?>/financial/deleteWithdrawal/' + withdrawalToDelete;
    }
});
</script>