<?php $title = 'Cancelar Ticket'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-x-circle text-danger"></i> Cancelar Ticket</h1>
    <a href="<?= BASE_URL ?>/tickets" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Tickets
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-receipt"></i> Información del Ticket
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Número de Ticket:</strong><br>
                        <span class="text-primary"><?= htmlspecialchars($ticket['ticket_number']) ?></span>
                    </div>
                    <div class="col-sm-6">
                        <strong>Mesa:</strong><br>
                        Mesa <?= htmlspecialchars($ticket['table_number'] ?? 'N/A') ?>
                    </div>
                    <div class="col-sm-6 mt-3">
                        <strong>Mesero:</strong><br>
                        <?= htmlspecialchars($ticket['waiter_name'] ?? 'N/A') ?>
                    </div>
                    <div class="col-sm-6 mt-3">
                        <strong>Cajero:</strong><br>
                        <?= htmlspecialchars($ticket['cashier_name'] ?? 'N/A') ?>
                    </div>
                    <div class="col-sm-6 mt-3">
                        <strong>Método de Pago:</strong><br>
                        <span class="badge bg-secondary"><?= ucfirst($ticket['payment_method']) ?></span>
                    </div>
                    <div class="col-sm-6 mt-3">
                        <strong>Fecha:</strong><br>
                        <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
                    </div>
                </div>
                
                <hr>
                
                <h6><i class="bi bi-list-ul"></i> Detalles del Pedido</h6>
                <?php if (!empty($ticket['items'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Platillo</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ticket['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($item['dish_name']) ?>
                                        <?php if (!empty($item['notes'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($item['notes']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end">$<?= number_format($item['unit_price'], 2) ?></td>
                                    <td class="text-end">$<?= number_format($item['subtotal'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <div class="row mt-3">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Subtotal:</strong></td>
                                <td class="text-end">$<?= number_format($ticket['subtotal'], 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>IVA (16%):</strong></td>
                                <td class="text-end">$<?= number_format($ticket['tax'], 2) ?></td>
                            </tr>
                            <tr class="table-active">
                                <td><strong>Total:</strong></td>
                                <td class="text-end"><strong>$<?= number_format($ticket['total'], 2) ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-exclamation-triangle"></i> Confirmar Cancelación
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    <strong>¡Atención!</strong><br>
                    Esta acción cancelará el ticket y:
                    <ul class="mb-0 mt-2">
                        <li>Revertirá el estado del pedido a "Listo"</li>
                        <li>Ajustará las estadísticas del cliente</li>
                        <li>Revertirá el inventario (si aplica)</li>
                        <li>Descontará el ingreso del sistema</li>
                    </ul>
                </div>
                
                <form method="POST" action="<?= BASE_URL ?>/tickets/cancel/<?= $ticket['id'] ?>">
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">
                            <strong>Motivo de Cancelación *</strong>
                        </label>
                        <textarea class="form-control" 
                                  id="cancellation_reason" 
                                  name="cancellation_reason" 
                                  rows="4" 
                                  required 
                                  placeholder="Explique el motivo de la cancelación..."></textarea>
                        <div class="form-text">
                            El motivo de cancelación es obligatorio y quedará registrado permanentemente.
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea cancelar este ticket? Esta acción no se puede deshacer.')">
                            <i class="bi bi-x-circle"></i> Cancelar Ticket
                        </button>
                        <a href="<?= BASE_URL ?>/tickets" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar Acción
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>