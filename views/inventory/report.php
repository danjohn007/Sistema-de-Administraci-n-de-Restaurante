<?php $title = 'Reportes de Inventario'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-graph-up"></i> Reportes de Inventario</h1>
    <a href="<?= BASE_URL ?>/inventory" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Inventario
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-funnel"></i> Período del Reporte
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/inventory/report">
            <div class="row">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Generar Reporte
                    </button>
                    <a href="<?= BASE_URL ?>/inventory/report" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Resetear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen General -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Valor Total Inventario</h6>
                        <h3 class="mb-0">$<?= number_format($inventoryValue, 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Productos con Stock Bajo</h6>
                        <h3 class="mb-0"><?= count($lowStockProducts) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-arrow-up-down" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Movimientos del Período</h6>
                        <h3 class="mb-0"><?= count($report['movements'] ?? []) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alertas de Stock Bajo -->
<?php if (!empty($lowStockProducts)): ?>
<div class="alert alert-warning mb-4">
    <h6><i class="bi bi-exclamation-triangle"></i> Productos con Stock Bajo</h6>
    <div class="row">
        <?php foreach ($lowStockProducts as $product): ?>
        <div class="col-md-6 mb-2">
            <strong><?= htmlspecialchars($product['name']) ?></strong>: 
            <?= number_format($product['current_stock'], 3) ?> <?= htmlspecialchars($product['unit_measure']) ?>
            <span class="text-muted">(Mínimo: <?= number_format($product['min_stock'], 3) ?>)</span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Reporte de Movimientos por Producto -->
<?php if (!empty($report['by_product'])): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-bar-chart"></i> Movimientos por Producto
            <small class="text-muted">(<?= date('d/m/Y', strtotime($date_from)) ?> - <?= date('d/m/Y', strtotime($date_to)) ?>)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Entradas</th>
                        <th>Salidas</th>
                        <th>Movimiento Neto</th>
                        <th>Costo Entradas</th>
                        <th>Valor Salidas</th>
                        <th>Stock Actual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['by_product'] as $productReport): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($productReport['product_name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($productReport['category'] ?? 'Sin categoría') ?></small>
                        </td>
                        <td>
                            <span class="badge bg-success">
                                <?= number_format($productReport['total_in'], 3) ?> <?= htmlspecialchars($productReport['unit_measure']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-danger">
                                <?= number_format($productReport['total_out'], 3) ?> <?= htmlspecialchars($productReport['unit_measure']) ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $netMovement = $productReport['total_in'] - $productReport['total_out'];
                            $badgeClass = $netMovement >= 0 ? 'bg-success' : 'bg-warning';
                            ?>
                            <span class="badge <?= $badgeClass ?>">
                                <?= $netMovement >= 0 ? '+' : '' ?><?= number_format($netMovement, 3) ?> <?= htmlspecialchars($productReport['unit_measure']) ?>
                            </span>
                        </td>
                        <td>$<?= number_format($productReport['cost_in'], 2) ?></td>
                        <td>$<?= number_format($productReport['cost_out'], 2) ?></td>
                        <td>
                            <strong><?= number_format($productReport['current_stock'], 3) ?></strong> <?= htmlspecialchars($productReport['unit_measure']) ?>
                            <br><small class="text-muted">Valor: $<?= number_format($productReport['current_stock'] * $productReport['cost_per_unit'], 2) ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Resumen por Tipo de Movimiento -->
<?php if (!empty($report['summary'])): ?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-arrow-down"></i> Total Entradas
                </h6>
            </div>
            <div class="card-body">
                <h4 class="text-success mb-0"><?= number_format($report['summary']['total_in'] ?? 0, 3) ?> unidades</h4>
                <p class="text-muted mb-0">Costo Total: $<?= number_format($report['summary']['cost_in'] ?? 0, 2) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-arrow-up"></i> Total Salidas
                </h6>
            </div>
            <div class="card-body">
                <h4 class="text-danger mb-0"><?= number_format($report['summary']['total_out'] ?? 0, 3) ?> unidades</h4>
                <p class="text-muted mb-0">Valor Total: $<?= number_format($report['summary']['cost_out'] ?? 0, 2) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-calculator"></i> Balance
                </h6>
            </div>
            <div class="card-body">
                <?php 
                $netQuantity = ($report['summary']['total_in'] ?? 0) - ($report['summary']['total_out'] ?? 0);
                $netValue = ($report['summary']['cost_in'] ?? 0) - ($report['summary']['cost_out'] ?? 0);
                $textClass = $netValue >= 0 ? 'text-success' : 'text-danger';
                ?>
                <h4 class="<?= $textClass ?> mb-0"><?= $netQuantity >= 0 ? '+' : '' ?><?= number_format($netQuantity, 3) ?> unidades</h4>
                <p class="text-muted mb-0">Valor Neto: <span class="<?= $textClass ?>">$<?= number_format($netValue, 2) ?></span></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Botones de Acciones -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-download"></i> Acciones del Reporte
                </h5>
            </div>
            <div class="card-body">
                <div class="btn-group">
                    <button class="btn btn-success" onclick="window.print()">
                        <i class="bi bi-printer"></i> Imprimir Reporte
                    </button>
                    <a href="<?= BASE_URL ?>/inventory/movements?date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="btn btn-info">
                        <i class="bi bi-list-ul"></i> Ver Movimientos Detallados
                    </a>
                    <a href="<?= BASE_URL ?>/inventory/addMovement" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Registrar Nuevo Movimiento
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($report) || (empty($report['by_product']) && empty($report['movements']))): ?>
<div class="text-center py-4">
    <i class="bi bi-file-earmark-text" style="font-size: 3rem; color: #ccc;"></i>
    <h4 class="text-muted mt-2">No hay datos para mostrar</h4>
    <p class="text-muted">No se encontraron movimientos de inventario en el período seleccionado.</p>
    <a href="<?= BASE_URL ?>/inventory/addMovement" class="btn btn-primary">
        <i class="bi bi-plus"></i> Registrar Primer Movimiento
    </a>
</div>
<?php endif; ?>

<style>
@media print {
    .btn, .card-header .btn, .no-print {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.badge {
    font-size: 0.75em;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.text-success {
    color: #198754 !important;
}

.text-danger {
    color: #dc3545 !important;
}

.border-success {
    border-color: #198754 !important;
}

.border-danger {
    border-color: #dc3545 !important;
}

.border-info {
    border-color: #0dcaf0 !important;
}
</style>