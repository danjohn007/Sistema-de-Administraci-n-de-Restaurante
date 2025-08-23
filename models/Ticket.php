<?php
class Ticket extends BaseModel {
    protected $table = 'tickets';
    
    public function generateTicketNumber() {
        $date = date('Ymd');
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        $sequential = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
        return "T{$date}{$sequential}";
    }
    
    public function createTicket($orderId, $cashierId, $paymentMethod = 'efectivo') {
        try {
            $this->db->beginTransaction();
            
            // Get order details
            $orderModel = new Order();
            $order = $orderModel->find($orderId);
            
            if (!$order) {
                throw new Exception('Orden no encontrada');
            }
            
            // Calculate totals
            $subtotal = $order['total'];
            $tax = $subtotal * 0.16; // 16% IVA
            $total = $subtotal + $tax;
            
            // Create ticket
            $ticketData = [
                'order_id' => $orderId,
                'ticket_number' => $this->generateTicketNumber(),
                'cashier_id' => $cashierId,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $paymentMethod
            ];
            
            $ticketId = $this->create($ticketData);
            
            if (!$ticketId) {
                throw new Exception('Error al crear el ticket');
            }
            
            // Update order status
            $orderModel->updateOrderStatus($orderId, ORDER_DELIVERED);
            
            // Update table status
            $tableModel = new Table();
            $tableModel->updateTableStatus($order['table_id'], TABLE_CLOSED);
            
            $this->db->commit();
            return $ticketId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function getTicketWithDetails($ticketId) {
        $query = "SELECT t.*, o.table_id, o.waiter_id, o.notes as order_notes,
                         tb.number as table_number,
                         w.employee_code,
                         u_waiter.name as waiter_name,
                         u_cashier.name as cashier_name
                  FROM {$this->table} t
                  JOIN orders o ON t.order_id = o.id
                  JOIN tables tb ON o.table_id = tb.id
                  JOIN waiters w ON o.waiter_id = w.id
                  JOIN users u_waiter ON w.user_id = u_waiter.id
                  JOIN users u_cashier ON t.cashier_id = u_cashier.id
                  WHERE t.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId]);
        
        $ticket = $stmt->fetch();
        
        if ($ticket) {
            // Get order items
            $orderModel = new Order();
            $ticket['items'] = $orderModel->getOrderItems($ticket['order_id']);
        }
        
        return $ticket;
    }
    
    public function getTicketsByDate($date = null, $cashierId = null) {
        $date = $date ?: date('Y-m-d');
        
        $query = "SELECT t.*, o.table_id, tb.number as table_number,
                         u.name as cashier_name
                  FROM {$this->table} t
                  JOIN orders o ON t.order_id = o.id
                  JOIN tables tb ON o.table_id = tb.id
                  JOIN users u ON t.cashier_id = u.id
                  WHERE DATE(t.created_at) = ?";
        
        $params = [$date];
        
        if ($cashierId) {
            $query .= " AND t.cashier_id = ?";
            $params[] = $cashierId;
        }
        
        $query .= " ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getDailySalesReport($date = null) {
        $date = $date ?: date('Y-m-d');
        
        $query = "SELECT 
                    COUNT(*) as total_tickets,
                    SUM(subtotal) as total_subtotal,
                    SUM(tax) as total_tax,
                    SUM(total) as total_amount,
                    payment_method,
                    COUNT(*) as method_count
                  FROM {$this->table}
                  WHERE DATE(created_at) = ?
                  GROUP BY payment_method";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$date]);
        
        $results = $stmt->fetchAll();
        
        // Get overall totals
        $totalQuery = "SELECT 
                        COUNT(*) as total_tickets,
                        SUM(subtotal) as total_subtotal,
                        SUM(tax) as total_tax,
                        SUM(total) as total_amount
                       FROM {$this->table}
                       WHERE DATE(created_at) = ?";
        
        $stmt = $this->db->prepare($totalQuery);
        $stmt->execute([$date]);
        $totals = $stmt->fetch();
        
        return [
            'by_payment_method' => $results,
            'totals' => $totals
        ];
    }
    
    public function getSalesReportData($startDate, $endDate) {
        $query = "SELECT 
                    DATE(t.created_at) as date,
                    COUNT(*) as total_tickets,
                    SUM(t.subtotal) as total_subtotal,
                    SUM(t.tax) as total_tax,
                    SUM(t.total) as total_amount,
                    t.payment_method,
                    COUNT(*) as method_count
                  FROM {$this->table} t
                  WHERE DATE(t.created_at) BETWEEN ? AND ?
                  GROUP BY DATE(t.created_at), t.payment_method
                  ORDER BY DATE(t.created_at) DESC, t.payment_method";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        
        return $stmt->fetchAll();
    }
}
?>