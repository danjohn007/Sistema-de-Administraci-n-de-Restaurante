<?php $title = 'Dashboard Financiero'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-graph-up"></i> Dashboard Financiero</h1>
    <div class="btn-group" role="group">
        <a href="<?= BASE_URL ?>/financial/expenses" class="btn btn-outline-primary">
            <i class="bi bi-credit-card"></i> Gastos
        </a>
        <a href="<?= BASE_URL ?>/financial/withdrawals" class="btn btn-outline-warning">
            <i class="bi bi-cash-coin"></i> Retiros
        </a>
        <a href="<?= BASE_URL ?>/financial/closures" class="btn btn-outline-success">
            <i class="bi bi-journal-check"></i> Corte de Caja
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
                <a href="<?= BASE_URL ?>/financial" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Estadísticas por categoría -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Gastos por Categoría
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($total_expenses)): ?>
                    <?php foreach ($total_expenses as $expense): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge" style="background-color: <?= htmlspecialchars($expense['category_color']) ?>">
                                <?= htmlspecialchars($expense['category_name']) ?>
                            </span>
                            <small class="text-muted">(<?= $expense['expense_count'] ?> gastos)</small>
                        </div>
                        <strong>$<?= number_format($expense['total_amount'], 2) ?></strong>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">No hay gastos registrados en el período seleccionado</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i> Actividad Reciente
                </h5>
            </div>
            <div class="card-body">
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-expenses-tab" data-bs-toggle="tab" data-bs-target="#nav-expenses" type="button" role="tab">
                        Gastos
                    </button>
                    <button class="nav-link" id="nav-withdrawals-tab" data-bs-toggle="tab" data-bs-target="#nav-withdrawals" type="button" role="tab">
                        Retiros
                    </button>
                    <button class="nav-link" id="nav-closures-tab" data-bs-toggle="tab" data-bs-target="#nav-closures" type="button" role="tab">
                        Cortes
                    </button>
                </div>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-expenses" role="tabpanel">
                        <?php if (!empty($recent_expenses)): ?>
                            <?php foreach ($recent_expenses as $expense): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($expense['description']) ?></div>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($expense['category_name']) ?> - 
                                        <?= date('d/m/Y', strtotime($expense['expense_date'])) ?>
                                    </small>
                                </div>
                                <strong class="text-danger">-$<?= number_format($expense['amount'], 2) ?></strong>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No hay gastos recientes</p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="nav-withdrawals" role="tabpanel">
                        <?php if (!empty($recent_withdrawals)): ?>
                            <?php foreach ($recent_withdrawals as $withdrawal): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($withdrawal['reason']) ?></div>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($withdrawal['responsible_name']) ?> - 
                                        <?= date('d/m/Y H:i', strtotime($withdrawal['withdrawal_date'])) ?>
                                    </small>
                                </div>
                                <strong class="text-warning">-$<?= number_format($withdrawal['amount'], 2) ?></strong>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No hay retiros recientes</p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="nav-closures" role="tabpanel">
                        <?php if (!empty($recent_closures)): ?>
                            <?php foreach ($recent_closures as $closure): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <div class="fw-bold">Corte de Caja</div>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($closure['cashier_name']) ?> - 
                                        <?= date('d/m/Y H:i', strtotime($closure['shift_start'])) ?>
                                    </small>
                                </div>
                                <strong class="<?= $closure['net_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    $<?= number_format($closure['net_profit'], 2) ?>
                                </strong>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No hay cortes recientes</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="row">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-plus-circle display-4 text-danger mb-3"></i>
                <h5 class="card-title">Registrar Gasto</h5>
                <p class="card-text">Registra un nuevo gasto del negocio</p>
                <a href="<?= BASE_URL ?>/financial/createExpense" class="btn btn-danger">
                    <i class="bi bi-plus"></i> Nuevo Gasto
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-cash-coin display-4 text-warning mb-3"></i>
                <h5 class="card-title">Registrar Retiro</h5>
                <p class="card-text">Registra un retiro de dinero de caja</p>
                <a href="<?= BASE_URL ?>/financial/createWithdrawal" class="btn btn-warning">
                    <i class="bi bi-plus"></i> Nuevo Retiro
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-journal-check display-4 text-success mb-3"></i>
                <h5 class="card-title">Corte de Caja</h5>
                <p class="card-text">Realiza el corte de caja del turno</p>
                <a href="<?= BASE_URL ?>/financial/createClosure" class="btn btn-success">
                    <i class="bi bi-plus"></i> Nuevo Corte
                </a>
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