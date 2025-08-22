<?php $title = 'Gestión de Meseros'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-badge"></i> Gestión de Meseros</h1>
    <a href="<?= BASE_URL ?>/waiters/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Mesero
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <label for="search" class="form-label">Buscar Mesero</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Nombre, email o código de empleado">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-funnel"></i> Buscar
                </button>
                <a href="<?= BASE_URL ?>/waiters" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de meseros -->
<div class="card">
    <div class="card-body">
        <?php if (empty($waiters)): ?>
            <div class="text-center py-4">
                <i class="bi bi-person-badge-fill display-4 text-muted"></i>
                <p class="mt-3 text-muted">No se encontraron meseros</p>
                <a href="<?= BASE_URL ?>/waiters/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear Primer Mesero
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Mesero</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th>Fecha de Ingreso</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($waiters as $waiter): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary font-monospace">
                                    <?= htmlspecialchars($waiter['employee_code']) ?>
                                </span>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($waiter['name']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-envelope"></i> 
                                        <?= htmlspecialchars($waiter['email']) ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <?php if ($waiter['phone']): ?>
                                    <i class="bi bi-telephone"></i> 
                                    <?= htmlspecialchars($waiter['phone']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin teléfono</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($waiter['active'] && $waiter['user_active']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($waiter['created_at'])) ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/waiters/edit/<?= $waiter['id'] ?>" 
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/waiters/assignTables/<?= $waiter['id'] ?>" 
                                       class="btn btn-outline-info" title="Asignar Mesas">
                                        <i class="bi bi-grid-3x3-gap"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            title="Eliminar" 
                                            onclick="confirmDelete(<?= $waiter['id'] ?>, '<?= htmlspecialchars($waiter['name'], ENT_QUOTES) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($pagination['total_pages'] > 1): ?>
            <nav aria-label="Paginación de meseros" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['has_prev']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= urlencode($search) ?>">
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
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= urlencode($search) ?>">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <div class="text-muted small mt-3">
                Mostrando <?= count($waiters) ?> de <?= $pagination['total'] ?> meseros
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
                <p>¿Estás seguro de que deseas eliminar al mesero <strong id="waiterName"></strong>?</p>
                <p class="text-muted small">Esta acción desactivará también al usuario asociado y no se puede deshacer.</p>
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
function confirmDelete(waiterId, waiterName) {
    document.getElementById('waiterName').textContent = waiterName;
    document.getElementById('deleteForm').action = '<?= BASE_URL ?>/waiters/delete/' + waiterId;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>