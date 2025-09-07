<?php
class FinancialController extends BaseController {
    private $branchModel;
    private $expenseCategoryModel;
    private $expenseModel;
    private $cashWithdrawalModel;
    private $cashClosureModel;
    private $ticketModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        
        $this->branchModel = new Branch();
        $this->expenseCategoryModel = new ExpenseCategory();
        $this->expenseModel = new Expense();
        $this->cashWithdrawalModel = new CashWithdrawal();
        $this->cashClosureModel = new CashClosure();
        $this->ticketModel = new Ticket();
    }
    
    // Dashboard financiero
    public function index() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $user = $this->getCurrentUser();
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $branchId = $_GET['branch_id'] ?? null;
        
        // Estadísticas generales
        $totalExpenses = $this->expenseModel->getTotalByCategory($dateFrom, $dateTo, $branchId);
        $recentExpenses = $this->expenseModel->getExpensesWithDetails(['limit' => 5]);
        $recentWithdrawals = $this->cashWithdrawalModel->getRecentWithdrawals(5);
        $recentClosures = $this->cashClosureModel->getRecentClosures(5);
        
        // Income statistics
        $totalIncome = $this->ticketModel->getTotalIncome($dateFrom, $dateTo);
        $incomeByPaymentMethod = $this->ticketModel->getIncomeByPaymentMethod($dateFrom, $dateTo);
        $incomeVsExpenses = $this->ticketModel->getIncomeVsExpensesData($dateFrom, $dateTo);
        
        // New payment method statistics
        $paymentMethodStats = $this->ticketModel->getPaymentMethodStats($dateFrom, $dateTo);
        $intercambioStats = $this->ticketModel->getIntercambioTotal($dateFrom, $dateTo);
        $pendingPaymentStats = $this->ticketModel->getPendingPaymentTotal($dateFrom, $dateTo);
        
        // Calculate total expenses for comparison
        $totalExpenseAmount = 0;
        foreach ($totalExpenses as $expense) {
            $totalExpenseAmount += (float)$expense['total_amount'];
        }
        
        // Get withdrawal totals for the date range
        $totalWithdrawals = $this->cashWithdrawalModel->getTotalWithdrawals($dateFrom, $dateTo, $branchId);
        $withdrawalsByDate = $this->cashWithdrawalModel->getWithdrawalsByDateRange($dateFrom, $dateTo, $branchId);
        
        // Estadísticas de sucursales si es admin
        $branches = [];
        if ($user['role'] === ROLE_ADMIN) {
            $branches = $this->branchModel->getAllActive();
        }
        
        $data = [
            'user' => $user,
            'total_expenses' => $totalExpenses,
            'recent_expenses' => $recentExpenses,
            'recent_withdrawals' => $recentWithdrawals,
            'recent_closures' => $recentClosures,
            'total_income' => $totalIncome,
            'income_by_payment_method' => $incomeByPaymentMethod,
            'income_vs_expenses' => $incomeVsExpenses,
            'payment_method_stats' => $paymentMethodStats,
            'intercambio_stats' => $intercambioStats,
            'pending_payment_stats' => $pendingPaymentStats,
            'total_expense_amount' => $totalExpenseAmount,
            'total_withdrawals' => $totalWithdrawals,
            'withdrawals_by_date' => $withdrawalsByDate,
            'branches' => $branches,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'selected_branch' => $branchId
        ];
        
        $this->view('financial/index', $data);
    }
    
    // ============= COLLECTION MANAGEMENT (COBRANZA) =============
    
    public function collections() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $pendingTickets = $this->ticketModel->getPendingPayments();
        
        $data = [
            'pending_tickets' => $pendingTickets
        ];
        
        $this->view('financial/collections', $data);
    }
    
    public function updatePaymentStatus($ticketId) {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Método no permitido');
            $this->redirect('financial/collections');
            return;
        }
        
        $paymentMethod = $_POST['payment_method'] ?? '';
        
        if (!in_array($paymentMethod, ['efectivo', 'tarjeta', 'transferencia', 'intercambio'])) {
            $this->setFlashMessage('error', 'Método de pago inválido');
            $this->redirect('financial/collections');
            return;
        }
        
        if ($this->ticketModel->updatePaymentMethod($ticketId, $paymentMethod)) {
            $this->setFlashMessage('success', 'Estado de pago actualizado correctamente');
        } else {
            $this->setFlashMessage('error', 'Error al actualizar el estado de pago');
        }
        
        $this->redirect('financial/collections');
    }
    
    public function intercambios() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $intercambioTickets = $this->ticketModel->getTicketsByPaymentMethod('intercambio', $dateFrom, $dateTo);
        
        $data = [
            'tickets' => $intercambioTickets,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        $this->view('financial/intercambios', $data);
    }

    // ============= GESTIÓN DE GASTOS =============
    
    public function expenses() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $user = $this->getCurrentUser();
        $page = $_GET['page'] ?? 1;
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $categoryId = $_GET['category_id'] ?? null;
        $branchId = $_GET['branch_id'] ?? null;
        
        $conditions = [];
        if ($dateFrom) $conditions['date_from'] = $dateFrom;
        if ($dateTo) $conditions['date_to'] = $dateTo;
        if ($categoryId) $conditions['category_id'] = $categoryId;
        if ($branchId) $conditions['branch_id'] = $branchId;
        
        $expenses = $this->expenseModel->getExpensesWithDetails($conditions);
        $categories = $this->expenseCategoryModel->getAllActive();
        $branches = $user['role'] === ROLE_ADMIN ? $this->branchModel->getAllActive() : [];
        
        $data = [
            'expenses' => $expenses,
            'categories' => $categories,
            'branches' => $branches,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'selected_category' => $categoryId,
            'selected_branch' => $branchId
        ];
        
        $this->view('financial/expenses', $data);
    }
    
    public function createExpense() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->getCurrentUser();
            
            $validationRules = [
                'category_id' => ['required' => true],
                'amount' => ['required' => true, 'numeric' => true],
                'description' => ['required' => true, 'min' => 5],
                'expense_date' => ['required' => true]
            ];
            
            $errors = $this->validateInput($_POST, $validationRules);
            
            if (empty($errors)) {
                $data = [
                    'category_id' => $_POST['category_id'],
                    'branch_id' => $_POST['branch_id'] ?? null,
                    'user_id' => $user['id'],
                    'amount' => $_POST['amount'],
                    'description' => $_POST['description'],
                    'expense_date' => $_POST['expense_date']
                ];
                
                // Manejar archivo de evidencia
                if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->uploadEvidenceFile($_FILES['receipt_file']);
                    if ($uploadResult['success']) {
                        $data['receipt_file'] = $uploadResult['filename'];
                    } else {
                        $this->setFlashMessage('error', $uploadResult['error']);
                        $this->redirect('financial/expenses');
                    }
                }
                
                if ($this->expenseModel->createExpense($data)) {
                    $this->setFlashMessage('success', 'Gasto registrado correctamente');
                    $this->redirect('financial/expenses');
                } else {
                    $this->setFlashMessage('error', 'Error al registrar el gasto');
                }
            } else {
                $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            }
        }
        
        $categories = $this->expenseCategoryModel->getAllActive();
        $branches = $this->getCurrentUser()['role'] === ROLE_ADMIN ? $this->branchModel->getAllActive() : [];
        
        $data = [
            'categories' => $categories,
            'branches' => $branches,
            'expense_date' => date('Y-m-d')
        ];
        
        $this->view('financial/create_expense', $data);
    }
    
    public function viewExpense($id) {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $expense = $this->expenseModel->getExpenseById($id);
        
        if (!$expense) {
            $this->setFlashMessage('error', 'Gasto no encontrado');
            $this->redirect('financial/expenses');
        }
        
        $data = ['expense' => $expense];
        $this->view('financial/view_expense', $data);
    }
    
    // ============= GESTIÓN DE CATEGORÍAS =============
    
    public function categories() {
        $this->requireRole([ROLE_ADMIN]);
        
        $categories = $this->expenseCategoryModel->getCategoriesWithStats();
        
        $data = ['categories' => $categories];
        $this->view('financial/categories', $data);
    }
    
    public function createCategory() {
        $this->requireRole([ROLE_ADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validationRules = [
                'name' => ['required' => true, 'min' => 3],
                'color' => ['required' => true]
            ];
            
            $errors = $this->validateInput($_POST, $validationRules);
            
            // Validar nombre único
            if ($this->expenseCategoryModel->nameExists($_POST['name'])) {
                $errors['name'] = 'Ya existe una categoría con este nombre';
            }
            
            if (empty($errors)) {
                $data = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'] ?? '',
                    'color' => $_POST['color']
                ];
                
                if ($this->expenseCategoryModel->create($data)) {
                    $this->setFlashMessage('success', 'Categoría creada correctamente');
                    $this->redirect('financial/categories');
                } else {
                    $this->setFlashMessage('error', 'Error al crear la categoría');
                }
            } else {
                $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            }
        }
        
        $this->view('financial/create_category');
    }
    
    // ============= GESTIÓN DE RETIROS =============
    
    public function withdrawals() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $user = $this->getCurrentUser();
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $branchId = $_GET['branch_id'] ?? null;
        
        $conditions = [];
        if ($dateFrom) $conditions['date_from'] = $dateFrom;
        if ($dateTo) $conditions['date_to'] = $dateTo;
        if ($branchId) $conditions['branch_id'] = $branchId;
        
        // Si no es admin, solo mostrar sus retiros
        if ($user['role'] !== ROLE_ADMIN) {
            $conditions['responsible_user_id'] = $user['id'];
        }
        
        $withdrawals = $this->cashWithdrawalModel->getWithdrawalsWithDetails($conditions);
        $branches = $user['role'] === ROLE_ADMIN ? $this->branchModel->getAllActive() : [];
        
        $data = [
            'withdrawals' => $withdrawals,
            'branches' => $branches,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'selected_branch' => $branchId
        ];
        
        $this->view('financial/withdrawals', $data);
    }
    
    public function createWithdrawal() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->getCurrentUser();
            
            $validationRules = [
                'amount' => ['required' => true, 'numeric' => true],
                'reason' => ['required' => true, 'min' => 10],
                'withdrawal_date' => ['required' => true]
            ];
            
            $errors = $this->validateInput($_POST, $validationRules);
            
            if (empty($errors)) {
                $data = [
                    'branch_id' => $_POST['branch_id'] ?? null,
                    'responsible_user_id' => $user['id'],
                    'amount' => $_POST['amount'],
                    'reason' => $_POST['reason'],
                    'withdrawal_date' => $_POST['withdrawal_date'] . ' ' . date('H:i:s')
                ];
                
                // Auto-autorizar si es admin
                if ($user['role'] === ROLE_ADMIN) {
                    $data['authorized_by_user_id'] = $user['id'];
                }
                
                // Manejar archivo de evidencia
                if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->uploadEvidenceFile($_FILES['evidence_file']);
                    if ($uploadResult['success']) {
                        $data['evidence_file'] = $uploadResult['filename'];
                    } else {
                        $this->setFlashMessage('error', $uploadResult['error']);
                        $this->redirect('financial/withdrawals');
                    }
                }
                
                if ($this->cashWithdrawalModel->createWithdrawal($data)) {
                    $this->setFlashMessage('success', 'Retiro registrado correctamente');
                    $this->redirect('financial/withdrawals');
                } else {
                    $this->setFlashMessage('error', 'Error al registrar el retiro');
                }
            } else {
                $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            }
        }
        
        $branches = $this->getCurrentUser()['role'] === ROLE_ADMIN ? $this->branchModel->getAllActive() : [];
        
        $data = [
            'branches' => $branches,
            'withdrawal_date' => date('Y-m-d')
        ];
        
        $this->view('financial/create_withdrawal', $data);
    }
    
    // ============= CORTE DE CAJA =============
    
    public function closures() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $user = $this->getCurrentUser();
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $branchId = $_GET['branch_id'] ?? null;
        
        $conditions = [];
        if ($dateFrom) $conditions['date_from'] = $dateFrom;
        if ($dateTo) $conditions['date_to'] = $dateTo;
        if ($branchId) $conditions['branch_id'] = $branchId;
        
        // Si no es admin, solo mostrar sus cortes
        if ($user['role'] !== ROLE_ADMIN) {
            $conditions['cashier_user_id'] = $user['id'];
        }
        
        $closures = $this->cashClosureModel->getClosuresWithDetails($conditions);
        $branches = $user['role'] === ROLE_ADMIN ? $this->branchModel->getAllActive() : [];
        
        $data = [
            'closures' => $closures,
            'branches' => $branches,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'selected_branch' => $branchId
        ];
        
        $this->view('financial/closures', $data);
    }
    
    public function createClosure() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->getCurrentUser();
            
            $validationRules = [
                'shift_start' => ['required' => true],
                'shift_end' => ['required' => true],
                'initial_cash' => ['required' => true, 'numeric' => true],
                'final_cash' => ['required' => true, 'numeric' => true]
            ];
            
            $errors = $this->validateInput($_POST, $validationRules);
            
            if (empty($errors)) {
                $data = [
                    'branch_id' => $_POST['branch_id'] ?? null,
                    'cashier_user_id' => $user['id'],
                    'shift_start' => $_POST['shift_start'],
                    'shift_end' => $_POST['shift_end'],
                    'initial_cash' => $_POST['initial_cash'],
                    'final_cash' => $_POST['final_cash'],
                    'notes' => $_POST['notes'] ?? ''
                ];
                
                if ($this->cashClosureModel->createClosure($data)) {
                    $this->setFlashMessage('success', 'Corte de caja realizado correctamente');
                    $this->redirect('financial/closures');
                } else {
                    $this->setFlashMessage('error', 'Error al realizar el corte de caja');
                }
            } else {
                $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            }
        }
        
        $branches = $this->getCurrentUser()['role'] === ROLE_ADMIN ? $this->branchModel->getAllActive() : [];
        
        $data = [
            'branches' => $branches,
            'shift_start' => date('Y-m-d 08:00'),
            'shift_end' => date('Y-m-d H:i')
        ];
        
        $this->view('financial/create_closure', $data);
    }
    
    public function viewClosure($id) {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $closure = $this->cashClosureModel->getClosureById($id);
        
        if (!$closure) {
            $this->setFlashMessage('error', 'Corte de caja no encontrado');
            $this->redirect('financial/closures');
        }
        
        // Verificar permisos si no es admin
        $user = $this->getCurrentUser();
        if ($user['role'] !== ROLE_ADMIN && $closure['cashier_user_id'] != $user['id']) {
            $this->setFlashMessage('error', 'No tienes permisos para ver este corte de caja');
            $this->redirect('financial/closures');
        }
        
        $data = ['closure' => $closure];
        $this->view('financial/view_closure', $data);
    }
    
    // ============= GESTIÓN DE SUCURSALES =============
    
    public function branches() {
        $this->requireRole([ROLE_ADMIN]);
        
        $branches = $this->branchModel->getAllActive();
        
        $data = ['branches' => $branches];
        $this->view('financial/branches', $data);
    }
    
    public function createBranch() {
        $this->requireRole([ROLE_ADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validationRules = [
                'name' => ['required' => true, 'min' => 3],
                'address' => ['required' => true, 'min' => 10]
            ];
            
            $errors = $this->validateInput($_POST, $validationRules);
            
            if (empty($errors)) {
                $data = [
                    'name' => $_POST['name'],
                    'address' => $_POST['address'],
                    'phone' => $_POST['phone'] ?? '',
                    'manager_user_id' => $_POST['manager_user_id'] ?? null
                ];
                
                if ($this->branchModel->create($data)) {
                    $this->setFlashMessage('success', 'Sucursal creada correctamente');
                    $this->redirect('financial/branches');
                } else {
                    $this->setFlashMessage('error', 'Error al crear la sucursal');
                }
            } else {
                $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            }
        }
        
        $userModel = new User();
        $managers = $userModel->getUsersByRole(ROLE_ADMIN);
        
        $data = ['managers' => $managers];
        $this->view('financial/create_branch', $data);
    }
    
    public function viewBranch($id) {
        $this->requireRole([ROLE_ADMIN]);
        
        $branch = $this->branchModel->getBranchWithManager($id);
        
        if (!$branch) {
            $this->setFlashMessage('error', 'Sucursal no encontrada');
            $this->redirect('financial/branches');
        }
        
        $staff = $this->branchModel->getBranchStaff($id);
        $stats = $this->branchModel->getBranchStats($id);
        
        $data = [
            'branch' => $branch,
            'staff' => $staff,
            'stats' => $stats
        ];
        
        $this->view('financial/view_branch', $data);
    }
    
    // ============= FUNCIONES AUXILIARES =============
    
    private function uploadEvidenceFile($file) {
        $uploadDir = UPLOAD_EVIDENCE_PATH;
        
        // Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedExtensions = ALLOWED_EVIDENCE_EXTENSIONS;
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            return [
                'success' => false,
                'error' => 'Tipo de archivo no permitido. Tipos permitidos: ' . implode(', ', $allowedExtensions)
            ];
        }
        
        // Generar nombre único
        $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true,
                'filename' => $filename
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Error al subir el archivo'
            ];
        }
    }
    
    // API endpoints para obtener datos JSON
    public function getExpenseStats() {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $branchId = $_GET['branch_id'] ?? null;
        
        $stats = $this->expenseModel->getTotalByCategory($dateFrom, $dateTo, $branchId);
        
        $this->json($stats);
    }
    
    public function downloadEvidence($filename) {
        $this->requireRole([ROLE_ADMIN, ROLE_CASHIER]);
        
        $filePath = UPLOAD_EVIDENCE_PATH . $filename;
        
        if (!file_exists($filePath)) {
            $this->setFlashMessage('error', 'Archivo no encontrado');
            $this->redirect('financial');
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit();
    }
    
    // ============= MÉTODOS DE ELIMINACIÓN Y AUTORIZACIÓN =============
    
    public function deleteExpense($id) {
        $this->requireRole([ROLE_ADMIN]);
        
        if ($this->expenseModel->deleteExpense($id)) {
            $this->setFlashMessage('success', 'Gasto eliminado correctamente');
        } else {
            $this->setFlashMessage('error', 'Error al eliminar el gasto');
        }
        
        $this->redirect('financial/expenses');
    }
    
    public function authorizeWithdrawal($id) {
        $this->requireRole([ROLE_ADMIN]);
        
        $user = $this->getCurrentUser();
        
        if ($this->cashWithdrawalModel->authorizeWithdrawal($id, $user['id'])) {
            $this->setFlashMessage('success', 'Retiro autorizado correctamente');
        } else {
            $this->setFlashMessage('error', 'Error al autorizar el retiro');
        }
        
        $this->redirect('financial/withdrawals');
    }
    
    public function deleteWithdrawal($id) {
        $this->requireRole([ROLE_ADMIN]);
        
        if ($this->cashWithdrawalModel->deleteWithdrawal($id)) {
            $this->setFlashMessage('success', 'Retiro eliminado correctamente');
        } else {
            $this->setFlashMessage('error', 'Error al eliminar el retiro');
        }
        
        $this->redirect('financial/withdrawals');
    }
    
    public function deleteClosure($id) {
        $this->requireRole([ROLE_ADMIN]);
        
        if ($this->cashClosureModel->delete($id)) {
            $this->setFlashMessage('success', 'Corte de caja eliminado correctamente');
        } else {
            $this->setFlashMessage('error', 'Error al eliminar el corte de caja');
        }
        
        $this->redirect('financial/closures');
    }
    
    public function deleteCategory($id) {
        $this->requireRole([ROLE_ADMIN]);
        
        // Verificar si tiene gastos asociados
        $expensesCount = $this->expenseModel->count(['category_id' => $id]);
        if ($expensesCount > 0) {
            $this->setFlashMessage('error', 'No se puede eliminar la categoría porque tiene gastos asociados');
            $this->redirect('financial/categories');
        }
        
        if ($this->expenseCategoryModel->delete($id)) {
            $this->setFlashMessage('success', 'Categoría eliminada correctamente');
        } else {
            $this->setFlashMessage('error', 'Error al eliminar la categoría');
        }
        
        $this->redirect('financial/categories');
    }
    
    public function deleteBranch($id) {
        $this->requireRole([ROLE_ADMIN]);
        
        if ($this->branchModel->delete($id)) {
            $this->setFlashMessage('success', 'Sucursal eliminada correctamente');
        } else {
            $this->setFlashMessage('error', 'Error al eliminar la sucursal');
        }
        
        $this->redirect('financial/branches');
    }
}
?>