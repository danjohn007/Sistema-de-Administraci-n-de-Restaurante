<?php
class TicketsController extends BaseController {
    private $ticketModel;
    private $orderModel;
    private $tableModel;
    private $systemSettingsModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->ticketModel = new Ticket();
        $this->orderModel = new Order();
        $this->tableModel = new Table();
        $this->systemSettingsModel = new SystemSettings();
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
        
        // Get search filters
        $searchFilters = [];
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchFilters['search'] = $_GET['search'];
        }
        
        $tickets = $this->ticketModel->getTicketsByDate($date, $filters['cashier_id'] ?? null, $searchFilters);
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
            // Get ready orders grouped by table
            $tablesWithReadyOrders = $this->orderModel->getReadyOrdersGroupedByTable();
            
            $this->view('tickets/create', [
                'tables' => $tablesWithReadyOrders,
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
            $tablesWithReadyOrders = $this->orderModel->getReadyOrdersGroupedByTable();
            $this->view('tickets/create', [
                'errors' => $errors,
                'old' => $_POST,
                'tables' => $tablesWithReadyOrders
            ]);
            return;
        }
        
        $user = $this->getCurrentUser();
        $paymentMethod = $_POST['payment_method'] ?? 'efectivo';
        
        try {
            // Check if we're creating a ticket for multiple orders or single order
            if (isset($_POST['table_id'])) {
                // Multiple orders from a table
                $tableId = $_POST['table_id'];
                
                // Get all ready orders for this table
                $readyOrders = $this->orderModel->getOrdersReadyForTicket();
                $tableOrders = array_filter($readyOrders, function($order) use ($tableId) {
                    return $order['table_id'] == $tableId;
                });
                
                if (empty($tableOrders)) {
                    throw new Exception('No hay pedidos listos para esta mesa');
                }
                
                $orderIds = array_map(function($order) { return $order['id']; }, $tableOrders);
                $ticketId = $this->ticketModel->createTicketFromMultipleOrders($orderIds, $user['id'], $paymentMethod);
            } else {
                // Single order (backward compatibility)
                $orderId = $_POST['order_id'];
                $ticketId = $this->ticketModel->createTicket($orderId, $user['id'], $paymentMethod);
            }
            
            $this->redirect('tickets/show/' . $ticketId, 'success', 'Ticket generado correctamente');
        } catch (Exception $e) {
            $tablesWithReadyOrders = $this->orderModel->getReadyOrdersGroupedByTable();
            $this->view('tickets/create', [
                'error' => 'Error al generar el ticket: ' . $e->getMessage(),
                'old' => $_POST,
                'tables' => $tablesWithReadyOrders
            ]);
        }
    }
    
    private function validateTicketInput($data) {
        $errors = $this->validateInput($data, [
            'payment_method' => ['required' => true]
        ]);
        
        // Get available payment methods based on system settings
        $systemSettingsModel = new SystemSettings();
        $validMethods = ['efectivo', 'tarjeta', 'transferencia', 'intercambio'];
        
        // Add collections method only if enabled
        if ($systemSettingsModel->isCollectionsEnabled()) {
            $validMethods[] = 'pendiente_por_cobrar';
        }
        
        // Validate payment method
        if (!in_array($data['payment_method'] ?? '', $validMethods)) {
            $errors['payment_method'] = 'Método de pago inválido o no disponible';
        }
        
        // Special validation for collections
        if ($data['payment_method'] === 'pendiente_por_cobrar' && !$systemSettingsModel->isCollectionsEnabled()) {
            $errors['payment_method'] = 'Las cuentas por cobrar están deshabilitadas';
        }
        
        // Validate that either table_id or order_id is provided
        if (empty($data['table_id']) && empty($data['order_id'])) {
            $errors['selection'] = 'Debe seleccionar una mesa o un pedido para generar el ticket';
        }
        
        // Validate table selection (for multiple orders)
        if (!empty($data['table_id'])) {
            $tableId = $data['table_id'];
            
            // Check that the table has ready orders
            $readyOrders = $this->orderModel->getOrdersReadyForTicket();
            $tableOrders = array_filter($readyOrders, function($order) use ($tableId) {
                return $order['table_id'] == $tableId;
            });
            
            if (empty($tableOrders)) {
                $errors['table_id'] = 'La mesa seleccionada no tiene pedidos listos para generar ticket';
            }
        }
        
        // Validate single order selection (backward compatibility)
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
        return $this->orderModel->getOrdersReadyForTicket();
    }
    
    private function getSalesReportData($startDate, $endDate) {
        return $this->ticketModel->getSalesReportData($startDate, $endDate);
    }
    
    public function pendingPayments() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        // Get search filters
        $searchFilters = [];
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchFilters['search'] = $_GET['search'];
        }
        
        // Get all tickets with payment method 'pendiente_por_cobrar'
        $pendingTickets = $this->ticketModel->getPendingPayments($searchFilters);
        
        $this->view('tickets/pending_payments', [
            'tickets' => $pendingTickets,
            'user' => $this->getCurrentUser()
        ]);
    }
    
    public function markAsPaid($ticketId) {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentMethod = $_POST['payment_method'] ?? 'efectivo';
            
            if ($this->ticketModel->updatePaymentMethod($ticketId, $paymentMethod)) {
                $this->redirect('tickets/pendingPayments', 'success', 'Pago marcado como cobrado');
            } else {
                $this->redirect('tickets/pendingPayments', 'error', 'Error al actualizar el pago');
            }
        }
    }
    
    public function createExpiredTicket() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $user = $this->getCurrentUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Get expired orders that are ready for ticket generation
            $expiredOrders = $this->orderModel->getExpiredOrdersReadyForTicket();
            
            $this->view('tickets/create_expired', [
                'orders' => $expiredOrders,
                'user' => $user
            ]);
        } else {
            // Handle form submission
            $errors = $this->validateExpiredTicketInput($_POST);
            
            if (!empty($errors)) {
                $expiredOrders = $this->orderModel->getExpiredOrdersReadyForTicket();
                $this->view('tickets/create_expired', [
                    'errors' => $errors,
                    'old' => $_POST,
                    'orders' => $expiredOrders,
                    'user' => $user
                ]);
                return;
            }
            
            try {
                $orderId = $_POST['order_id'];
                $paymentMethod = $_POST['payment_method'] ?? 'efectivo';
                
                $ticketId = $this->ticketModel->createExpiredOrderTicket($orderId, $user['id'], $paymentMethod);
                
                $this->redirect('tickets/show/' . $ticketId, 'success', 'Ticket de pedido vencido generado correctamente');
            } catch (Exception $e) {
                $expiredOrders = $this->orderModel->getExpiredOrdersReadyForTicket();
                $this->view('tickets/create_expired', [
                    'error' => 'Error al generar el ticket: ' . $e->getMessage(),
                    'old' => $_POST,
                    'orders' => $expiredOrders,
                    'user' => $user
                ]);
            }
        }
    }
    
    private function validateExpiredTicketInput($data) {
        $errors = $this->validateInput($data, [
            'order_id' => ['required' => true],
            'payment_method' => ['required' => true]
        ]);
        
        // Get available payment methods based on system settings
        $validMethods = ['efectivo', 'tarjeta', 'transferencia', 'intercambio'];
        
        // Add collections method only if enabled
        if ($this->systemSettingsModel->isCollectionsEnabled()) {
            $validMethods[] = 'pendiente_por_cobrar';
        }
        
        // Validate payment method
        if (!in_array($data['payment_method'] ?? '', $validMethods)) {
            $errors['payment_method'] = 'Método de pago inválido o no disponible';
        }
        
        // Special validation for collections
        if ($data['payment_method'] === 'pendiente_por_cobrar' && !$this->systemSettingsModel->isCollectionsEnabled()) {
            $errors['payment_method'] = 'Las cuentas por cobrar están deshabilitadas';
        }
        
        // Validate order exists and is ready
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
}
?>