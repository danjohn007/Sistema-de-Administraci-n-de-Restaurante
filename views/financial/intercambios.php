<?php $title = 'Listado de Intercambios'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-arrow-left-right"></i> Listado de Intercambios</h1>
    <div class="btn-group">
        <a href="<?= BASE_URL ?>/financial" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="date_from" class="form-label">Fecha Desde</label>
                <input type="date" 
                       class="form-control" 
                       id="date_from" 
                       name="date_from" 
                       value="<?= htmlspecialchars($date_from) ?>">
            </div>
            <div class="col-md-4">
                <label for="date_to" class="form-label">Fecha Hasta</label>
                <input type="date" 
                       class="form-control" 
                       id="date_to" 
                       name="date_to" 
                       value="<?= htmlspecialchars($date_to) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (empty($tickets)): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    No se encontraron intercambios en el período seleccionado.
</div>
<?php else: ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-arrow-left-right"></i> Intercambios Realizados
            <span class="badge bg-warning"><?= count($tickets) ?></span>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Mesa</th>
                        <th>Mesero</th>
                        <th>Cajero</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($ticket['ticket_number']) ?></strong>
                        </td>
                        <td>
                            <?php if ($ticket['table_number']): ?>
                                <span class="badge bg-info">Mesa <?= $ticket['table_number'] ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ticket['waiter_name']): ?>
                                <?= htmlspecialchars($ticket['waiter_name']) ?>
                                <small class="text-muted">(<?= htmlspecialchars($ticket['employee_code']) ?>)</small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($ticket['cashier_name']) ?></td>
                        <td>
                            <strong class="text-warning">$<?= number_format($ticket['total'], 2) ?></strong>
                        </td>
                        <td>
                            <small>
                                <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
                            </small>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/tickets/show/<?= $ticket['id'] ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Total de intercambios:</strong> <?= count($tickets) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-warning">
                        <strong>Monto total en intercambios:</strong> 
                        $<?= number_format(array_sum(array_column($tickets, 'total')), 2) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>