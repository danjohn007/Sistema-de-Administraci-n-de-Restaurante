<?php
class OrdersController extends BaseController {
    private $orderModel;
    private $tableModel;
    private $dishModel;
    private $waiterModel;
    private $customerModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->orderModel = new Order();
        $this->tableModel = new Table();
        $this->dishModel = new Dish();
        $this->waiterModel = new Waiter();
        $this->customerModel = new Customer();
    }
    
    public function index() {
        $user = $this->getCurrentUser();
        $filters = [];
        
        // Check if we're showing future orders
        $showFuture = isset($_GET['future']) && $_GET['future'] == '1';
        
        // Filter by waiter for non-admin users
        if ($user['role'] === ROLE_WAITER) {
            $waiter = $this->waiterModel->findBy('user_id', $user['id']);
            if ($waiter) {
                $filters['waiter_id'] = $waiter['id'];
            } else {
                // User is not a waiter, show empty list
                $orders = [];
            }
        }
        
        // Add search filter
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        if (!isset($orders)) {
            if ($showFuture) {
                $orders = $this->orderModel->getFuturePickupOrders($filters);
            } else {
                $orders = $this->orderModel->getTodaysOrders($filters);
            }
        }
        
        $this->view('orders/index', [
            'orders' => $orders,
            'user' => $user,
            'showFuture' => $showFuture
        ]);
    }
    
    public function create() {
        $user = $this->getCurrentUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
        } else {
            // Get available tables for waiters, or all tables for admins
            if ($user['role'] === ROLE_WAITER) {
                $waiter = $this->waiterModel->findBy('user_id', $user['id']);
                $tables = $waiter ? $this->tableModel->getWaiterTables($waiter['id']) : [];
            } else {
                $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            }
            
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            
            // Get waiters for admin and cashier roles
            $waiters = [];
            if ($user['role'] === ROLE_ADMIN || $user['role'] === ROLE_CASHIER) {
                $waiters = $this->waiterModel->getWaitersWithUsers();
            }
            
            $this->view('orders/create', [
                'tables' => $tables,
                'dishes' => $dishes,
                'waiters' => $waiters,
                'user' => $user
            ]);
        }
    }
    
    public function show($id) {
        $order = $this->orderModel->find($id);
        if (!$order) {
            $this->redirect('orders', 'error', 'Pedido no encontrado');
            return;
        }
        
        $user = $this->getCurrentUser();
        
        // Check permissions
        if ($user['role'] === ROLE_WAITER) {
            $waiter = $this->waiterModel->findBy('user_id', $user['id']);
            if (!$waiter || $order['waiter_id'] != $waiter['id']) {
                $this->redirect('orders', 'error', 'No tienes permisos para ver este pedido');
                return;
            }
        }
        
        $orderDetails = $this->orderModel->getOrdersWithDetails(['id' => $id]);
        $orderItems = $this->orderModel->getOrderItems($id);
        
        $this->view('orders/view', [
            'order' => $orderDetails[0] ?? $order,
            'items' => $orderItems
        ]);
    }
    
    public function edit($id) {
        $order = $this->orderModel->find($id);
        if (!$order) {
            $this->redirect('orders', 'error', 'Pedido no encontrado');
            return;
        }
        
        $user = $this->getCurrentUser();
        
        // Check permissions - only waiters have restrictions, admins and cashiers can edit any order
        if ($user['role'] === ROLE_WAITER) {
            $waiter = $this->waiterModel->findBy('user_id', $user['id']);
            if (!$waiter || $order['waiter_id'] != $waiter['id']) {
                $this->redirect('orders', 'error', 'No tienes permisos para editar este pedido');
                return;
            }
        }
        // Admin and cashier can edit any order
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEdit($id);
        } else {
            $orderItems = $this->orderModel->getOrderItems($id);
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            
            $this->view('orders/edit', [
                'order' => $order,
                'items' => $orderItems,
                'dishes' => $dishes,
                'tables' => $tables
            ]);
        }
    }
    
    public function delete($id) {
        $order = $this->orderModel->find($id);
        if (!$order) {
            $this->redirect('orders', 'error', 'Pedido no encontrado');
            return;
        }
        
        $user = $this->getCurrentUser();
        
        // Only admins can delete orders
        if ($user['role'] !== ROLE_ADMIN) {
            $this->redirect('orders', 'error', 'No tienes permisos para eliminar pedidos');
            return;
        }
        
        try {
            $this->orderModel->delete($id);
            $this->redirect('orders', 'success', 'Pedido eliminado correctamente');
        } catch (Exception $e) {
            $this->redirect('orders', 'error', 'Error al eliminar el pedido: ' . $e->getMessage());
        }
    }
    
    public function updateStatus($id) {
        $order = $this->orderModel->find($id);
        if (!$order) {
            $this->redirect('orders', 'error', 'Pedido no encontrado');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';
            $validStatuses = [ORDER_PENDING_CONFIRMATION, ORDER_PENDING, ORDER_PREPARING, ORDER_READY, ORDER_DELIVERED];
            
            if (!in_array($status, $validStatuses)) {
                $this->redirect('orders', 'error', 'Estado inválido');
                return;
            }
            
            try {
                $this->orderModel->updateOrderStatusAndCustomerStats($id, $status);
                $this->redirect('orders/show/' . $id, 'success', 'Estado del pedido actualizado');
            } catch (Exception $e) {
                $this->redirect('orders/show/' . $id, 'error', 'Error al actualizar el estado: ' . $e->getMessage());
            }
        } else {
            $this->redirect('orders/show/' . $id);
        }
    }
    
    public function confirmPublicOrder($id) {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $order = $this->orderModel->find($id);
        if (!$order) {
            $this->redirect('orders', 'error', 'Pedido no encontrado');
            return;
        }
        
        if ($order['status'] !== ORDER_PENDING_CONFIRMATION) {
            $this->redirect('orders', 'error', 'Solo se pueden confirmar pedidos pendientes de confirmación');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $waiterId = $_POST['waiter_id'] ?? null;
            
            if (empty($waiterId)) {
                $this->redirect('orders', 'error', 'Debe seleccionar un mesero para confirmar el pedido');
                return;
            }
            
            try {
                $this->orderModel->update($id, [
                    'waiter_id' => $waiterId,
                    'status' => ORDER_PENDING
                ]);
                
                $this->redirect('orders', 'success', 'Pedido público confirmado y asignado correctamente');
            } catch (Exception $e) {
                $this->redirect('orders', 'error', 'Error al confirmar el pedido: ' . $e->getMessage());
            }
        } else {
            $waiters = $this->waiterModel->getWaitersWithUsers();
            
            $this->view('orders/confirm_public', [
                'order' => $order,
                'waiters' => $waiters
            ]);
        }
    }
    
    public function table($tableId = null) {
        if (!$tableId) {
            $this->redirect('orders', 'error', 'ID de mesa requerido');
            return;
        }
        
        // Get table with waiter information
        $query = "SELECT t.*, w.employee_code, u.name as waiter_name 
                  FROM tables t 
                  LEFT JOIN waiters w ON t.waiter_id = w.id 
                  LEFT JOIN users u ON w.user_id = u.id 
                  WHERE t.id = ? AND t.active = 1";
        
        $stmt = $this->tableModel->db->prepare($query);
        $stmt->execute([$tableId]);
        $table = $stmt->fetch();
        
        if (!$table) {
            $this->redirect('orders', 'error', 'Mesa no encontrada');
            return;
        }
        
        // Get orders for today for this table
        $orders = $this->orderModel->getOrdersWithDetails([
            'table_id' => $tableId,
            'date' => date('Y-m-d')
        ]);
        
        $this->view('orders/table', [
            'table' => $table,
            'orders' => $orders
        ]);
    }
    
    public function futureOrders() {
        $user = $this->getCurrentUser();
        $filters = [];
        
        // Filter by waiter for non-admin users
        if ($user['role'] === ROLE_WAITER) {
            $waiter = $this->waiterModel->findBy('user_id', $user['id']);
            if ($waiter) {
                $filters['waiter_id'] = $waiter['id'];
            } else {
                // User is not a waiter, show empty list
                $orders = [];
            }
        }
        
        if (!isset($orders)) {
            $orders = $this->orderModel->getFuturePickupOrders($filters);
        }
        
        $this->view('orders/future', [
            'orders' => $orders,
            'user' => $user
        ]);
    }
    
    private function processCreate() {
        $errors = $this->validateOrderInput($_POST);
        
        if (!empty($errors)) {
            $user = $this->getCurrentUser();
            $waiters = [];
            if ($user['role'] === ROLE_ADMIN || $user['role'] === ROLE_CASHIER) {
                $waiters = $this->waiterModel->getWaitersWithUsers();
            }
            
            $this->view('orders/create', [
                'errors' => $errors,
                'old' => $_POST,
                'tables' => $this->tableModel->findAll(['active' => 1], 'number ASC'),
                'dishes' => $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC'),
                'waiters' => $waiters,
                'user' => $user
            ]);
            return;
        }
        
        $user = $this->getCurrentUser();
        $waiterId = null;
        
        if ($user['role'] === ROLE_WAITER) {
            $waiter = $this->waiterModel->findBy('user_id', $user['id']);
            $waiterId = $waiter['id'];
        } else {
            $waiterId = $_POST['waiter_id'];
        }
        
        $orderData = [
            'table_id' => $_POST['table_id'],
            'waiter_id' => $waiterId,
            'status' => ORDER_PENDING,
            'notes' => $_POST['notes'] ?? null
        ];
        
        // Handle customer assignment
        $customerId = null;
        if (!empty($_POST['customer_id'])) {
            // Existing customer selected
            $customerId = $_POST['customer_id'];
        } elseif (!empty($_POST['new_customer_name']) && !empty($_POST['new_customer_phone'])) {
            // Create new customer
            try {
                $customerData = [
                    'name' => trim($_POST['new_customer_name']),
                    'phone' => trim($_POST['new_customer_phone'])
                ];
                
                // Validate customer data before creation
                if (empty($customerData['name']) || empty($customerData['phone'])) {
                    throw new Exception('Nombre y teléfono del cliente son requeridos');
                }
                
                $customerId = $this->customerModel->findOrCreateByPhone($customerData);
                
                if (!$customerId) {
                    throw new Exception('No se pudo crear o encontrar el cliente en la base de datos');
                }
                
            } catch (Exception $e) {
                // Show error to user instead of silently failing
                $user = $this->getCurrentUser();
                $waiters = [];
                if ($user['role'] === ROLE_ADMIN || $user['role'] === ROLE_CASHIER) {
                    $waiters = $this->waiterModel->getWaitersWithUsers();
                }
                
                $this->view('orders/create', [
                    'error' => 'Error al registrar cliente: ' . $e->getMessage(),
                    'old' => $_POST,
                    'tables' => $this->tableModel->findAll(['active' => 1], 'number ASC'),
                    'dishes' => $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC'),
                    'waiters' => $waiters,
                    'user' => $user
                ]);
                return;
            }
        }
        
        if ($customerId) {
            $orderData['customer_id'] = $customerId;
        }
        
        $items = [];
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                if ($item['quantity'] > 0) {
                    $dish = $this->dishModel->find($item['dish_id']);
                    if ($dish) {
                        $items[] = [
                            'dish_id' => $item['dish_id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $dish['price'],
                            'notes' => $item['notes'] ?? null
                        ];
                    }
                }
            }
        }
        
        if (empty($items)) {
            $user = $this->getCurrentUser();
            $waiters = [];
            if ($user['role'] === ROLE_ADMIN || $user['role'] === ROLE_CASHIER) {
                $waiters = $this->waiterModel->getWaitersWithUsers();
            }
            
            $this->view('orders/create', [
                'errors' => ['items' => 'Debe agregar al menos un platillo al pedido'],
                'old' => $_POST,
                'tables' => $this->tableModel->findAll(['active' => 1], 'number ASC'),
                'dishes' => $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC'),
                'waiters' => $waiters,
                'user' => $user
            ]);
            return;
        }
        
        try {
            $orderId = $this->orderModel->createOrderWithItems($orderData, $items);
            
            // Update table status to occupied
            $this->tableModel->update($_POST['table_id'], ['status' => TABLE_OCCUPIED]);
            
            $this->redirect('orders/show/' . $orderId, 'success', 'Pedido creado correctamente');
        } catch (Exception $e) {
            $user = $this->getCurrentUser();
            $waiters = [];
            if ($user['role'] === ROLE_ADMIN || $user['role'] === ROLE_CASHIER) {
                $waiters = $this->waiterModel->getWaitersWithUsers();
            }
            
            $this->view('orders/create', [
                'error' => 'Error al crear el pedido: ' . $e->getMessage(),
                'old' => $_POST,
                'tables' => $this->tableModel->findAll(['active' => 1], 'number ASC'),
                'dishes' => $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC'),
                'waiters' => $waiters,
                'user' => $user
            ]);
        }
    }
    
    private function processEdit($id) {
        $errors = $this->validateOrderInput($_POST, $id);
        
        if (!empty($errors)) {
            $order = $this->orderModel->find($id);
            $orderItems = $this->orderModel->getOrderItems($id);
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            
            $this->view('orders/edit', [
                'errors' => $errors,
                'order' => $order,
                'items' => $orderItems,
                'dishes' => $dishes,
                'tables' => $tables,
                'old' => $_POST
            ]);
            return;
        }
        
        try {
            // Prepare order data for update
            $orderData = [
                'notes' => $_POST['notes'] ?? null
            ];
            
            // Only update table_id if provided and valid
            if (isset($_POST['table_id']) && !empty($_POST['table_id'])) {
                $orderData['table_id'] = $_POST['table_id'];
            }
            
            $this->orderModel->update($id, $orderData);
            
            // Process new items if any
            if (isset($_POST['new_items']) && is_array($_POST['new_items'])) {
                foreach ($_POST['new_items'] as $item) {
                    if (isset($item['dish_id']) && isset($item['quantity']) && $item['quantity'] > 0) {
                        $dish = $this->dishModel->find($item['dish_id']);
                        if ($dish) {
                            $this->orderModel->addItemToOrder(
                                $id,
                                $item['dish_id'],
                                $item['quantity'],
                                $dish['price'],
                                $item['notes'] ?? null
                            );
                        }
                    }
                }
                
                // Ensure order total is updated after adding new items
                $this->orderModel->updateOrderTotal($id);
            }
            
            // Update table status if table was changed
            if (isset($_POST['table_id']) && !empty($_POST['table_id'])) {
                $order = $this->orderModel->find($id);
                // If the table changed, update the table status
                if ($order['table_id'] != $_POST['table_id']) {
                    // Set new table as occupied
                    $this->tableModel->updateTableStatus($_POST['table_id'], TABLE_OCCUPIED);
                    
                    // If old table exists and no other active orders, free it
                    if ($order['table_id']) {
                        $activeOrders = $this->orderModel->findAll([
                            'table_id' => $order['table_id'],
                            'status' => [ORDER_PENDING, ORDER_PREPARING, ORDER_READY]
                        ]);
                        if (count($activeOrders) <= 1) { // Only current order
                            $this->tableModel->updateTableStatus($order['table_id'], TABLE_AVAILABLE);
                        }
                    }
                }
            }
            
            $this->redirect('orders/show/' . $id, 'success', 'Pedido actualizado correctamente');
        } catch (Exception $e) {
            $order = $this->orderModel->find($id);
            $orderItems = $this->orderModel->getOrderItems($id);
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            
            $this->view('orders/edit', [
                'error' => 'Error al actualizar el pedido: ' . $e->getMessage(),
                'order' => $order,
                'items' => $orderItems,
                'dishes' => $dishes,
                'tables' => $tables,
                'old' => $_POST
            ]);
        }
    }
    
    private function validateOrderInput($data, $excludeId = null) {
        $errors = [];
        
        // Only require table_id for internal orders or pickup orders
        // For public orders (non-pickup), table assignment is optional
        $isPublicOrder = isset($data['is_public_order']) && $data['is_public_order'];
        $isPickup = isset($data['is_pickup']) && $data['is_pickup'];
        
        // If we're editing an existing order, check if it's a public order
        if ($excludeId) {
            $order = $this->orderModel->find($excludeId);
            if ($order) {
                $isPublicOrder = !empty($order['customer_name']) || !empty($order['customer_phone']);
                $isPickup = $order['is_pickup'] ?? false;
            }
        }
        
        // Table is required for internal orders and pickup orders
        if (!$isPublicOrder || $isPickup) {
            $errors = $this->validateInput($data, [
                'table_id' => ['required' => true]
            ]);
        }
        
        // Validate waiter for admin and cashier users
        $user = $this->getCurrentUser();
        if (($user['role'] === ROLE_ADMIN || $user['role'] === ROLE_CASHIER) && empty($data['waiter_id'])) {
            $errors['waiter_id'] = 'Debe seleccionar un mesero';
        }
        
        // Validate customer selection (mandatory)
        $hasExistingCustomer = !empty($data['customer_id']);
        $hasNewCustomer = !empty($data['new_customer_name']) && !empty($data['new_customer_phone']);
        
        if (!$hasExistingCustomer && !$hasNewCustomer) {
            $errors['customer_id'] = 'Debe seleccionar un cliente existente o crear uno nuevo';
        }
        
        // Validate new customer data if provided
        if (!$hasExistingCustomer && !empty($data['new_customer_name']) && empty($data['new_customer_phone'])) {
            $errors['customer_id'] = 'Debe proporcionar el teléfono del cliente';
        }
        
        if (!$hasExistingCustomer && empty($data['new_customer_name']) && !empty($data['new_customer_phone'])) {
            $errors['customer_id'] = 'Debe proporcionar el nombre del cliente';
        }
        
        return $errors;
    }
    
    public function removeItem($itemId) {
        $orderItemModel = new OrderItem();
        $item = $orderItemModel->find($itemId);
        
        if (!$item) {
            $this->redirect('orders', 'error', 'Item no encontrado');
            return;
        }
        
        $orderId = $item['order_id'];
        $order = $this->orderModel->find($orderId);
        
        if (!$order) {
            $this->redirect('orders', 'error', 'Pedido no encontrado');
            return;
        }
        
        $user = $this->getCurrentUser();
        
        // Check permissions - only waiters have restrictions, admins and cashiers can edit any order
        if ($user['role'] === ROLE_WAITER) {
            $waiter = $this->waiterModel->findBy('user_id', $user['id']);
            if (!$waiter || $order['waiter_id'] != $waiter['id']) {
                $this->redirect('orders', 'error', 'No tienes permisos para editar este pedido');
                return;
            }
        }
        
        try {
            $this->orderModel->removeItemFromOrder($itemId);
            $this->redirect('orders/edit/' . $orderId, 'success', 'Item eliminado correctamente');
        } catch (Exception $e) {
            $this->redirect('orders/edit/' . $orderId, 'error', 'Error al eliminar el item: ' . $e->getMessage());
        }
    }
    
    public function searchCustomers() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $query = $data['query'] ?? '';
        
        if (empty($query) || strlen($query) < 2) {
            echo json_encode(['customers' => []]);
            return;
        }
        
        try {
            $customers = $this->customerModel->searchCustomers($query);
            header('Content-Type: application/json');
            echo json_encode(['customers' => $customers]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error searching customers: ' . $e->getMessage()]);
        }
    }
    
    // ============= EXPIRED ORDERS MANAGEMENT =============
    
    public function expiredOrders() {
        $user = $this->getCurrentUser();
        $filters = [];
        
        // Filter by waiter for waiter users only
        if ($user['role'] === ROLE_WAITER) {
            $waiter = $this->waiterModel->findBy('user_id', $user['id']);
            if ($waiter) {
                $filters['waiter_id'] = $waiter['id'];
            } else {
                // User is not a waiter, show empty list
                $orders = [];
            }
        }
        // Admin and cashier can see all expired orders
        
        if (!isset($orders)) {
            $orders = $this->orderModel->getExpiredOrders($filters);
        }
        
        $this->view('orders/expired', [
            'orders' => $orders,
            'user' => $user
        ]);
    }
}
?>