<?php
class PublicController extends BaseController {
    private $dishModel;
    private $tableModel;
    private $orderModel;
    
    public function __construct() {
        parent::__construct();
        $this->dishModel = new Dish();
        $this->tableModel = new Table();
        $this->orderModel = new Order();
    }
    
    public function menu() {
        $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
        $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
        
        $this->viewPublic('public/menu', [
            'dishes' => $dishes,
            'tables' => $tables
        ]);
    }
    
    public function order() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processPublicOrder();
        } else {
            $this->redirect('public/menu');
        }
    }
    
    private function processPublicOrder() {
        $errors = $this->validatePublicOrderInput($_POST);
        
        if (!empty($errors)) {
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
            
            $this->viewPublic('public/menu', [
                'errors' => $errors,
                'old' => $_POST,
                'dishes' => $dishes,
                'tables' => $tables
            ]);
            return;
        }
        
        // Process items
        $items = [];
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                if (isset($item['quantity']) && $item['quantity'] > 0) {
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
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
            
            $this->viewPublic('public/menu', [
                'errors' => ['items' => 'Debe agregar al menos un platillo al pedido'],
                'old' => $_POST,
                'dishes' => $dishes,
                'tables' => $tables
            ]);
            return;
        }
        
        // Create order data
        $orderData = [
            'table_id' => $_POST['table_id'],
            'waiter_id' => null, // Public orders don't have waiter assigned yet
            'status' => 'pendiente_confirmacion', // New status for public orders
            'notes' => $_POST['notes'] ?? null,
            'customer_name' => $_POST['customer_name'],
            'customer_phone' => $_POST['customer_phone'],
            'is_pickup' => isset($_POST['is_pickup']) ? 1 : 0,
            'pickup_datetime' => isset($_POST['pickup_datetime']) ? $_POST['pickup_datetime'] : null
        ];
        
        try {
            $orderId = $this->orderModel->createPublicOrderWithItems($orderData, $items);
            
            // Don't update table status for pickup orders
            if (!$orderData['is_pickup']) {
                $this->tableModel->update($_POST['table_id'], ['status' => TABLE_OCCUPIED]);
            }
            
            $this->viewPublic('public/order_success', [
                'order_id' => $orderId,
                'is_pickup' => $orderData['is_pickup']
            ]);
        } catch (Exception $e) {
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
            
            $this->viewPublic('public/menu', [
                'error' => 'Error al crear el pedido: ' . $e->getMessage(),
                'old' => $_POST,
                'dishes' => $dishes,
                'tables' => $tables
            ]);
        }
    }
    
    private function validatePublicOrderInput($data) {
        $errors = $this->validateInput($data, [
            'customer_name' => ['required' => true, 'max' => 255],
            'customer_phone' => ['required' => true, 'max' => 20],
            'table_id' => ['required' => true]
        ]);
        
        // Validate pickup datetime if pickup is selected
        if (isset($data['is_pickup']) && !empty($data['pickup_datetime'])) {
            $pickupTime = strtotime($data['pickup_datetime']);
            $now = time();
            
            // Minimum 30 minutes advance notice
            $minTime = $now + (30 * 60); // 30 minutes from now
            
            // Maximum 30 days advance
            $maxTime = $now + (30 * 24 * 60 * 60); // 30 days from now
            
            if ($pickupTime <= $minTime) {
                $errors['pickup_datetime'] = 'La fecha y hora de pickup debe ser al menos 30 minutos en adelante';
            } elseif ($pickupTime > $maxTime) {
                $errors['pickup_datetime'] = 'La fecha y hora de pickup no puede ser más de 30 días en adelante';
            }
        } elseif (isset($data['is_pickup']) && empty($data['pickup_datetime'])) {
            $errors['pickup_datetime'] = 'Debe seleccionar fecha y hora para pedidos pickup';
        }
        
        return $errors;
    }
    
    protected function viewPublic($viewName, $data = []) {
        // Extract data variables
        extract($data);
        
        // Include public header
        include BASE_PATH . '/views/layouts/public_header.php';
        
        // Include the view
        $viewPath = BASE_PATH . '/views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            include BASE_PATH . '/views/errors/404.php';
        }
        
        // Include public footer
        include BASE_PATH . '/views/layouts/public_footer.php';
    }
}
?>