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
        $query = "SELECT 
                    DATE_FORMAT(o.created_at, '%Y-%m') as month,
                    COUNT(DISTINCT o.customer_id) as unique_customers,
                    COUNT(o.id) as total_orders,
                    SUM(o.total) as total_revenue,
                    AVG(o.total) as avg_order_value
                  FROM orders o 
                  WHERE o.customer_id IS NOT NULL 
                    AND DATE(o.created_at) BETWEEN ? AND ?
                    AND o.status = ?
                  GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
                  ORDER BY month DESC";
        
        $stmt = $this->customerModel->db->prepare($query);
        $stmt->execute([$startDate, $endDate, ORDER_DELIVERED]);
        
        return $stmt->fetchAll();
    }
    
    private function getCustomerGrowthData() {
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as new_customers
                  FROM customers 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month ASC";
        
        $stmt = $this->customerModel->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
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