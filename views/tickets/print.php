<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket <?= htmlspecialchars($ticket['ticket_number']) ?></title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .ticket-info {
            margin-bottom: 15px;
        }
        
        .ticket-info div {
            margin-bottom: 3px;
        }
        
        .items-table {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            margin: 15px 0;
            padding: 5px 0;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .item-name {
            flex: 1;
            font-weight: bold;
        }
        
        .item-qty {
            width: 30px;
            text-align: center;
        }
        
        .item-price {
            width: 60px;
            text-align: right;
        }
        
        .item-total {
            width: 70px;
            text-align: right;
        }
        
        .item-notes {
            font-size: 10px;
            color: #666;
            margin-left: 10px;
            font-style: italic;
        }
        
        .totals {
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .final-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #000;
            font-size: 10px;
        }
        
        .print-button {
            margin: 20px 0;
            text-align: center;
        }
        
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button onclick="window.print()" class="btn">
            🖨️ Imprimir Ticket
        </button>
        <a href="<?= BASE_URL ?>/tickets/show/<?= $ticket['id'] ?>" class="btn" style="background-color: #6c757d;">
            ← Volver al Ticket
        </a>
    </div>

    <div class="header">
        <div class="company-name"><?= APP_NAME ?></div>
        <div>Sistema de Restaurante</div>
    </div>
    
    <div class="ticket-info">
        <div><strong>TICKET: <?= htmlspecialchars($ticket['ticket_number']) ?></strong></div>
        <div>Mesa: <?= $ticket['table_number'] ?></div>
        <div>Mesero: <?= htmlspecialchars($ticket['waiter_name']) ?></div>
        <div>Cajero: <?= htmlspecialchars($ticket['cashier_name']) ?></div>
        <div>Fecha: <?= date('d/m/Y H:i:s', strtotime($ticket['created_at'])) ?></div>
        <div>Pago: <?= getPaymentMethodText($ticket['payment_method']) ?></div>
    </div>
    
    <?php if (!empty($ticket['order_notes'])): ?>
    <div style="margin-bottom: 15px; padding: 5px; border: 1px solid #ccc;">
        <strong>Notas del Pedido:</strong><br>
        <?= nl2br(htmlspecialchars($ticket['order_notes'])) ?>
    </div>
    <?php endif; ?>
    
    <div class="items-table">
        <div class="item-row" style="font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 5px;">
            <div class="item-name">PLATILLO</div>
            <div class="item-qty">CANT</div>
            <div class="item-price">PRECIO</div>
            <div class="item-total">TOTAL</div>
        </div>
        
        <?php if (!empty($ticket['items'])): ?>
            <?php foreach ($ticket['items'] as $item): ?>
            <div class="item-row">
                <div class="item-name"><?= htmlspecialchars($item['dish_name']) ?></div>
                <div class="item-qty"><?= $item['quantity'] ?></div>
                <div class="item-price">$<?= number_format($item['unit_price'], 2) ?></div>
                <div class="item-total">$<?= number_format($item['subtotal'], 2) ?></div>
            </div>
            <?php if (!empty($item['notes'])): ?>
            <div class="item-notes">* <?= htmlspecialchars($item['notes']) ?></div>
            <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="totals">
        <div class="total-row">
            <div>Subtotal:</div>
            <div>$<?= number_format($ticket['subtotal'], 2) ?></div>
        </div>
        <div class="total-row">
            <div>IVA (16%):</div>
            <div>$<?= number_format($ticket['tax'], 2) ?></div>
        </div>
        <div class="total-row final-total">
            <div>TOTAL:</div>
            <div>$<?= number_format($ticket['total'], 2) ?></div>
        </div>
    </div>
    
    <div class="footer">
        <div>¡Gracias por su visita!</div>
        <div>Conserve este ticket</div>
        <div style="margin-top: 10px;">
            Ticket generado el <?= date('d/m/Y H:i') ?>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>

<?php
function getPaymentMethodText($method) {
    $methods = [
        'efectivo' => 'EFECTIVO',
        'tarjeta' => 'TARJETA',
        'transferencia' => 'TRANSFERENCIA',
        'intercambio' => 'INTERCAMBIO',
        'pendiente_por_cobrar' => 'PENDIENTE POR COBRAR'
    ];
    
    return $methods[$method] ?? strtoupper($method);
}
?>