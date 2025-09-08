<?php
class InventoryController extends BaseController {
    private $productModel;
    private $movementModel;
    private $systemSettingsModel;
    private $dishIngredientModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
        $this->movementModel = new InventoryMovement();
        $this->systemSettingsModel = new SystemSettings();
        $this->dishIngredientModel = new DishIngredient();
    }
    
    public function index() {
        $this->requireRole([ROLE_ADMIN, ROLE_SUPERADMIN, ROLE_CASHIER]);
        
        // Verificar si el inventario está habilitado
        if (!$this->systemSettingsModel->isInventoryEnabled()) {
            $this->setFlashMessage('warning', 'El módulo de inventarios está deshabilitado');
            $this->redirect('dashboard');
        }
        
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        
        if ($search || $category) {
            $products = $this->productModel->searchProducts($search, $category);
        } else {
            $products = $this->productModel->getProductsWithStock();
        }
        
        $categories = $this->productModel->getCategories();
        $lowStockProducts = $this->productModel->getLowStockProducts();
        $inventoryValue = $this->productModel->getInventoryValue();
        
        $data = [
            'products' => $products,
            'categories' => $categories,
            'lowStockProducts' => $lowStockProducts,
            'inventoryValue' => $inventoryValue,
            'search' => $search,
            'selected_category' => $category
        ];
        
        $this->view('inventory/index', $data);
    }
    
    public function create() {
        $this->requireRole([ROLE_ADMIN, ROLE_SUPERADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateProductInput($_POST);
            
            if (empty($errors)) {
                $data = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'] ?? '',
                    'category' => $_POST['category'] ?? '',
                    'unit_measure' => $_POST['unit_measure'] ?? 'unidad',
                    'current_stock' => $_POST['current_stock'] ?? 0,
                    'min_stock' => $_POST['min_stock'] ?? 0,
                    'max_stock' => $_POST['max_stock'] ?? 0,
                    'cost_per_unit' => $_POST['cost_per_unit'] ?? 0,
                    'is_dish_ingredient' => isset($_POST['is_dish_ingredient']) ? 1 : 0
                ];
                
                if ($this->productModel->create($data)) {
                    $this->setFlashMessage('success', 'Producto agregado correctamente');
                    $this->redirect('inventory');
                } else {
                    $this->setFlashMessage('error', 'Error al agregar el producto');
                }
            } else {
                $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            }
        }
        
        $categories = $this->productModel->getCategories();
        $this->view('inventory/create', ['categories' => $categories, 'old' => $_POST ?? [], 'errors' => $errors ?? []]);
    }
    
    public function edit($id) {
        $this->requireRole([ROLE_ADMIN, ROLE_SUPERADMIN]);
        
        $product = $this->productModel->find($id);
        if (!$product) {
            $this->setFlashMessage('error', 'Producto no encontrado');
            $this->redirect('inventory');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateProductInput($_POST, $id);
            
            if (empty($errors)) {
                $data = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'] ?? '',
                    'category' => $_POST['category'] ?? '',
                    'unit_measure' => $_POST['unit_measure'] ?? 'unidad',
                    'min_stock' => $_POST['min_stock'] ?? 0,
                    'max_stock' => $_POST['max_stock'] ?? 0,
                    'cost_per_unit' => $_POST['cost_per_unit'] ?? 0,
                    'is_dish_ingredient' => isset($_POST['is_dish_ingredient']) ? 1 : 0
                ];
                
                if ($this->productModel->update($id, $data)) {
                    $this->setFlashMessage('success', 'Producto actualizado correctamente');
                    $this->redirect('inventory');
                } else {
                    $this->setFlashMessage('error', 'Error al actualizar el producto');
                }
            } else {
                $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            }
        }
        
        $categories = $this->productModel->getCategories();
        $this->view('inventory/edit', [
            'product' => $product,
            'categories' => $categories,
            'errors' => $errors ?? []
        ]);
    }
    
    public function show($id) {
        $this->requireRole([ROLE_ADMIN, ROLE_SUPERADMIN, ROLE_CASHIER]);
        
        $product = $this->productModel->find($id);
        if (!$product) {
            $this->setFlashMessage('error', 'Producto no encontrado');
            $this->redirect('inventory');
        }
        
        $movements = $this->movementModel->getMovementsByProduct($id, 100);
        $dishes = $this->dishIngredientModel->getDishesByIngredient($id);
        
        $data = [
            'product' => $product,
            'movements' => $movements,
            'dishes' => $dishes
        ];
        
        $this->view('inventory/show', $data);
    }
    
    public function movements() {
        $this->requireRole([ROLE_ADMIN, ROLE_SUPERADMIN, ROLE_CASHIER]);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $movementType = $_GET['movement_type'] ?? '';
        $productId = $_GET['product_id'] ?? '';
        
        $conditions = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        if ($movementType) {
            $conditions['movement_type'] = $movementType;
        }
        
        if ($productId) {
            $conditions['product_id'] = $productId;
        }
        
        $movements = $this->movementModel->getMovementsWithDetails($conditions);
        $products = $this->productModel->getAll();
        
        $data = [
            'movements' => $movements,
            'products' => $products,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'movement_type' => $movementType,
            'product_id' => $productId
        ];
        
        $this->view('inventory/movements', $data);
    }
    
    public function addMovement() {
        $this->requireRole([ROLE_ADMIN, ROLE_SUPERADMIN, ROLE_CASHIER]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateMovementInput($_POST);
            
            if (empty($errors)) {
                try {
                    $user = $this->getCurrentUser();
                    
                    $data = [
                        'product_id' => $_POST['product_id'],
                        'movement_type' => $_POST['movement_type'],
                        'quantity' => $_POST['quantity'],
                        'cost_per_unit' => $_POST['cost_per_unit'] ?? 0,
                        'reference_type' => REFERENCE_TYPE_MANUAL,
                        'description' => $_POST['description'] ?? 'Ajuste manual de inventario',
                        'user_id' => $user['id'],
                        'movement_date' => $_POST['movement_date'] ?? date('Y-m-d H:i:s')
                    ];
                    
                    $this->movementModel->createMovement($data);
                    $this->setFlashMessage('success', 'Movimiento registrado correctamente');
                    $this->redirect('inventory/movements');
                    
                } catch (Exception $e) {
                    $this->setFlashMessage('error', 'Error: ' . $e->getMessage());
                }
            } else {
                $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            }
        }
        
        $products = $this->productModel->getAll();
        $this->view('inventory/add_movement', [
            'products' => $products,
            'old' => $_POST ?? [],
            'errors' => $errors ?? []
        ]);
    }
    
    public function report() {
        $this->requireRole([ROLE_ADMIN, ROLE_SUPERADMIN, ROLE_CASHIER]);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $report = $this->movementModel->getInventoryReport($dateFrom, $dateTo);
        $lowStockProducts = $this->productModel->getLowStockProducts();
        $inventoryValue = $this->productModel->getInventoryValue();
        
        $data = [
            'report' => $report,
            'lowStockProducts' => $lowStockProducts,
            'inventoryValue' => $inventoryValue,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        $this->view('inventory/report', $data);
    }
    
    public function settings() {
        $this->requireRole([ROLE_SUPERADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = [
                'collections_enabled' => isset($_POST['collections_enabled']) ? '1' : '0',
                'inventory_enabled' => isset($_POST['inventory_enabled']) ? '1' : '0',
                'auto_deduct_inventory' => isset($_POST['auto_deduct_inventory']) ? '1' : '0'
            ];
            
            if ($this->systemSettingsModel->updateModuleSettings($settings)) {
                $this->setFlashMessage('success', 'Configuraciones actualizadas correctamente');
            } else {
                $this->setFlashMessage('error', 'Error al actualizar las configuraciones');
            }
        }
        
        $settings = $this->systemSettingsModel->getModuleSettings();
        $this->view('inventory/settings', ['settings' => $settings]);
    }
    
    private function validateProductInput($data, $excludeId = null) {
        $errors = $this->validateInput($data, [
            'name' => ['required' => true, 'max_length' => 255],
            'unit_measure' => ['required' => true, 'max_length' => 50],
            'min_stock' => ['required' => true, 'type' => 'numeric'],
            'max_stock' => ['required' => true, 'type' => 'numeric'],
            'cost_per_unit' => ['required' => true, 'type' => 'numeric']
        ]);
        
        // Validar que el nombre no exista
        if (!empty($data['name']) && $this->productModel->nameExists($data['name'], $excludeId)) {
            $errors['name'] = 'Ya existe un producto con este nombre';
        }
        
        // Validar que el stock máximo sea mayor al mínimo
        if (!empty($data['min_stock']) && !empty($data['max_stock'])) {
            if (floatval($data['max_stock']) <= floatval($data['min_stock'])) {
                $errors['max_stock'] = 'El stock máximo debe ser mayor al stock mínimo';
            }
        }
        
        return $errors;
    }
    
    private function validateMovementInput($data) {
        $errors = $this->validateInput($data, [
            'product_id' => ['required' => true, 'type' => 'numeric'],
            'movement_type' => ['required' => true],
            'quantity' => ['required' => true, 'type' => 'numeric'],
            'movement_date' => ['required' => true]
        ]);
        
        // Validar que el producto exista
        if (!empty($data['product_id'])) {
            $product = $this->productModel->find($data['product_id']);
            if (!$product) {
                $errors['product_id'] = 'Producto no encontrado';
            }
        }
        
        // Validar tipo de movimiento
        $validTypes = [MOVEMENT_TYPE_IN, MOVEMENT_TYPE_OUT];
        if (!empty($data['movement_type']) && !in_array($data['movement_type'], $validTypes)) {
            $errors['movement_type'] = 'Tipo de movimiento inválido';
        }
        
        // Validar cantidad positiva
        if (!empty($data['quantity']) && floatval($data['quantity']) <= 0) {
            $errors['quantity'] = 'La cantidad debe ser mayor a cero';
        }
        
        return $errors;
    }
}
?>