<?php
class CustomersController extends BaseController {
    private $customerModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->customerModel = new Customer();
    }
    
    public function index() {
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $customers = [];
        $totalCustomers = 0;
        
        if ($search) {
            $customers = $this->customerModel->searchCustomers($search);
            $totalCustomers = count($customers);
        } else {
            $customers = $this->customerModel->getAllWithPagination($perPage, $offset);
            $totalCustomers = $this->customerModel->getTotalCount();
        }
        
        $totalPages = ceil($totalCustomers / $perPage);
        
        $this->view('customers/index', [
            'customers' => $customers,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCustomers' => $totalCustomers
        ]);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? '') ?: null,
                'birthday' => trim($_POST['birthday'] ?? '') ?: null,
                'total_visits' => 0,
                'total_spent' => 0.00
            ];
            
            if (empty($data['name']) || empty($data['phone'])) {
                $this->redirect('customers/create', 'error', 'Nombre y teléfono son obligatorios');
                return;
            }
            
            // Check if phone already exists
            if ($this->customerModel->findBy('phone', $data['phone'])) {
                $this->redirect('customers/create', 'error', 'Ya existe un cliente con ese número de teléfono');
                return;
            }
            
            try {
                $customerId = $this->customerModel->create($data);
                $this->redirect('customers/show/' . $customerId, 'success', 'Cliente creado exitosamente');
            } catch (Exception $e) {
                $this->redirect('customers/create', 'error', 'Error al crear cliente: ' . $e->getMessage());
            }
        } else {
            $this->view('customers/create');
        }
    }
    
    public function show($id) {
        $customer = $this->customerModel->getCustomerWithStats($id);
        
        if (!$customer) {
            $this->redirect('customers', 'error', 'Cliente no encontrado');
            return;
        }
        
        // Get customer's order history
        $orderModel = new Order();
        $orders = $orderModel->getOrdersWithDetails(['customer_id' => $id]);
        
        $this->view('customers/show', [
            'customer' => $customer,
            'orders' => $orders
        ]);
    }
    
    public function edit($id) {
        $customer = $this->customerModel->findById($id);
        
        if (!$customer) {
            $this->redirect('customers', 'error', 'Cliente no encontrado');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? '') ?: null,
                'birthday' => trim($_POST['birthday'] ?? '') ?: null
            ];
            
            if (empty($data['name']) || empty($data['phone'])) {
                $this->redirect('customers/edit/' . $id, 'error', 'Nombre y teléfono son obligatorios');
                return;
            }
            
            // Check if phone already exists for another customer
            $existingCustomer = $this->customerModel->findBy('phone', $data['phone']);
            if ($existingCustomer && $existingCustomer['id'] != $id) {
                $this->redirect('customers/edit/' . $id, 'error', 'Ya existe otro cliente con ese número de teléfono');
                return;
            }
            
            try {
                $this->customerModel->update($id, $data);
                $this->redirect('customers/show/' . $id, 'success', 'Cliente actualizado exitosamente');
            } catch (Exception $e) {
                $this->redirect('customers/edit/' . $id, 'error', 'Error al actualizar cliente: ' . $e->getMessage());
            }
        } else {
            $this->view('customers/edit', ['customer' => $customer]);
        }
    }
    
    public function delete($id) {
        if (!$this->requireRole([ROLE_ADMIN])) {
            return;
        }
        
        $customer = $this->customerModel->findById($id);
        
        if (!$customer) {
            $this->redirect('customers', 'error', 'Cliente no encontrado');
            return;
        }
        
        try {
            // Check if customer has orders
            $orderModel = new Order();
            $orderCount = $orderModel->count(['customer_id' => $id]);
            
            if ($orderCount > 0) {
                // Soft delete - just mark as inactive
                $this->customerModel->update($id, ['active' => false]);
                $this->redirect('customers', 'success', 'Cliente desactivado (tiene pedidos asociados)');
            } else {
                // Hard delete if no orders
                $this->customerModel->delete($id);
                $this->redirect('customers', 'success', 'Cliente eliminado exitosamente');
            }
        } catch (Exception $e) {
            $this->redirect('customers', 'error', 'Error al eliminar cliente: ' . $e->getMessage());
        }
    }
    
    public function search() {
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }
        
        try {
            $customers = $this->customerModel->searchCustomers($query);
            echo json_encode($customers);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>