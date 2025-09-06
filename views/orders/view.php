<?php $title = 'Detalles del Pedido #' . $order['id']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-clipboard-check"></i> Pedido #<?= $order['id'] ?></h1>
    <div>
        <?php if ($order['status'] !== ORDER_DELIVERED): ?>
        <a href="<?= BASE_URL ?>/orders/edit/<?= $order['id'] ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Pedidos
        </a>
    </div>
</div>

<div class="row">
    <!-- Order Details -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Información del Pedido
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Mesa:</label>
                    <span class="badge bg-info fs-6">Mesa <?= $order['table_number'] ?></span>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Mesero:</label>
                    <div>
                        <?= htmlspecialchars($order['waiter_name'] ?? '') ?><br>
                        <small class="text-muted"><?= htmlspecialchars($order['employee_code'] ?? '') ?></small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Estado:</label>
                    <div>
                        <span class="badge status-<?= $order['status'] ?> fs-6">
                            <?= getOrderStatusText($order['status']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Fecha de Creación:</label>
                    <div><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></div>
                </div>
                
                <?php if ($order['updated_at'] !== $order['created_at']): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Última Actualización:</label>
                    <div><?= date('d/m/Y H:i:s', strtotime($order['updated_at'])) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($order['notes'])): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Notas:</label>
                    <div class="alert alert-light mb-0">
                        <?= nl2br(htmlspecialchars($order['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Status Update -->
        <?php if ($order['status'] !== ORDER_DELIVERED): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Cambiar Estado
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/orders/updateStatus/<?= $order['id'] ?>">
                    <div class="mb-3">
                        <select class="form-select" name="status" required>
                            <option value="">Seleccionar estado...</option>
                            <option value="<?= ORDER_PENDING ?>" <?= $order['status'] === ORDER_PENDING ? 'selected' : '' ?>>
                                Pendiente
                            </option>
                            <option value="<?= ORDER_PREPARING ?>" <?= $order['status'] === ORDER_PREPARING ? 'selected' : '' ?>>
                                En Preparación
                            </option>
                            <option value="<?= ORDER_READY ?>" <?= $order['status'] === ORDER_READY ? 'selected' : '' ?>>
                                Listo
                            </option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle"></i> Actualizar Estado
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Order Items -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list"></i> Items del Pedido
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($items)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-exclamation-circle display-4 text-muted"></i>
                        <p class="text-muted mt-3">No hay items en este pedido</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Platillo</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grandTotal = 0;
                                foreach ($items as $item): 
                                    $grandTotal += $item['subtotal'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($item['dish_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($item['category']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= $item['quantity'] ?></span>
                                    </td>
                                    <td>
                                        $<?= number_format($item['unit_price'], 2) ?>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($item['subtotal'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['notes'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($item['notes']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th>$<?= number_format($grandTotal, 2) ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.status-pendiente { background-color: #ffc107; color: #000; }
.status-en_preparacion { background-color: #fd7e14; color: #fff; }
.status-listo { background-color: #198754; color: #fff; }
.status-entregado { background-color: #0dcaf0; color: #000; }
</style>

<?php
function getOrderStatusText($status) {
    $statusTexts = [
        ORDER_PENDING => 'Pendiente',
        ORDER_PREPARING => 'En Preparación',
        ORDER_READY => 'Listo',
        ORDER_DELIVERED => 'Entregado'
    ];
    
    return $statusTexts[$status] ?? $status;
}
?>