<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $title ?? 'Pedidos de Mesa' ?></h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Esta sección está en desarrollo.</p>
                <p>Aquí se mostrarán los pedidos de la mesa ID: <?= $table_id ?? 'N/A' ?></p>
                <a href="<?= BASE_URL ?>/orders" class="btn btn-secondary">Volver a Pedidos</a>
            </div>
        </div>
    </div>
</div>