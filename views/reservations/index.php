<?php $title = 'Gestión de Reservaciones'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-calendar-check"></i> Reservaciones</h1>
    <div>
        <a href="<?= BASE_URL ?>/reservations/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Reservación
        </a>
    </div>
</div>

<!-- Filter tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $filter === 'today' ? 'active' : '' ?>" 
           href="<?= BASE_URL ?>/reservations?filter=today">
            <i class="bi bi-calendar-day"></i> Hoy
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filter === 'future' ? 'active' : '' ?>" 
           href="<?= BASE_URL ?>/reservations?filter=future">
            <i class="bi bi-calendar-plus"></i> Próximas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filter === 'all' ? 'active' : '' ?>" 
           href="<?= BASE_URL ?>/reservations?filter=all">
            <i class="bi bi-calendar-range"></i> Todas
        </a>
    </li>
</ul>

<?php if (empty($reservations)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        No hay reservaciones 
        <?php if ($filter === 'today'): ?>
            para hoy.
        <?php elseif ($filter === 'future'): ?>
            próximas.
        <?php else: ?>
            en el sistema.
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Mesas</th>
                            <th>Mesero</th>
                            <th>Fecha/Hora</th>
                            <th>Personas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary">
                                    #<?= str_pad($reservation['id'], 3, '0', STR_PAD_LEFT) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($reservation['customer_name']) ?></strong>
                            </td>
                            <td>
                                <a href="tel:<?= htmlspecialchars($reservation['customer_phone']) ?>" 
                                   class="text-decoration-none">
                                    <i class="bi bi-telephone"></i>
                                    <?= htmlspecialchars($reservation['customer_phone']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if (!empty($reservation['table_numbers'])): ?>
                                    <span class="badge bg-info">
                                        Mesa<?= strpos($reservation['table_numbers'], ',') !== false ? 's' : '' ?> <?= $reservation['table_numbers'] ?>
                                    </span>
                                    <br><small class="text-muted">
                                        (Capacidad total: <?= $reservation['total_capacity'] ?? 'N/A' ?> personas)
                                    </small>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sin mesa asignada</span>
                                    <br><small class="text-muted">(Se asignará automáticamente)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($reservation['waiter_name'])): ?>
                                    <span class="badge bg-success">
                                        <?= htmlspecialchars($reservation['waiter_name']) ?>
                                    </span>
                                    <br><small class="text-muted">
                                        (<?= htmlspecialchars($reservation['waiter_code']) ?>)
                                    </small>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($reservation['reservation_datetime'])) ?><br>
                                <small class="text-muted">
                                    <?= date('H:i', strtotime($reservation['reservation_datetime'])) ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <?= $reservation['party_size'] ?> 
                                    persona<?= $reservation['party_size'] > 1 ? 's' : '' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge status-<?= $reservation['status'] ?>">
                                    <?= ucfirst($reservation['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/reservations/show/<?= $reservation['id'] ?>" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($reservation['status'] === 'pendiente'): ?>
                                    <a href="<?= BASE_URL ?>/reservations/edit/<?= $reservation['id'] ?>" 
                                       class="btn btn-outline-warning btn-sm" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (in_array($reservation['status'], ['pendiente', 'confirmada'])): ?>
                                    <div class="btn-group">
                                        <button type="button" 
                                                class="btn btn-outline-success btn-sm dropdown-toggle" 
                                                data-bs-toggle="dropdown" 
                                                title="Cambiar estado">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php if ($reservation['status'] === 'pendiente'): ?>
                                            <li>
                                                <form method="POST" action="<?= BASE_URL ?>/reservations/updateStatus/<?= $reservation['id'] ?>" style="display: inline;">
                                                    <input type="hidden" name="status" value="confirmada">
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bi bi-check-circle"></i> Confirmar
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($reservation['status'] === 'confirmada'): ?>
                                            <li>
                                                <form method="POST" action="<?= BASE_URL ?>/reservations/updateStatus/<?= $reservation['id'] ?>" style="display: inline;">
                                                    <input type="hidden" name="status" value="completada">
                                                    <button type="submit" class="dropdown-item text-primary">
                                                        <i class="bi bi-check2-all"></i> Completar
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <li>
                                                <form method="POST" action="<?= BASE_URL ?>/reservations/updateStatus/<?= $reservation['id'] ?>" style="display: inline;">
                                                    <input type="hidden" name="status" value="cancelada">
                                                    <button type="submit" class="dropdown-item text-danger" 
                                                            onclick="return confirm('¿Está seguro de cancelar esta reservación?')">
                                                        <i class="bi bi-x-circle"></i> Cancelar
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
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
<?php endif; ?>

<style>
.status-pendiente {
    background-color: #ffc107;
    color: #000;
}

.status-confirmada {
    background-color: #198754;
    color: #fff;
}

.status-cancelada {
    background-color: #dc3545;
    color: #fff;
}

.status-completada {
    background-color: #6c757d;
    color: #fff;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>