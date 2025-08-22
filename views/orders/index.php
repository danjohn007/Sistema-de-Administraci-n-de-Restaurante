<?php $title = 'Gestión de Pedidos'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-clipboard-check"></i> Gestión de Pedidos</h1>
    <a href="<?= BASE_URL ?>/orders/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Pedido
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-clipboard-check display-1 text-muted"></i>
            <h3 class="mt-3">Módulo de Pedidos</h3>
            <p class="text-muted">
                Esta funcionalidad está en desarrollo.<br>
                Próximamente podrá gestionar todos los pedidos del restaurante desde aquí.
            </p>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Features Preview -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-plus-circle display-4 text-primary"></i>
                <h5 class="mt-3">Crear Pedidos</h5>
                <p class="text-muted">Registrar nuevos pedidos para las mesas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-eye display-4 text-success"></i>
                <h5 class="mt-3">Seguimiento</h5>
                <p class="text-muted">Monitorear el estado de los pedidos</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-check-circle display-4 text-info"></i>
                <h5 class="mt-3">Completar</h5>
                <p class="text-muted">Marcar pedidos como listos o entregados</p>
            </div>
        </div>
    </div>
</div>