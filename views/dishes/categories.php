<?php $title = 'Gestionar Categorías'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-tags"></i> Gestionar Categorías</h1>
    <a href="<?= BASE_URL ?>/dishes" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Menú
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Categorías del Menú</h5>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-tags display-4 text-muted"></i>
                        <p class="mt-3 text-muted">No hay categorías definidas</p>
                        <p class="text-muted">Las categorías se crean automáticamente al agregar platillos</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Categoría</th>
                                    <th>Platillos</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <h6 class="mb-0"><?= htmlspecialchars($category) ?></h6>
                                    </td>
                                    <td>
                                        <?php $dishCount = count($dishesByCategory[$category] ?? []); ?>
                                        <span class="badge bg-primary"><?= $dishCount ?> platillo<?= $dishCount != 1 ? 's' : '' ?></span>
                                        
                                        <?php if (isset($dishesByCategory[$category]) && count($dishesByCategory[$category]) > 0): ?>
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <?php 
                                                    $dishNames = array_slice(array_column($dishesByCategory[$category], 'name'), 0, 3);
                                                    echo htmlspecialchars(implode(', ', $dishNames));
                                                    if (count($dishesByCategory[$category]) > 3) {
                                                        echo ' y ' . (count($dishesByCategory[$category]) - 3) . ' más';
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="showRenameModal('<?= htmlspecialchars($category, ENT_QUOTES) ?>')"
                                                    title="Renombrar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="showDeleteModal('<?= htmlspecialchars($category, ENT_QUOTES) ?>', <?= $dishCount ?>)"
                                                    title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle"></i>
                    <strong>Información:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Renombrar:</strong> Cambia el nombre de la categoría para todos los platillos asociados</li>
                        <li><strong>Eliminar:</strong> Remueve la categoría de todos los platillos (los platillos permanecen sin categoría)</li>
                        <li>Las categorías se crean automáticamente al agregar o editar platillos</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para renombrar categoría -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renameModalLabel">Renombrar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="renameForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" name="old_category" id="renameOldCategory">
                    
                    <div class="mb-3">
                        <label for="renameNewCategory" class="form-label">Nuevo nombre de la categoría</label>
                        <input type="text" class="form-control" id="renameNewCategory" name="new_category" 
                               placeholder="Nuevo nombre..." required maxlength="100">
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Este cambio afectará a todos los platillos de la categoría <strong id="renameCategoryName"></strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Renombrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para eliminar categoría -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Eliminar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="old_category" id="deleteOldCategory">
                    
                    <p>¿Estás seguro de que deseas eliminar la categoría <strong id="deleteCategoryName"></strong>?</p>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Atención:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Los <span id="deleteDishCount"></span> platillos de esta categoría quedarán sin categoría</li>
                            <li>Esta acción no se puede deshacer</li>
                            <li>Los platillos no serán eliminados, solo perderán su categoría</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRenameModal(categoryName) {
    document.getElementById('renameOldCategory').value = categoryName;
    document.getElementById('renameCategoryName').textContent = categoryName;
    document.getElementById('renameNewCategory').value = categoryName;
    
    var renameModal = new bootstrap.Modal(document.getElementById('renameModal'));
    renameModal.show();
    
    // Focus on input after modal is shown
    document.getElementById('renameModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('renameNewCategory').focus();
        document.getElementById('renameNewCategory').select();
    }, { once: true });
}

function showDeleteModal(categoryName, dishCount) {
    document.getElementById('deleteOldCategory').value = categoryName;
    document.getElementById('deleteCategoryName').textContent = categoryName;
    document.getElementById('deleteDishCount').textContent = dishCount + ' platillo' + (dishCount != 1 ? 's' : '');
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>