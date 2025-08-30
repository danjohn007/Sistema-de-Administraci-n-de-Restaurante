<?php $title = 'Detalles de Reservación #' . $reservation['id']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-calendar-check"></i> Reservación #<?= str_pad($reservation['id'], 3, '0', STR_PAD_LEFT) ?></h1>
    <div>
        <?php if ($reservation['status'] === 'pendiente'): ?>
        <a href="<?= BASE_URL ?>/reservations/edit/<?= $reservation['id'] ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/reservations" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Reservaciones
        </a>
    </div>
</div>

<div class="row">
    <!-- Reservation Details -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Información de la Reservación
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Estado:</label>
                    <div>
                        <span class="badge status-<?= $reservation['status'] ?> fs-6">
                            <?= ucfirst($reservation['status']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Mesas Asignadas:</label>
                    <div>
                        <?php if (!empty($reservation['table_numbers'])): ?>
                            <span class="badge bg-info fs-6">Mesa<?= strpos($reservation['table_numbers'], ',') !== false ? 's' : '' ?> <?= $reservation['table_numbers'] ?></span>
                            <small class="text-muted ms-2">(Capacidad total: <?= $reservation['total_capacity'] ?? 'N/A' ?> personas)</small>
                        <?php else: ?>
                            <span class="badge bg-secondary fs-6">Sin mesa asignada</span>
                            <small class="text-muted ms-2">(Se asignará automáticamente)</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Mesero Asignado:</label>
                    <div>
                        <?php if (!empty($reservation['waiter_name'])): ?>
                            <span class="badge bg-success fs-6"><?= htmlspecialchars($reservation['waiter_name']) ?></span>
                            <small class="text-muted ms-2">(<?= htmlspecialchars($reservation['waiter_code']) ?>)</small>
                        <?php else: ?>
                            <span class="badge bg-secondary fs-6">Sin asignar</span>
                            <small class="text-muted ms-2">(Se asignará durante confirmación)</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Fecha y Hora:</label>
                    <div>
                        <i class="bi bi-calendar"></i>
                        <?= date('l, d \de F \de Y', strtotime($reservation['reservation_datetime'])) ?>
                        <br>
                        <i class="bi bi-clock"></i>
                        <?= date('H:i', strtotime($reservation['reservation_datetime'])) ?> hrs
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Número de Personas:</label>
                    <div>
                        <span class="badge bg-light text-dark">
                            <?= $reservation['party_size'] ?> persona<?= $reservation['party_size'] > 1 ? 's' : '' ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($reservation['notes']): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Notas Especiales:</label>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($reservation['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Fecha de Creación:</label>
                    <div><?= date('d/m/Y H:i:s', strtotime($reservation['created_at'])) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Customer Details -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person"></i> Información del Cliente
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre:</label>
                    <div>
                        <i class="bi bi-person-fill"></i>
                        <?= htmlspecialchars($reservation['customer_name']) ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Teléfono:</label>
                    <div>
                        <a href="tel:<?= htmlspecialchars($reservation['customer_phone']) ?>" class="text-decoration-none">
                            <i class="bi bi-telephone"></i>
                            <?= htmlspecialchars($reservation['customer_phone']) ?>
                        </a>
                    </div>
                </div>
                
                <?php if ($reservation['customer_birthday']): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Cumpleaños:</label>
                    <div>
                        <i class="bi bi-gift"></i>
                        <?= date('d \de F', strtotime($reservation['customer_birthday'])) ?>
                        
                        <?php 
                        $today = new DateTime();
                        $birthday = new DateTime($reservation['customer_birthday']);
                        $birthday->setDate($today->format('Y'), $birthday->format('m'), $birthday->format('d'));
                        
                        if ($birthday->format('m-d') === $today->format('m-d')): ?>
                            <span class="badge bg-warning text-dark ms-2">¡Hoy es su cumpleaños!</span>
                        <?php elseif ($birthday > $today && $birthday->diff($today)->days <= 7): ?>
                            <span class="badge bg-info ms-2">Cumpleaños próximo</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Actions for Status Change -->
        <?php if (in_array($reservation['status'], ['pendiente', 'confirmada'])): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Acciones
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($reservation['status'] === 'pendiente'): ?>
                    <form method="POST" action="<?= BASE_URL ?>/reservations/updateStatus/<?= $reservation['id'] ?>">
                        <input type="hidden" name="status" value="confirmada">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Confirmar Reservación
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if ($reservation['status'] === 'confirmada'): ?>
                    <form method="POST" action="<?= BASE_URL ?>/reservations/updateStatus/<?= $reservation['id'] ?>">
                        <input type="hidden" name="status" value="completada">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check2-all"></i> Marcar como Completada
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= BASE_URL ?>/reservations/updateStatus/<?= $reservation['id'] ?>" 
                          onsubmit="return confirm('¿Está seguro de cancelar esta reservación?')">
                        <input type="hidden" name="status" value="cancelada">
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-circle"></i> Cancelar Reservación
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

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

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>