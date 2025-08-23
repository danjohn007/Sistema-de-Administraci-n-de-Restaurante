<?php
class OrdersController extends BaseController {
    private $orderModel;
    private $tableModel;
    private $dishModel;
    private $waiterModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->orderModel = new Order();
        $this->tableModel = new Table();
        $this->dishModel = new Dish();
        $this->waiterModel = new Waiter();
    }
    
    public function index() {
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
            $orders = $this->orderModel->getOrdersWithDetails($filters);
        }
        
        $this->view('orders/index', [
            'orders' => $orders,
            'user' => $user
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
            
            // Get waiters for admin role
            $waiters = [];
            if ($user['role'] === ROLE_ADMIN) {
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
        
        // Check permissions
        if ($user['role'] === ROLE_WAITER) {
            $waiter = $this->waiterModel->findBy('user_id', $user['id']);
            if (!$waiter || $order['waiter_id'] != $waiter['id']) {
                $this->redirect('orders', 'error', 'No tienes permisos para editar este pedido');
                return;
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEdit($id);
        } else {
            $orderItems = $this->orderModel->getOrderItems($id);
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            
            $this->view('orders/edit', [
                'order' => $order,
                'items' => $orderItems,
                'dishes' => $dishes
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
            $validStatuses = [ORDER_PENDING, ORDER_PREPARING, ORDER_READY, ORDER_DELIVERED];
            
            if (!in_array($status, $validStatuses)) {
                $this->redirect('orders', 'error', 'Estado inválido');
                return;
            }
            
            try {
                $this->orderModel->updateOrderStatus($id, $status);
                $this->redirect('orders', 'success', 'Estado del pedido actualizado');
            } catch (Exception $e) {
                $this->redirect('orders', 'error', 'Error al actualizar el estado: ' . $e->getMessage());
            }
        } else {
            $this->redirect('orders/show/' . $id);
        }
    }
    
    public function table($tableId = null) {
        if (!$tableId) {
            $this->redirect('orders', 'error', 'ID de mesa requerido');
            return;
        }
        
        $table = $this->tableModel->find($tableId);
        if (!$table) {
            $this->redirect('orders', 'error', 'Mesa no encontrada');
            return;
        }
        
        $user = $this->getCurrentUser();
        
        // Check permissions for waiters
        if ($user['role'] === ROLE_WAITER) {
            $waiter = $this->waiterModel->findBy('user_id', $user['id']);
            if (!$waiter || $table['waiter_id'] != $waiter['id']) {
                $this->redirect('orders', 'error', 'No tienes permisos para ver los pedidos de esta mesa');
                return;
            }
        }
        
        $orders = $this->orderModel->getOrdersWithDetails(['table_id' => $tableId]);
        
        $this->view('orders/table', [
            'table' => $table,
            'orders' => $orders
        ]);
    }
    
    private function processCreate() {
        $errors = $this->validateOrderInput($_POST);
        
        if (!empty($errors)) {
            $user = $this->getCurrentUser();
            $waiters = [];
            if ($user['role'] === ROLE_ADMIN) {
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
            if ($user['role'] === ROLE_ADMIN) {
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
            if ($user['role'] === ROLE_ADMIN) {
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
            
            $this->view('orders/edit', [
                'errors' => $errors,
                'order' => $order,
                'items' => $orderItems,
                'dishes' => $dishes,
                'old' => $_POST
            ]);
            return;
        }
        
        try {
            // Update order notes
            $orderData = [
                'notes' => $_POST['notes'] ?? null
            ];
            
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
            }
            
            $this->redirect('orders/show/' . $id, 'success', 'Pedido actualizado correctamente');
        } catch (Exception $e) {
            $order = $this->orderModel->find($id);
            $orderItems = $this->orderModel->getOrderItems($id);
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            
            $this->view('orders/edit', [
                'error' => 'Error al actualizar el pedido: ' . $e->getMessage(),
                'order' => $order,
                'items' => $orderItems,
                'dishes' => $dishes,
                'old' => $_POST
            ]);
        }
    }
    
    private function validateOrderInput($data, $excludeId = null) {
        $errors = $this->validateInput($data, [
            'table_id' => ['required' => true]
        ]);
        
        // Validate waiter for admin users
        $user = $this->getCurrentUser();
        if ($user['role'] === ROLE_ADMIN && empty($data['waiter_id'])) {
            $errors['waiter_id'] = 'Debe seleccionar un mesero';
        }
        
        return $errors;
    }
}
?>