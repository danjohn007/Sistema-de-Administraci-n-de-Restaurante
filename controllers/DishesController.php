<?php
class DishesController extends BaseController {
    private $dishModel;
    
    public function __construct() {
        parent::__construct();
        $this->dishModel = new Dish();
    }
    
    public function index() {
        $this->requireRole([ROLE_ADMIN, ROLE_WAITER]);
        
        $user = $this->getCurrentUser();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        // Build conditions
        $conditions = ['active' => 1];
        
        if ($categoryFilter) {
            $conditions['category'] = $categoryFilter;
        }
        
        // If search is provided, we need a custom query
        if ($search) {
            $query = "SELECT * FROM dishes WHERE active = 1";
            $params = [];
            
            if ($categoryFilter) {
                $query .= " AND category = ?";
                $params[] = $categoryFilter;
            }
            
            $query .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            
            $query .= " ORDER BY category ASC, name ASC";
            
            // Manual pagination for search
            $perPage = ITEMS_PER_PAGE;
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
            $stmt = $this->dishModel->db->prepare($countQuery);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];
            
            // Get data
            $query .= " LIMIT {$perPage} OFFSET {$offset}";
            $stmt = $this->dishModel->db->prepare($query);
            $stmt->execute($params);
            $dishes = $stmt->fetchAll();
            
            $result = [
                'data' => $dishes,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage),
                    'has_next' => $page < ceil($total / $perPage),
                    'has_prev' => $page > 1
                ]
            ];
        } else {
            $result = $this->dishModel->paginate($page, ITEMS_PER_PAGE, $conditions, 'category ASC, name ASC');
        }
        
        // Get categories for filter
        $categories = $this->dishModel->getCategories();
        
        $this->view('dishes/index', [
            'dishes' => $result['data'],
            'pagination' => $result['pagination'],
            'categories' => $categories,
            'categoryFilter' => $categoryFilter,
            'search' => $search,
            'user' => $user
        ]);
    }
    
    public function create() {
        $this->requireRole(ROLE_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
        } else {
            $categories = $this->dishModel->getCategories();
            $this->view('dishes/create', [
                'categories' => $categories
            ]);
        }
    }
    
    private function processCreate() {
        $errors = $this->validateDishInput($_POST);
        
        if (!empty($errors)) {
            $categories = $this->dishModel->getCategories();
            $this->view('dishes/create', [
                'errors' => $errors,
                'old' => $_POST,
                'categories' => $categories
            ]);
            return;
        }
        
        $dishData = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']) ?: null,
            'price' => (float)$_POST['price'],
            'category' => trim($_POST['category']) ?: null,
            'has_validity' => isset($_POST['has_validity']) ? 1 : 0,
            'validity_start' => null,
            'validity_end' => null,
            'availability_days' => '1234567' // Default: all days
        ];
        
        // Handle validity dates if validity is enabled
        if ($dishData['has_validity']) {
            if (!empty($_POST['validity_start'])) {
                $dishData['validity_start'] = $_POST['validity_start'];
            }
            if (!empty($_POST['validity_end'])) {
                $dishData['validity_end'] = $_POST['validity_end'];
            }
            
            // Handle availability days
            if (isset($_POST['availability_days']) && is_array($_POST['availability_days'])) {
                $dishData['availability_days'] = implode('', $_POST['availability_days']);
            }
        }
        
        try {
            $dishId = $this->dishModel->create($dishData);
            
            if ($dishId) {
                $this->redirect('dishes', 'success', 'Platillo creado correctamente');
            } else {
                throw new Exception('Error al crear el platillo');
            }
        } catch (Exception $e) {
            $categories = $this->dishModel->getCategories();
            $this->view('dishes/create', [
                'error' => 'Error al crear el platillo: ' . $e->getMessage(),
                'old' => $_POST,
                'categories' => $categories
            ]);
        }
    }
    
    public function edit($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $dish = $this->dishModel->find($id);
        if (!$dish || !$dish['active']) {
            $this->redirect('dishes', 'error', 'Platillo no encontrado');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEdit($id);
        } else {
            $categories = $this->dishModel->getCategories();
            $this->view('dishes/edit', [
                'dish' => $dish,
                'categories' => $categories
            ]);
        }
    }
    
    private function processEdit($id) {
        $errors = $this->validateDishInput($_POST, $id);
        
        $dish = $this->dishModel->find($id);
        
        if (!empty($errors)) {
            $categories = $this->dishModel->getCategories();
            $this->view('dishes/edit', [
                'errors' => $errors,
                'dish' => $dish,
                'old' => $_POST,
                'categories' => $categories
            ]);
            return;
        }
        
        $dishData = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']) ?: null,
            'price' => (float)$_POST['price'],
            'category' => trim($_POST['category']) ?: null,
            'has_validity' => isset($_POST['has_validity']) ? 1 : 0,
            'validity_start' => null,
            'validity_end' => null,
            'availability_days' => '1234567' // Default: all days
        ];
        
        // Handle validity dates if validity is enabled
        if ($dishData['has_validity']) {
            if (!empty($_POST['validity_start'])) {
                $dishData['validity_start'] = $_POST['validity_start'];
            }
            if (!empty($_POST['validity_end'])) {
                $dishData['validity_end'] = $_POST['validity_end'];
            }
            
            // Handle availability days
            if (isset($_POST['availability_days']) && is_array($_POST['availability_days'])) {
                $dishData['availability_days'] = implode('', $_POST['availability_days']);
            }
        }
        
        try {
            $success = $this->dishModel->update($id, $dishData);
            
            if ($success) {
                $this->redirect('dishes', 'success', 'Platillo actualizado correctamente');
            } else {
                throw new Exception('Error al actualizar el platillo');
            }
        } catch (Exception $e) {
            $categories = $this->dishModel->getCategories();
            $this->view('dishes/edit', [
                'error' => 'Error al actualizar el platillo: ' . $e->getMessage(),
                'dish' => $dish,
                'old' => $_POST,
                'categories' => $categories
            ]);
        }
    }
    
    public function delete($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $dish = $this->dishModel->find($id);
        if (!$dish || !$dish['active']) {
            $this->redirect('dishes', 'error', 'Platillo no encontrado');
            return;
        }
        
        try {
            $success = $this->dishModel->softDelete($id);
            
            if ($success) {
                $this->redirect('dishes', 'success', 'Platillo eliminado correctamente');
            } else {
                throw new Exception('Error al eliminar el platillo');
            }
        } catch (Exception $e) {
            $this->redirect('dishes', 'error', 'Error al eliminar el platillo: ' . $e->getMessage());
        }
    }
    
    public function categories() {
        $this->requireRole(ROLE_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processManageCategories();
        } else {
            $categories = $this->dishModel->getCategories();
            $dishesByCategory = $this->dishModel->getDishesByCategory();
            
            $this->view('dishes/categories', [
                'categories' => $categories,
                'dishesByCategory' => $dishesByCategory
            ]);
        }
    }
    
    private function processManageCategories() {
        $action = $_POST['action'] ?? '';
        $oldCategory = $_POST['old_category'] ?? '';
        $newCategory = trim($_POST['new_category'] ?? '');
        
        if ($action === 'rename' && $oldCategory && $newCategory) {
            try {
                $this->renameDishCategory($oldCategory, $newCategory);
                $this->redirect('dishes/categories', 'success', 'Categoría renombrada correctamente');
            } catch (Exception $e) {
                $this->redirect('dishes/categories', 'error', 'Error al renombrar categoría: ' . $e->getMessage());
            }
        } elseif ($action === 'delete' && $oldCategory) {
            try {
                $this->deleteDishCategory($oldCategory);
                $this->redirect('dishes/categories', 'success', 'Categoría eliminada correctamente');
            } catch (Exception $e) {
                $this->redirect('dishes/categories', 'error', 'Error al eliminar categoría: ' . $e->getMessage());
            }
        } else {
            $this->redirect('dishes/categories', 'error', 'Datos inválidos');
        }
    }
    
    private function renameDishCategory($oldCategory, $newCategory) {
        // Check if new category name already exists
        if (in_array($newCategory, $this->dishModel->getCategories())) {
            throw new Exception('Ya existe una categoría con ese nombre');
        }
        
        $query = "UPDATE dishes SET category = ? WHERE category = ? AND active = 1";
        $stmt = $this->dishModel->db->prepare($query);
        
        if (!$stmt->execute([$newCategory, $oldCategory])) {
            throw new Exception('Error al actualizar los platillos');
        }
    }
    
    private function deleteDishCategory($category) {
        $query = "UPDATE dishes SET category = NULL WHERE category = ? AND active = 1";
        $stmt = $this->dishModel->db->prepare($query);
        
        if (!$stmt->execute([$category])) {
            throw new Exception('Error al eliminar la categoría');
        }
    }
    
    public function show($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $dish = $this->dishModel->find($id);
        if (!$dish || !$dish['active']) {
            $this->redirect('dishes', 'error', 'Platillo no encontrado');
            return;
        }
        
        $stats = $this->dishModel->getDishStats($id);
        
        $this->view('dishes/view', [
            'dish' => $dish,
            'stats' => $stats
        ]);
    }
    
    private function validateDishInput($data, $excludeId = null) {
        $errors = $this->validateInput($data, [
            'name' => ['required' => true, 'max' => 255],
            'price' => ['required' => true, 'numeric' => true]
        ]);
        
        // Additional validations
        $price = (float)($data['price'] ?? 0);
        
        if ($price <= 0) {
            $errors['price'] = 'El precio debe ser mayor a 0';
        }
        
        if ($price > 999999.99) {
            $errors['price'] = 'El precio no puede ser mayor a $999,999.99';
        }
        
        // Check if dish name already exists
        if ($this->dishModel->nameExists($data['name'] ?? '', $excludeId)) {
            $errors['name'] = 'Ya existe un platillo con este nombre';
        }
        
        // Validate category if provided
        if (!empty($data['category'])) {
            $category = trim($data['category']);
            if (strlen($category) > 100) {
                $errors['category'] = 'El nombre de la categoría no puede tener más de 100 caracteres';
            }
        }
        
        // Validate description length
        if (!empty($data['description']) && strlen($data['description']) > 1000) {
            $errors['description'] = 'La descripción no puede tener más de 1000 caracteres';
        }
        
        return $errors;
    }
}
?>