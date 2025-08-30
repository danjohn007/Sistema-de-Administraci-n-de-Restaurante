<?php $title = ($user['role'] === ROLE_WAITER) ? 'Consultar Menú' : 'Gestión de Menú'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-cup-hot"></i> <?= ($user['role'] === ROLE_WAITER) ? 'Consultar Menú' : 'Gestión de Menú' ?></h1>
    <?php if ($user['role'] === ROLE_ADMIN): ?>
    <div>
        <a href="<?= BASE_URL ?>/dishes/categories" class="btn btn-outline-primary me-2">
            <i class="bi bi-tags"></i> Gestionar Categorías
        </a>
        <a href="<?= BASE_URL ?>/dishes/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Platillo
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar Platillo</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Nombre o descripción">
            </div>
            <div class="col-md-4">
                <label for="category" class="form-label">Filtrar por Categoría</label>
                <select class="form-select" id="category" name="category">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="null" <?= $categoryFilter === 'null' ? 'selected' : '' ?>>Sin categoría</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="<?= BASE_URL ?>/dishes" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de platillos -->
<div class="card">
    <div class="card-body">
        <?php if (empty($dishes)): ?>
            <div class="text-center py-4">
                <i class="bi bi-cup-hot display-4 text-muted"></i>
                <p class="mt-3 text-muted">No se encontraron platillos</p>
                <?php if ($user['role'] === ROLE_ADMIN): ?>
                <a href="<?= BASE_URL ?>/dishes/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear Primer Platillo
                </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Platillo</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Fecha de Creación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dishes as $dish): ?>
                        <tr>
                            <td>
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($dish['name']) ?></h6>
                                    <?php if ($dish['description']): ?>
                                        <small class="text-muted">
                                            <?= htmlspecialchars(substr($dish['description'], 0, 100)) ?>
                                            <?= strlen($dish['description']) > 100 ? '...' : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($dish['category']): ?>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($dish['category']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Sin categoría</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-success">
                                    $<?= number_format($dish['price'], 2) ?>
                                </strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($dish['created_at'])) ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/dishes/show/<?= $dish['id'] ?>" 
                                       class="btn btn-outline-info" title="Ver Detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($user['role'] === ROLE_ADMIN): ?>
                                    <a href="<?= BASE_URL ?>/dishes/edit/<?= $dish['id'] ?>" 
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            title="Eliminar" 
                                            onclick="confirmDelete(<?= $dish['id'] ?>, '<?= htmlspecialchars($dish['name'], ENT_QUOTES) ?>')">
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

            <!-- Paginación -->
            <?php if ($pagination['total_pages'] > 1): ?>
            <nav aria-label="Paginación de platillos" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['has_prev']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $pagination['current_page'] - 2);
                    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryFilter) ?>">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <div class="text-muted small mt-3">
                Mostrando <?= count($dishes) ?> de <?= $pagination['total'] ?> platillos
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el platillo <strong id="dishName"></strong>?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(dishId, dishName) {
    document.getElementById('dishName').textContent = dishName;
    document.getElementById('deleteForm').action = '<?= BASE_URL ?>/dishes/delete/' + dishId;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>