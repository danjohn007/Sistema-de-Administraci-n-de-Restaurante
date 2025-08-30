<?php $title = 'Confirmar Pedido Público'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-check-circle"></i> Confirmar Pedido Público</h1>
    <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Pedidos
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Información del Pedido #<?= $order['id'] ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Datos del Cliente</h6>
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($order['customer_name'] ?? 'No especificado') ?></p>
                        <p><strong>Teléfono:</strong> <?= htmlspecialchars($order['customer_phone'] ?? 'No especificado') ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Detalles del Pedido</h6>
                        <p><strong>Mesa:</strong> Mesa #<?= $order['table_id'] ?></p>
                        <p><strong>Tipo:</strong> 
                            <?php if ($order['is_pickup'] ?? false): ?>
                                <span class="badge bg-info">Para Llevar</span>
                                <?php if ($order['pickup_datetime']): ?>
                                    <br><small>Pickup: <?= date('d/m/Y H:i', strtotime($order['pickup_datetime'])) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-primary">Para Mesa</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Estado:</strong> <span class="badge bg-warning">Pendiente de Confirmación</span></p>
                        <p><strong>Total:</strong> $<?= number_format($order['total'] ?? 0, 2) ?></p>
                    </div>
                </div>
                
                <?php if (!empty($order['notes'])): ?>
                <div class="mt-3">
                    <h6>Notas del Pedido</h6>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-check"></i> Asignar Mesero
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/orders/confirmPublicOrder/<?= $order['id'] ?>">
                    <div class="mb-3">
                        <label for="waiter_id" class="form-label">Seleccionar Mesero *</label>
                        <select class="form-select" id="waiter_id" name="waiter_id" required>
                            <option value="">Seleccionar mesero...</option>
                            <?php foreach ($waiters as $waiter): ?>
                                <option value="<?= $waiter['id'] ?>">
                                    <?= htmlspecialchars($waiter['name']) ?> 
                                    (<?= htmlspecialchars($waiter['employee_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Al confirmar, el pedido cambiará a estado "Pendiente" y se asignará al mesero seleccionado.
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Confirmar Pedido
                        </button>
                        <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>