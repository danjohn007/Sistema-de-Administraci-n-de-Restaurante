<?php $title = ($user['role'] === ROLE_WAITER) ? 'Consultar Mesas' : 'Gestión de Mesas'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-grid-3x3-gap"></i> <?= ($user['role'] === ROLE_WAITER) ? 'Consultar Mesas' : 'Gestión de Mesas' ?></h1>
    <?php if ($user['role'] === ROLE_ADMIN): ?>
    <div>
        <a href="<?= BASE_URL ?>/tables/zones" class="btn btn-outline-secondary me-2">
            <i class="bi bi-geo-alt"></i> Gestionar Zonas
        </a>
        <a href="<?= BASE_URL ?>/tables/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Mesa
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Estadísticas -->
<?php if (!empty($stats)): ?>
<div class="row mb-4">
    <?php foreach ($statuses as $status => $label): ?>
        <?php if (isset($stats[$status])): ?>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="card-title text-<?= getStatusColor($status) ?>">
                        <?= $stats[$status]['count'] ?>
                    </h2>
                    <p class="card-text"><?= $label ?></p>
                    <small class="text-muted"><?= $stats[$status]['percentage'] ?>%</small>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Número de mesa o mesero">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Filtrar por Estado</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos los estados</option>
                    <?php foreach ($statuses as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $statusFilter === $value ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="<?= BASE_URL ?>/tables" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de mesas -->
<div class="card">
    <div class="card-body">
        <?php if (empty($tables)): ?>
            <div class="text-center py-4">
                <i class="bi bi-table display-4 text-muted"></i>
                <p class="mt-3 text-muted">No se encontraron mesas</p>
                <?php if ($user['role'] === ROLE_ADMIN): ?>
                <a href="<?= BASE_URL ?>/tables/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear Primera Mesa
                </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mesa #</th>
                            <th>Capacidad</th>
                            <th>Estado</th>
                            <th>Mesero Asignado</th>
                            <th>Fecha de Creación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $table): ?>
                        <tr>
                            <td>
                                <h5 class="mb-1">
                                    <span class="badge bg-dark">
                                        Mesa <?= $table['number'] ?>
                                    </span>
                                </h5>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-people"></i> 
                                    <?= $table['capacity'] ?> personas
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = getStatusColor($table['status']);
                                ?>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= $statuses[$table['status']] ?? ucfirst($table['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($table['waiter_name']): ?>
                                    <div>
                                        <strong><?= htmlspecialchars($table['waiter_name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($table['employee_code']) ?></small>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($table['created_at'])) ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <?php if ($user['role'] === ROLE_ADMIN): ?>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/tables/edit/<?= $table['id'] ?>" 
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/tables/changeStatus/<?= $table['id'] ?>" 
                                       class="btn btn-outline-info" title="Cambiar Estado">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </a>
                                    <?php if ($table['status'] === TABLE_AVAILABLE): ?>
                                    <button type="button" class="btn btn-outline-danger" 
                                            title="Eliminar" 
                                            onclick="confirmDelete(<?= $table['id'] ?>, <?= $table['number'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">Solo consulta</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($pagination['total_pages'] > 1): ?>
            <nav aria-label="Paginación de mesas" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['has_prev']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">
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
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <div class="text-muted small mt-3">
                Mostrando <?= count($tables) ?> de <?= $pagination['total'] ?> mesas
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
                <p>¿Estás seguro de que deseas eliminar la <strong id="tableName"></strong>?</p>
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

<?php
function getStatusColor($status) {
    switch($status) {
        case TABLE_AVAILABLE:
            return 'success';
        case TABLE_OCCUPIED:
            return 'warning';
        case TABLE_BILL_REQUESTED:
            return 'info';
        case 'cerrada':
            return 'secondary';
        default:
            return 'secondary';
    }
}
?>

<script>
function confirmDelete(tableId, tableNumber) {
    document.getElementById('tableName').textContent = 'Mesa ' + tableNumber;
    document.getElementById('deleteForm').action = '<?= BASE_URL ?>/tables/delete/' + tableId;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>