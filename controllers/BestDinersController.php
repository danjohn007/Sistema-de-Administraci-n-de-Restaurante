<?php
class BestDinersController extends BaseController {
    private $customerModel;
    private $orderModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->customerModel = new Customer();
        $this->orderModel = new Order();
    }
    
    public function index() {
        $this->view('best_diners/index');
    }
    
    public function bySpending() {
        $limit = $_GET['limit'] ?? 10;
        $customers = $this->customerModel->getTopCustomersBySpending($limit);
        
        $this->view('best_diners/by_spending', [
            'customers' => $customers,
            'limit' => $limit
        ]);
    }
    
    public function byVisits() {
        $limit = $_GET['limit'] ?? 10;
        $customers = $this->customerModel->getTopCustomersByVisits($limit);
        
        $this->view('best_diners/by_visits', [
            'customers' => $customers,
            'limit' => $limit
        ]);
    }
    
    public function report() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        // Get analytics data
        $topBySpending = $this->customerModel->getTopCustomersBySpending(10);
        $topByVisits = $this->customerModel->getTopCustomersByVisits(10);
        $monthlyStats = $this->getMonthlyCustomerStats($startDate, $endDate);
        $customerGrowth = $this->getCustomerGrowthData();
        
        $this->view('best_diners/report', [
            'topBySpending' => $topBySpending,
            'topByVisits' => $topByVisits,
            'monthlyStats' => $monthlyStats,
            'customerGrowth' => $customerGrowth,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    public function analytics() {
        // Return JSON data for charts
        header('Content-Type: application/json');
        
        $type = $_GET['type'] ?? 'spending';
        $limit = $_GET['limit'] ?? 10;
        
        try {
            switch ($type) {
                case 'spending':
                    $data = $this->customerModel->getTopCustomersBySpending($limit);
                    echo json_encode([
                        'success' => true,
                        'data' => array_map(function($customer) {
                            return [
                                'name' => $customer['name'],
                                'value' => floatval($customer['total_spent']),
                                'visits' => intval($customer['total_visits'])
                            ];
                        }, $data)
                    ]);
                    break;
                    
                case 'visits':
                    $data = $this->customerModel->getTopCustomersByVisits($limit);
                    echo json_encode([
                        'success' => true,
                        'data' => array_map(function($customer) {
                            return [
                                'name' => $customer['name'],
                                'value' => intval($customer['total_visits']),
                                'spending' => floatval($customer['total_spent'])
                            ];
                        }, $data)
                    ]);
                    break;
                    
                case 'growth':
                    $data = $this->getCustomerGrowthData();
                    echo json_encode([
                        'success' => true,
                        'data' => $data
                    ]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'error' => 'Invalid type']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    private function getMonthlyCustomerStats($startDate, $endDate) {
        try {
            return $this->customerModel->getMonthlyCustomerStats($startDate, $endDate);
        } catch (Exception $e) {
            error_log("BestDinersController::getMonthlyCustomerStats - Error: " . $e->getMessage());
            return [];
        }
    }
    
    private function getCustomerGrowthData() {
        try {
            return $this->customerModel->getCustomerGrowthData();
        } catch (Exception $e) {
            error_log("BestDinersController::getCustomerGrowthData - Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function customerDetail($id) {
        $customer = $this->customerModel->getCustomerWithStats($id);
        
        if (!$customer) {
            $this->redirect('best_diners', 'error', 'Cliente no encontrado');
            return;
        }
        
        // Get customer's order history
        $orders = $this->orderModel->getOrdersWithDetails(['customer_id' => $id]);
        
        $this->view('best_diners/customer_detail', [
            'customer' => $customer,
            'orders' => $orders
        ]);
    }
}
?>