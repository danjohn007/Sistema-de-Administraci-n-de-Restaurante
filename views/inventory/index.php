<?php $title = 'Gestión de Inventarios'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-boxes"></i> Gestión de Inventarios</h1>
    <div class="btn-group">
        <a href="<?= BASE_URL ?>/inventory/create" class="btn btn-primary">
            <i class="bi bi-plus"></i> Agregar Producto
        </a>
        <a href="<?= BASE_URL ?>/inventory/addMovement" class="btn btn-secondary">
            <i class="bi bi-arrow-up-down"></i> Registrar Movimiento
        </a>
        <a href="<?= BASE_URL ?>/inventory/report" class="btn btn-info">
            <i class="bi bi-graph-up"></i> Reportes
        </a>
        <?php if ($this->getCurrentUserRole() === ROLE_SUPERADMIN): ?>
        <a href="<?= BASE_URL ?>/inventory/settings" class="btn btn-warning">
            <i class="bi bi-gear"></i> Configuración
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Estadísticas Generales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-boxes" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Total Productos</h6>
                        <h3 class="mb-0"><?= count($products) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Stock Bajo</h6>
                        <h3 class="mb-0"><?= count($lowStockProducts) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Valor Inventario</h6>
                        <h3 class="mb-0">$<?= number_format($inventoryValue, 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-list" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Categorías</h6>
                        <h3 class="mb-0"><?= count($categories) ?></h3>
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
            (Mínimo: <?= number_format($product['min_stock'], 3) ?>)
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-funnel"></i> Filtros
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/inventory">
            <div class="row">
                <div class="col-md-4">
                    <label for="search" class="form-label">Buscar Producto</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Nombre o descripción...">
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label">Categoría</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" 
                                <?= $selected_category === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="<?= BASE_URL ?>/inventory" class="btn btn-secondary">
                        <i class="bi bi-x"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Productos -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list"></i> Productos en Inventario
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($products)): ?>
        <div class="text-center py-4">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-2">No se encontraron productos</p>
            <a href="<?= BASE_URL ?>/inventory/create" class="btn btn-primary">
                <i class="bi bi-plus"></i> Agregar Primer Producto
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Costo/Unidad</th>
                        <th>Valor Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                            <?php if ($product['is_dish_ingredient']): ?>
                            <span class="badge bg-info ms-1">Ingrediente</span>
                            <?php endif; ?>
                            <?php if (!empty($product['description'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($product['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($product['category'] ?: 'Sin categoría') ?></td>
                        <td>
                            <?= number_format($product['current_stock'], 3) ?> 
                            <?= htmlspecialchars($product['unit_measure']) ?>
                        </td>
                        <td>
                            <?= number_format($product['min_stock'], 3) ?> 
                            <?= htmlspecialchars($product['unit_measure']) ?>
                        </td>
                        <td>$<?= number_format($product['cost_per_unit'], 2) ?></td>
                        <td>$<?= number_format($product['current_stock'] * $product['cost_per_unit'], 2) ?></td>
                        <td>
                            <?php 
                            $stockStatus = $product['stock_status'] ?? 'normal';
                            $badgeClass = 'bg-success';
                            $statusText = 'Normal';
                            
                            if ($stockStatus === 'low') {
                                $badgeClass = 'bg-danger';
                                $statusText = 'Stock Bajo';
                            } elseif ($stockStatus === 'high') {
                                $badgeClass = 'bg-warning';
                                $statusText = 'Stock Alto';
                            }
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>/inventory/show/<?= $product['id'] ?>" 
                                   class="btn btn-outline-info" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/inventory/edit/<?= $product['id'] ?>" 
                                   class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
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

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>