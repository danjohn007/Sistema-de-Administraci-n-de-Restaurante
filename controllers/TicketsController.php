<?php
class TicketsController extends BaseController {
    private $ticketModel;
    private $orderModel;
    private $tableModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->ticketModel = new Ticket();
        $this->orderModel = new Order();
        $this->tableModel = new Table();
    }
    
    public function index() {
        $user = $this->getCurrentUser();
        $filters = [];
        
        // Filter by cashier for non-admin users
        if ($user['role'] === ROLE_CASHIER) {
            $filters['cashier_id'] = $user['id'];
        }
        
        // Get date filter from request
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $tickets = $this->ticketModel->getTicketsByDate($date, $filters['cashier_id'] ?? null);
        $salesReport = $this->ticketModel->getDailySalesReport($date);
        
        $this->view('tickets/index', [
            'tickets' => $tickets,
            'salesReport' => $salesReport,
            'selectedDate' => $date,
            'user' => $user
        ]);
    }
    
    public function create() {
        $user = $this->getCurrentUser();
        
        // Only cashiers and admins can create tickets
        if (!in_array($user['role'], [ROLE_CASHIER, ROLE_ADMIN])) {
            $this->redirect('tickets', 'error', 'No tienes permisos para generar tickets');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
        } else {
            // Get orders that are ready and don't have tickets yet
            $readyOrders = $this->getOrdersReadyForTicket();
            
            $this->view('tickets/create', [
                'orders' => $readyOrders,
                'user' => $user
            ]);
        }
    }
    
    public function show($id) {
        $ticket = $this->ticketModel->getTicketWithDetails($id);
        if (!$ticket) {
            $this->redirect('tickets', 'error', 'Ticket no encontrado');
            return;
        }
        
        $user = $this->getCurrentUser();
        
        // Check permissions for cashiers
        if ($user['role'] === ROLE_CASHIER && $ticket['cashier_id'] != $user['id']) {
            $this->redirect('tickets', 'error', 'No tienes permisos para ver este ticket');
            return;
        }
        
        $this->view('tickets/view', [
            'ticket' => $ticket
        ]);
    }
    
    public function print($id) {
        $ticket = $this->ticketModel->getTicketWithDetails($id);
        if (!$ticket) {
            $this->redirect('tickets', 'error', 'Ticket no encontrado');
            return;
        }
        
        $user = $this->getCurrentUser();
        
        // Check permissions for cashiers
        if ($user['role'] === ROLE_CASHIER && $ticket['cashier_id'] != $user['id']) {
            $this->redirect('tickets', 'error', 'No tienes permisos para imprimir este ticket');
            return;
        }
        
        $this->view('tickets/print', [
            'ticket' => $ticket
        ]);
    }
    
    public function delete($id) {
        $ticket = $this->ticketModel->find($id);
        if (!$ticket) {
            $this->redirect('tickets', 'error', 'Ticket no encontrado');
            return;
        }
        
        $user = $this->getCurrentUser();
        
        // Only admins can delete tickets
        if ($user['role'] !== ROLE_ADMIN) {
            $this->redirect('tickets', 'error', 'No tienes permisos para eliminar tickets');
            return;
        }
        
        try {
            $this->ticketModel->delete($id);
            $this->redirect('tickets', 'success', 'Ticket eliminado correctamente');
        } catch (Exception $e) {
            $this->redirect('tickets', 'error', 'Error al eliminar el ticket: ' . $e->getMessage());
        }
    }
    
    public function report() {
        $user = $this->getCurrentUser();
        
        // Only admins and cashiers can view reports
        if (!in_array($user['role'], [ROLE_ADMIN, ROLE_CASHIER])) {
            $this->redirect('tickets', 'error', 'No tienes permisos para ver reportes');
            return;
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        // Get sales data for the date range
        $salesData = $this->getSalesReportData($startDate, $endDate);
        
        $this->view('tickets/report', [
            'salesData' => $salesData,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    private function processCreate() {
        $errors = $this->validateTicketInput($_POST);
        
        if (!empty($errors)) {
            $readyOrders = $this->getOrdersReadyForTicket();
            $this->view('tickets/create', [
                'errors' => $errors,
                'old' => $_POST,
                'orders' => $readyOrders
            ]);
            return;
        }
        
        $user = $this->getCurrentUser();
        $orderId = $_POST['order_id'];
        $paymentMethod = $_POST['payment_method'] ?? 'efectivo';
        
        try {
            $ticketId = $this->ticketModel->createTicket($orderId, $user['id'], $paymentMethod);
            $this->redirect('tickets/show/' . $ticketId, 'success', 'Ticket generado correctamente');
        } catch (Exception $e) {
            $readyOrders = $this->getOrdersReadyForTicket();
            $this->view('tickets/create', [
                'error' => 'Error al generar el ticket: ' . $e->getMessage(),
                'old' => $_POST,
                'orders' => $readyOrders
            ]);
        }
    }
    
    private function validateTicketInput($data) {
        $errors = $this->validateInput($data, [
            'order_id' => ['required' => true],
            'payment_method' => ['required' => true]
        ]);
        
        // Validate payment method
        $validMethods = ['efectivo', 'tarjeta', 'transferencia'];
        if (!in_array($data['payment_method'] ?? '', $validMethods)) {
            $errors['payment_method'] = 'Método de pago inválido';
        }
        
        // Validate that order exists and is ready
        if (!empty($data['order_id'])) {
            $order = $this->orderModel->find($data['order_id']);
            if (!$order) {
                $errors['order_id'] = 'El pedido seleccionado no existe';
            } elseif ($order['status'] !== ORDER_READY) {
                $errors['order_id'] = 'El pedido debe estar en estado "Listo" para generar el ticket';
            } else {
                // Check if order already has a ticket
                $existingTicket = $this->ticketModel->findBy('order_id', $data['order_id']);
                if ($existingTicket) {
                    $errors['order_id'] = 'Este pedido ya tiene un ticket generado';
                }
            }
        }
        
        return $errors;
    }
    
    private function getOrdersReadyForTicket() {
        $query = "SELECT o.*, t.number as table_number, 
                         u.name as waiter_name, w.employee_code
                  FROM orders o
                  JOIN tables t ON o.table_id = t.id
                  JOIN waiters w ON o.waiter_id = w.id
                  JOIN users u ON w.user_id = u.id
                  LEFT JOIN tickets tk ON o.id = tk.order_id
                  WHERE o.status = ? AND tk.id IS NULL
                  ORDER BY o.created_at ASC";
        
        $stmt = $this->orderModel->db->prepare($query);
        $stmt->execute([ORDER_READY]);
        
        return $stmt->fetchAll();
    }
    
    private function getSalesReportData($startDate, $endDate) {
        $query = "SELECT 
                    DATE(t.created_at) as date,
                    COUNT(*) as total_tickets,
                    SUM(t.subtotal) as total_subtotal,
                    SUM(t.tax) as total_tax,
                    SUM(t.total) as total_amount,
                    t.payment_method,
                    COUNT(*) as method_count
                  FROM tickets t
                  WHERE DATE(t.created_at) BETWEEN ? AND ?
                  GROUP BY DATE(t.created_at), t.payment_method
                  ORDER BY DATE(t.created_at) DESC, t.payment_method";
        
        $stmt = $this->ticketModel->db->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        
        return $stmt->fetchAll();
    }
}
?>