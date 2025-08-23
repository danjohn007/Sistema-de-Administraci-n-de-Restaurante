<?php $title = 'Gestión de Sucursales'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-building"></i> Gestión de Sucursales</h1>
    <div>
        <a href="<?= BASE_URL ?>/financial/createBranch" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Sucursal
        </a>
        <a href="<?= BASE_URL ?>/financial" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Lista de sucursales -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list"></i> Sucursales Registradas
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($branches)): ?>
        <div class="row">
            <?php foreach ($branches as $branch): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-building"></i> <?= htmlspecialchars($branch['name']) ?>
                            </h6>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/financial/viewBranch/<?= $branch['id'] ?>">
                                            <i class="bi bi-eye"></i> Ver Detalles
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/financial/editBranch/<?= $branch['id'] ?>">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/financial/manageBranchStaff/<?= $branch['id'] ?>">
                                            <i class="bi bi-people"></i> Gestionar Personal
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-danger" onclick="deleteBranch(<?= $branch['id'] ?>)">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Dirección</h6>
                            <p class="card-text">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($branch['address']) ?>
                            </p>
                        </div>
                        
                        <?php if ($branch['phone']): ?>
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Teléfono</h6>
                            <p class="card-text">
                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($branch['phone']) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($branch['manager_user_id']): ?>
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Gerente</h6>
                            <p class="card-text">
                                <i class="bi bi-person-badge"></i> Asignado
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Estado</h6>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Activa
                            </span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Creada: <?= date('d/m/Y', strtotime($branch['created_at'])) ?>
                            </small>
                            <a href="<?= BASE_URL ?>/financial/viewBranch/<?= $branch['id'] ?>" 
                               class="btn btn-sm btn-outline-primary">
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Resumen de sucursales -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title">Resumen de Sucursales</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <h4 class="text-primary"><?= count($branches) ?></h4>
                                <small class="text-muted">Sucursales Activas</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-success">
                                    <?php 
                                    $withManager = array_filter($branches, function($b) { return $b['manager_user_id']; });
                                    echo count($withManager);
                                    ?>
                                </h4>
                                <small class="text-muted">Con Gerente</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-warning">
                                    <?php 
                                    $withPhone = array_filter($branches, function($b) { return $b['phone']; });
                                    echo count($withPhone);
                                    ?>
                                </h4>
                                <small class="text-muted">Con Teléfono</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-info">
                                    <?= date('Y') ?>
                                </h4>
                                <small class="text-muted">Año Actual</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-building display-1 text-muted"></i>
            <h3 class="mt-3">No hay sucursales registradas</h3>
            <p class="text-muted">Crea la primera sucursal para comenzar a organizar el negocio</p>
            <a href="<?= BASE_URL ?>/financial/createBranch" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Crear Primera Sucursal
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Información adicional sobre sucursales -->
<?php if (!empty($branches)): ?>
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-info-circle"></i> Gestión de Sucursales
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Funcionalidades Disponibles:</h6>
                <ul>
                    <li>Asignación de personal por sucursal</li>
                    <li>Gestión de roles específicos por ubicación</li>
                    <li>Reportes financieros segmentados</li>
                    <li>Control de gastos y retiros por sucursal</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Próximas Mejoras:</h6>
                <ul>
                    <li>Dashboard específico por sucursal</li>
                    <li>Comparativas entre sucursales</li>
                    <li>Métricas de rendimiento</li>
                    <li>Configuración personalizada</li>
                </ul>
            </div>
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
                ¿Estás seguro de que deseas eliminar esta sucursal? 
                <br><br>
                <strong>Nota:</strong> Se eliminará también:
                <ul>
                    <li>Asignaciones de personal</li>
                    <li>Referencias en gastos y retiros</li>
                    <li>Historial asociado</li>
                </ul>
                Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
let branchToDelete = null;

function deleteBranch(id) {
    branchToDelete = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (branchToDelete) {
        window.location.href = '<?= BASE_URL ?>/financial/deleteBranch/' + branchToDelete;
    }
});
</script>