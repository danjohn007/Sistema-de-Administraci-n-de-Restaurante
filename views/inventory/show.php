<?php $title = 'Detalles del Producto - ' . $product['name']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-box"></i> <?= htmlspecialchars($product['name']) ?></h1>
    <div class="btn-group">
        <a href="<?= BASE_URL ?>/inventory/edit/<?= $product['id'] ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="<?= BASE_URL ?>/inventory" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Inventario
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Información del Producto -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Información del Producto
                </h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-6">Nombre:</dt>
                    <dd class="col-sm-6"><?= htmlspecialchars($product['name']) ?></dd>
                    
                    <dt class="col-sm-6">Categoría:</dt>
                    <dd class="col-sm-6"><?= htmlspecialchars($product['category'] ?: 'Sin categoría') ?></dd>
                    
                    <dt class="col-sm-6">Unidad:</dt>
                    <dd class="col-sm-6"><?= htmlspecialchars($product['unit_measure']) ?></dd>
                    
                    <dt class="col-sm-6">Descripción:</dt>
                    <dd class="col-sm-6"><?= htmlspecialchars($product['description'] ?: 'Sin descripción') ?></dd>
                    
                    <dt class="col-sm-6">Ingrediente:</dt>
                    <dd class="col-sm-6">
                        <?php if ($product['is_dish_ingredient']): ?>
                        <span class="badge bg-info">Sí</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">No</span>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>
        
        <!-- Estadísticas de Stock -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Estadísticas de Stock
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <h3 class="text-primary mb-0"><?= number_format($product['current_stock'], 3) ?></h3>
                        <small class="text-muted"><?= htmlspecialchars($product['unit_measure']) ?> disponibles</small>
                    </div>
                </div>
                
                <dl class="row">
                    <dt class="col-sm-6">Stock Mínimo:</dt>
                    <dd class="col-sm-6"><?= number_format($product['min_stock'], 3) ?> <?= htmlspecialchars($product['unit_measure']) ?></dd>
                    
                    <dt class="col-sm-6">Stock Máximo:</dt>
                    <dd class="col-sm-6"><?= number_format($product['max_stock'], 3) ?> <?= htmlspecialchars($product['unit_measure']) ?></dd>
                    
                    <dt class="col-sm-6">Costo/Unidad:</dt>
                    <dd class="col-sm-6">$<?= number_format($product['cost_per_unit'], 2) ?></dd>
                    
                    <dt class="col-sm-6">Valor Total:</dt>
                    <dd class="col-sm-6"><strong>$<?= number_format($product['current_stock'] * $product['cost_per_unit'], 2) ?></strong></dd>
                </dl>
                
                <!-- Indicador de Estado -->
                <div class="mt-3">
                    <?php 
                    $stockStatus = 'normal';
                    $badgeClass = 'bg-success';
                    $statusText = 'Stock Normal';
                    
                    if ($product['current_stock'] <= $product['min_stock']) {
                        $stockStatus = 'low';
                        $badgeClass = 'bg-danger';
                        $statusText = 'Stock Bajo';
                    } elseif ($product['current_stock'] >= $product['max_stock']) {
                        $stockStatus = 'high';
                        $badgeClass = 'bg-warning';
                        $statusText = 'Stock Alto';
                    }
                    ?>
                    <span class="badge <?= $badgeClass ?> w-100 p-2"><?= $statusText ?></span>
                </div>
            </div>
        </div>
        
        <!-- Platillos que usan este ingrediente -->
        <?php if ($product['is_dish_ingredient'] && !empty($dishes)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-egg-fried"></i> Platillos que lo usan
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($dishes as $dish): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong><?= htmlspecialchars($dish['name']) ?></strong>
                        <br><small class="text-muted">Cantidad: <?= number_format($dish['quantity_needed'], 3) ?> <?= htmlspecialchars($product['unit_measure']) ?></small>
                    </div>
                    <span class="badge bg-info">$<?= number_format($dish['price'], 2) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-8">
        <!-- Historial de Movimientos -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i> Historial de Movimientos
                </h5>
                <a href="<?= BASE_URL ?>/inventory/addMovement" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Nuevo Movimiento
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($movements)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-2">No hay movimientos registrados para este producto</p>
                    <a href="<?= BASE_URL ?>/inventory/addMovement" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Registrar Primer Movimiento
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Costo</th>
                                <th>Referencia</th>
                                <th>Usuario</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movements as $movement): ?>
                            <tr>
                                <td>
                                    <small><?= date('d/m/Y H:i', strtotime($movement['movement_date'])) ?></small>
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
                                    <small class="text-muted"><?= htmlspecialchars($product['unit_measure']) ?></small>
                                </td>
                                <td>
                                    <?php if ($movement['total_cost'] > 0): ?>
                                    $<?= number_format($movement['total_cost'], 2) ?>
                                    <br><small class="text-muted">$<?= number_format($movement['cost_per_unit'], 2) ?>/u</small>
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
                                    <span class="badge <?= $refInfo['class'] ?>"><?= $refInfo['text'] ?></span>
                                    <?php if ($movement['reference_id']): ?>
                                    <br><small class="text-muted">#<?= $movement['reference_id'] ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($movement['user_name'] ?? 'Sistema') ?></small>
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
                
                <!-- Mostrar link para ver más movimientos si hay muchos -->
                <?php if (count($movements) >= 100): ?>
                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>/inventory/movements?product_id=<?= $product['id'] ?>" class="btn btn-outline-primary">
                        <i class="bi bi-list"></i> Ver todos los movimientos
                    </a>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
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

.table-sm td, .table-sm th {
    padding: 0.5rem;
}

dl {
    margin-bottom: 0;
}

dt {
    font-weight: 600;
}

dd {
    margin-bottom: 0.5rem;
}
</style>