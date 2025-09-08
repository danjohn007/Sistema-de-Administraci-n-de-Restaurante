<?php $title = 'Movimientos de Inventario'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-arrow-up-down"></i> Movimientos de Inventario</h1>
    <div class="btn-group">
        <a href="<?= BASE_URL ?>/inventory/addMovement" class="btn btn-primary">
            <i class="bi bi-plus"></i> Registrar Movimiento
        </a>
        <a href="<?= BASE_URL ?>/inventory" class="btn btn-outline-secondary">
            <i class="bi bi-boxes"></i> Ver Productos
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-funnel"></i> Filtros
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/inventory/movements">
            <div class="row">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-md-3">
                    <label for="movement_type" class="form-label">Tipo de Movimiento</label>
                    <select class="form-select" id="movement_type" name="movement_type">
                        <option value="">Todos</option>
                        <option value="entrada" <?= $movement_type === 'entrada' ? 'selected' : '' ?>>Entradas</option>
                        <option value="salida" <?= $movement_type === 'salida' ? 'selected' : '' ?>>Salidas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Producto</label>
                    <select class="form-select" id="product_id" name="product_id">
                        <option value="">Todos los productos</option>
                        <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>" 
                                <?= $product_id == $product['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($product['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="<?= BASE_URL ?>/inventory/movements" class="btn btn-secondary">
                        <i class="bi bi-x"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Movimientos -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list"></i> Historial de Movimientos
            <span class="badge bg-primary"><?= count($movements) ?></span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($movements)): ?>
        <div class="text-center py-4">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-2">No hay movimientos en el período seleccionado</p>
            <a href="<?= BASE_URL ?>/inventory/addMovement" class="btn btn-primary">
                <i class="bi bi-plus"></i> Registrar Primer Movimiento
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Costo Total</th>
                        <th>Referencia</th>
                        <th>Usuario</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $movement): ?>
                    <tr>
                        <td>
                            <small>
                                <?= date('d/m/Y H:i', strtotime($movement['movement_date'])) ?>
                            </small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($movement['product_name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($movement['product_category']) ?></small>
                        </td>
                        <td>
                            <?php if ($movement['movement_type'] === 'entrada'): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-arrow-down"></i> Entrada
                            </span>
                            <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-arrow-up"></i> Salida
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= number_format($movement['quantity'], 3) ?> 
                            <?= htmlspecialchars($movement['unit_measure']) ?>
                        </td>
                        <td>
                            <?php if ($movement['cost_per_unit'] > 0): ?>
                            $<?= number_format($movement['cost_per_unit'], 2) ?>
                            <?php else: ?>
                            <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($movement['total_cost'] > 0): ?>
                            <strong>$<?= number_format($movement['total_cost'], 2) ?></strong>
                            <?php else: ?>
                            <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $refTypes = [
                                'expense' => ['text' => 'Gasto', 'class' => 'bg-warning'],
                                'ticket' => ['text' => 'Ticket', 'class' => 'bg-info'],
                                'adjustment' => ['text' => 'Ajuste', 'class' => 'bg-secondary'],
                                'manual' => ['text' => 'Manual', 'class' => 'bg-dark']
                            ];
                            $refInfo = $refTypes[$movement['reference_type']] ?? ['text' => 'Otro', 'class' => 'bg-light'];
                            ?>
                            <span class="badge <?= $refInfo['class'] ?>">
                                <?= $refInfo['text'] ?>
                            </span>
                            <?php if ($movement['reference_id']): ?>
                            <br><small class="text-muted">#<?= $movement['reference_id'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?= htmlspecialchars($movement['user_name']) ?></small>
                        </td>
                        <td>
                            <?php if (!empty($movement['description'])): ?>
                            <small><?= htmlspecialchars($movement['description']) ?></small>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Resumen -->
        <div class="mt-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Entradas</h6>
                            <h4 class="mb-0">
                                <?php
                                $totalEntradas = 0;
                                $costEntradas = 0;
                                foreach ($movements as $mov) {
                                    if ($mov['movement_type'] === 'entrada') {
                                        $totalEntradas += $mov['quantity'];
                                        $costEntradas += $mov['total_cost'];
                                    }
                                }
                                echo number_format($totalEntradas, 3);
                                ?>
                            </h4>
                            <small>Costo: $<?= number_format($costEntradas, 2) ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Salidas</h6>
                            <h4 class="mb-0">
                                <?php
                                $totalSalidas = 0;
                                $costSalidas = 0;
                                foreach ($movements as $mov) {
                                    if ($mov['movement_type'] === 'salida') {
                                        $totalSalidas += $mov['quantity'];
                                        $costSalidas += $mov['total_cost'];
                                    }
                                }
                                echo number_format($totalSalidas, 3);
                                ?>
                            </h4>
                            <small>Costo: $<?= number_format($costSalidas, 2) ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Movimiento Neto</h6>
                            <h4 class="mb-0">
                                <?= number_format($totalEntradas - $totalSalidas, 3) ?>
                            </h4>
                            <small>Valor: $<?= number_format($costEntradas - $costSalidas, 2) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.badge {
    font-size: 0.75em;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875em;
}

.table td {
    font-size: 0.875em;
    vertical-align: middle;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table-responsive {
    border-radius: 0.375rem;
}
</style>