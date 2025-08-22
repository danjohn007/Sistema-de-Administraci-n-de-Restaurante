<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $title ?? 'Imprimir Ticket' ?></h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Esta sección está en desarrollo.</p>
                <p>Aquí se mostrará la versión para imprimir del ticket ID: <?= $ticket_id ?? 'N/A' ?></p>
                <a href="<?= BASE_URL ?>/tickets" class="btn btn-secondary">Volver a Tickets</a>
            </div>
        </div>
    </div>
</div>