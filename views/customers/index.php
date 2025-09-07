<?php $title = 'Gestión de Clientes'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people"></i> Gestión de Clientes</h1>
    <div>
        <a href="<?= BASE_URL ?>/customers/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Cliente
        </a>
        <a href="<?= BASE_URL ?>/best_diners/report" class="btn btn-outline-info">
            <i class="bi bi-file-earmark-bar-graph"></i> Mejores Comensales - Reporte Completo
        </a>
        <a href="<?= BASE_URL ?>/best_diners/bySpending" class="btn btn-outline-success">
            <i class="bi bi-currency-dollar"></i> Top por Consumo
        </a>
        <a href="<?= BASE_URL ?>/best_diners/byVisits" class="btn btn-outline-warning">
            <i class="bi bi-people-fill"></i> Top por Visitas
        </a>
    </div>
</div>

<!-- Search and Stats Card -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <label for="search" class="form-label">Buscar Cliente</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="<?= htmlspecialchars($search) ?>"
                               placeholder="Buscar por nombre o teléfono...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <?php if ($search): ?>
                    <div class="col-12">
                        <a href="<?= BASE_URL ?>/customers" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Limpiar búsqueda
                        </a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4><?= number_format($totalCustomers) ?></h4>
                <p class="mb-0">Total de Clientes</p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($customers)): ?>
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-people display-1 text-muted"></i>
            <h3 class="mt-3">
                <?= $search ? 'No se encontraron clientes' : 'No hay clientes registrados' ?>
            </h3>
            <p class="text-muted">
                <?= $search 
                    ? 'Intenta con otros términos de búsqueda' 
                    : 'Comienza agregando tu primer cliente al sistema' ?>
            </p>
            <?php if (!$search): ?>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/customers/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Agregar Primer Cliente
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Customers Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <?= $search ? "Resultados de búsqueda ({$totalCustomers})" : "Lista de Clientes" ?>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Visitas</th>
                        <th>Total Gastado</th>
                        <th>Última Visita</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?= htmlspecialchars($customer['name']) ?></strong>
                                <?php 
                                if (!empty($customer['birthday_day']) && !empty($customer['birthday_month'])): 
                                    $birthdayFormatted = sprintf('%02d/%02d', $customer['birthday_day'], $customer['birthday_month']);
                                ?>
                                    <br><small class="text-muted">
                                        <i class="bi bi-cake"></i> <?= htmlspecialchars($birthdayFormatted) ?>
                                    </small>
                                <?php elseif (!empty($customer['birthday'])): ?>
                                    <br><small class="text-muted">
                                        <i class="bi bi-cake"></i> <?= htmlspecialchars($customer['birthday']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($customer['phone']) ?></span>
                        </td>
                        <td>
                            <?= $customer['email'] ? htmlspecialchars($customer['email']) : '<span class="text-muted">-</span>' ?>
                        </td>
                        <td>
                            <span class="badge bg-success"><?= $customer['total_visits'] ?></span>
                        </td>
                        <td>
                            <strong>$<?= number_format($customer['total_spent'], 2) ?></strong>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= $customer['updated_at'] ? date('d/m/Y', strtotime($customer['updated_at'])) : 'Nunca' ?>
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/customers/show/<?= $customer['id'] ?>" 
                                   class="btn btn-outline-primary" 
                                   title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/customers/edit/<?= $customer['id'] ?>" 
                                   class="btn btn-outline-secondary" 
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                                <button type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Eliminar"
                                        onclick="confirmDelete(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['name']) ?>')">
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
    </div>
</div>

<?php if (!$search && $totalPages > 1): ?>
<!-- Pagination -->
<nav aria-label="Paginación de clientes">
    <ul class="pagination justify-content-center mt-4">
        <?php if ($page > 1): ?>
        <li class="page-item">
            <a class="page-link" href="<?= BASE_URL ?>/customers?page=<?= $page - 1 ?>">
                <i class="bi bi-chevron-left"></i> Anterior
            </a>
        </li>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= BASE_URL ?>/customers?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <li class="page-item">
            <a class="page-link" href="<?= BASE_URL ?>/customers?page=<?= $page + 1 ?>">
                Siguiente <i class="bi bi-chevron-right"></i>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar al cliente <strong id="customerName"></strong>?</p>
                <p class="text-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    Si el cliente tiene pedidos asociados, será desactivado en lugar de eliminado.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(customerId, customerName) {
    document.getElementById('customerName').textContent = customerName;
    document.getElementById('deleteForm').action = '<?= BASE_URL ?>/customers/delete/' + customerId;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>