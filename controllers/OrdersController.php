<?php
class OrdersController extends BaseController {
    private $orderModel;
    private $waiterModel;
    private $tableModel;
    private $dishModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->orderModel = new Order();
        $this->waiterModel = new Waiter();
        $this->tableModel = new Table();
        $this->dishModel = new Dish();
    }
    
    public function index() {
        $orders = $this->orderModel->getOrdersWithDetails();
        
        $this->view('orders/index', [
            'title' => 'Gestión de Pedidos',
            'orders' => $orders
        ]);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreateOrder();
        } else {
            // Get available tables and waiters for the form
            $availableTables = $this->tableModel->getAvailableTables();
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $dishes = $this->dishModel->findAll(['active' => 1], 'category, name');
            
            $this->view('orders/create', [
                'title' => 'Nuevo Pedido',
                'tables' => $availableTables,
                'waiters' => $waiters,
                'dishes' => $dishes
            ]);
        }
    }
    
    private function processCreateOrder() {
        try {
            // Validate required fields
            $tableId = (int)($_POST['table_id'] ?? 0);
            $waiterId = (int)($_POST['waiter_id'] ?? 0);
            $items = $_POST['items'] ?? [];
            
            if (!$tableId || !$waiterId || empty($items)) {
                throw new Exception('Todos los campos son requeridos');
            }
            
            // Validate table is available
            $table = $this->tableModel->find($tableId);
            if (!$table || $table['status'] !== 'disponible') {
                throw new Exception('La mesa seleccionada no está disponible');
            }
            
            // Validate waiter exists
            $waiter = $this->waiterModel->find($waiterId);
            if (!$waiter || !$waiter['active']) {
                throw new Exception('El mesero seleccionado no es válido');
            }
            
            // Prepare order data
            $orderData = [
                'table_id' => $tableId,
                'waiter_id' => $waiterId,
                'status' => 'pendiente',
                'notes' => trim($_POST['notes'] ?? '')
            ];
            
            // Process items
            $processedItems = [];
            foreach ($items as $item) {
                if ((int)$item['quantity'] > 0) {
                    $dish = $this->dishModel->find($item['dish_id']);
                    if ($dish) {
                        $processedItems[] = [
                            'dish_id' => $item['dish_id'],
                            'quantity' => (int)$item['quantity'],
                            'unit_price' => $dish['price'],
                            'notes' => trim($item['notes'] ?? '')
                        ];
                    }
                }
            }
            
            if (empty($processedItems)) {
                throw new Exception('Debe agregar al menos un platillo al pedido');
            }
            
            // Create order with items
            $orderId = $this->orderModel->createOrderWithItems($orderData, $processedItems);
            
            // Update table status
            $this->tableModel->updateTableStatus($tableId, 'ocupada', $waiterId);
            
            $this->redirect('orders', 'success', 'Pedido creado correctamente');
            
        } catch (Exception $e) {
            // Get data for form again
            $availableTables = $this->tableModel->getAvailableTables();
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $dishes = $this->dishModel->findAll(['active' => 1], 'category, name');
            
            $this->view('orders/create', [
                'title' => 'Nuevo Pedido',
                'tables' => $availableTables,
                'waiters' => $waiters,
                'dishes' => $dishes,
                'error' => $e->getMessage(),
                'old' => $_POST
            ]);
        }
    }
    
    public function table($tableId = null) {
        // Get orders for a specific table
        if (!$tableId) {
            $this->redirect('orders', 'error', 'ID de mesa requerido');
            return;
        }
        
        $table = $this->tableModel->find($tableId);
        if (!$table) {
            $this->redirect('orders', 'error', 'Mesa no encontrada');
            return;
        }
        
        $orders = $this->orderModel->getOrdersWithDetails(['table_id' => $tableId]);
        
        $this->view('orders/table', [
            'title' => 'Pedidos de Mesa ' . $table['number'],
            'table' => $table,
            'orders' => $orders
        ]);
    }
    
    public function edit($id) {
        // Placeholder for order editing
        $this->view('orders/edit', [
            'title' => 'Editar Pedido',
            'order_id' => $id
        ]);
    }
    
    public function show($id) {
        $order = $this->orderModel->find($id);
        if (!$order) {
            $this->redirect('orders', 'error', 'Pedido no encontrado');
            return;
        }
        
        $orderItems = $this->orderModel->getOrderItems($id);
        
        $this->view('orders/view', [
            'title' => 'Detalles del Pedido',
            'order' => $order,
            'items' => $orderItems
        ]);
    }
}
?>