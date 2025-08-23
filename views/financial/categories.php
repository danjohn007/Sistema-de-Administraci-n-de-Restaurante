<?php $title = 'Gestión de Categorías'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-tags"></i> Gestión de Categorías</h1>
    <div>
        <a href="<?= BASE_URL ?>/financial/createCategory" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Categoría
        </a>
        <a href="<?= BASE_URL ?>/financial" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Lista de categorías -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list"></i> Categorías de Gastos
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($categories)): ?>
        <div class="row">
            <?php foreach ($categories as $category): ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header" style="background-color: <?= htmlspecialchars($category['color']) ?>; color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">
                                <?= htmlspecialchars($category['name']) ?>
                            </h6>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/financial/editCategory/<?= $category['id'] ?>">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger" onclick="deleteCategory(<?= $category['id'] ?>)">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($category['description']): ?>
                        <p class="card-text text-muted">
                            <?= htmlspecialchars($category['description']) ?>
                        </p>
                        <?php endif; ?>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h5 class="text-danger mb-1">$<?= number_format($category['total_amount'], 2) ?></h5>
                                    <small class="text-muted">Total Gastado</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h5 class="text-primary mb-1"><?= $category['total_expenses'] ?></h5>
                                <small class="text-muted">Gastos</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Creada: <?= date('d/m/Y', strtotime($category['created_at'])) ?>
                            </small>
                            <a href="<?= BASE_URL ?>/financial/expenses?category_id=<?= $category['id'] ?>" 
                               class="btn btn-sm btn-outline-primary">
                                Ver Gastos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Resumen de categorías -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title">Resumen de Categorías</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <h4 class="text-primary"><?= count($categories) ?></h4>
                                <small class="text-muted">Categorías Activas</small>
                            </div>
                            <div class="col-md-4">
                                <h4 class="text-danger">
                                    $<?php 
                                    $totalAmount = array_sum(array_column($categories, 'total_amount'));
                                    echo number_format($totalAmount, 2);
                                    ?>
                                </h4>
                                <small class="text-muted">Total Gastado</small>
                            </div>
                            <div class="col-md-4">
                                <h4 class="text-success">
                                    <?php 
                                    $totalExpenses = array_sum(array_column($categories, 'total_expenses'));
                                    echo $totalExpenses;
                                    ?>
                                </h4>
                                <small class="text-muted">Total de Gastos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-tags display-1 text-muted"></i>
            <h3 class="mt-3">No hay categorías creadas</h3>
            <p class="text-muted">Crea la primera categoría para organizar los gastos</p>
            <a href="<?= BASE_URL ?>/financial/createCategory" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Crear Primera Categoría
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Distribución de gastos por categoría -->
<?php if (!empty($categories) && array_sum(array_column($categories, 'total_amount')) > 0): ?>
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-pie-chart"></i> Distribución de Gastos por Categoría
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php 
            $totalGlobal = array_sum(array_column($categories, 'total_amount'));
            foreach ($categories as $category): 
                if ($category['total_amount'] > 0):
                    $percentage = ($category['total_amount'] / $totalGlobal) * 100;
            ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle" 
                             style="width: 20px; height: 20px; background-color: <?= htmlspecialchars($category['color']) ?>"></div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold"><?= htmlspecialchars($category['name']) ?></span>
                            <span class="text-muted"><?= number_format($percentage, 1) ?>%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" 
                                 style="width: <?= $percentage ?>%; background-color: <?= htmlspecialchars($category['color']) ?>"></div>
                        </div>
                        <small class="text-muted">$<?= number_format($category['total_amount'], 2) ?></small>
                    </div>
                </div>
            </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
    </div>
</div>
<?php endif; ?>

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
                ¿Estás seguro de que deseas eliminar esta categoría? 
                <br><br>
                <strong>Nota:</strong> No se podrá eliminar si tiene gastos asociados.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
let categoryToDelete = null;

function deleteCategory(id) {
    categoryToDelete = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (categoryToDelete) {
        window.location.href = '<?= BASE_URL ?>/financial/deleteCategory/' + categoryToDelete;
    }
});
</script>