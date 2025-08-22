<?php $title = 'Gestión de Tickets'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-receipt"></i> Gestión de Tickets</h1>
    <a href="<?= BASE_URL ?>/tickets/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Generar Ticket
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="bi bi-receipt display-1 text-muted"></i>
            <h3 class="mt-3">Módulo de Tickets</h3>
            <p class="text-muted">
                Esta funcionalidad está en desarrollo.<br>
                Próximamente podrá gestionar todos los tickets y facturación desde aquí.
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
                <i class="bi bi-receipt-cutoff display-4 text-primary"></i>
                <h5 class="mt-3">Generar Tickets</h5>
                <p class="text-muted">Crear tickets de venta y facturación</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-printer display-4 text-success"></i>
                <h5 class="mt-3">Imprimir</h5>
                <p class="text-muted">Imprimir tickets para entrega</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-graph-up display-4 text-info"></i>
                <h5 class="mt-3">Reportes</h5>
                <p class="text-muted">Ver estadísticas de ventas</p>
            </div>
        </div>
    </div>
</div>