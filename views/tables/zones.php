<?php $title = 'Gestión de Zonas'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-geo-alt"></i> Gestión de Zonas</h1>
    <div>
        <a href="<?= BASE_URL ?>/tables" class="btn btn-secondary me-2">
            <i class="bi bi-arrow-left"></i> Volver a Mesas
        </a>
        <a href="<?= BASE_URL ?>/tables/createZone" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Zona
        </a>
    </div>
</div>

<!-- Zone Statistics -->
<div class="row mb-4">
    <?php foreach ($zone_stats as $stat): ?>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center border-0" style="border-left: 4px solid <?= htmlspecialchars($stat['zone_color']) ?> !important;">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($stat['zone_name']) ?></h5>
                <div class="row">
                    <div class="col-6">
                        <div class="text-muted small">Total Mesas</div>
                        <div class="h4"><?= $stat['total_tables'] ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Ocupadas</div>
                        <div class="h4 text-warning"><?= $stat['occupied_tables'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Zones List -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Zonas Disponibles</h5>
    </div>
    <div class="card-body">
        <?php if (empty($zones)): ?>
            <div class="text-center py-4">
                <i class="bi bi-geo-alt display-1 text-muted"></i>
                <h4>No hay zonas registradas</h4>
                <p class="text-muted">Comience creando una nueva zona para organizar las mesas del restaurante.</p>
                <a href="<?= BASE_URL ?>/tables/createZone" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear Primera Zona
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Zona</th>
                            <th>Descripción</th>
                            <th>Color</th>
                            <th>Mesas</th>
                            <th>Estado</th>
                            <th width="150">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($zones as $zone): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($zone['name']) ?></strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars($zone['description'] ?: 'Sin descripción') ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge" style="background-color: <?= htmlspecialchars($zone['color']) ?>; color: white;">
                                    <?= htmlspecialchars($zone['color']) ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $zoneStats = array_filter($zone_stats, function($stat) use ($zone) {
                                    return $stat['zone_name'] === $zone['name'];
                                });
                                $zoneStat = reset($zoneStats);
                                ?>
                                <span class="badge bg-info">
                                    <?= $zoneStat ? $zoneStat['total_tables'] : 0 ?> mesas
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success">Activa</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/tables/editZone/<?= $zone['id'] ?>" 
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/tables/deleteZone/<?= $zone['id'] ?>" 
                                       class="btn btn-outline-danger" 
                                       title="Eliminar"
                                       onclick="return confirm('¿Está seguro de eliminar esta zona? Solo se puede eliminar si no tiene mesas asignadas.')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>