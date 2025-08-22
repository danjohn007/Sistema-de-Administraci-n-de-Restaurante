<?php $title = 'Pedidos de Mesa ' . ($table_id ?? ''); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-grid-3x3-gap"></i> Pedidos de Mesa <?= htmlspecialchars($table_id ?? '') ?></h1>
    <div>
        <a href="<?= BASE_URL ?>/orders/create" class="btn btn-primary me-2">
            <i class="bi bi-plus-circle"></i> Nuevo Pedido
        </a>
        <a href="<?= BASE_URL ?>/tables" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Mesas
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-grid-3x3-gap display-1 text-muted"></i>
            <h3 class="mt-3">Pedidos de la Mesa <?= htmlspecialchars($table_id ?? '') ?></h3>
            <p class="text-muted">
                Esta funcionalidad está en desarrollo.<br>
                Próximamente podrá ver y gestionar los pedidos específicos de esta mesa.
            </p>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/orders/create" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Crear Pedido
                </a>
                <a href="<?= BASE_URL ?>/tables" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver a Mesas
                </a>
            </div>
        </div>
    </div>
</div>