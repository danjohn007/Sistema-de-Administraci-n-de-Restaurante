<?php
class DashboardController extends BaseController {
    private $tableModel;
    private $orderModel;
    private $ticketModel;
    private $dishModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        
        $this->tableModel = new Table();
        $this->orderModel = new Order();
        $this->ticketModel = new Ticket();
        $this->dishModel = new Dish();
    }
    
    public function index() {
        $user = $this->getCurrentUser();
        
        // Get dashboard data based on user role
        $data = [
            'user' => $user,
            'current_time' => date('Y-m-d H:i:s')
        ];
        
        switch ($user['role']) {
            case ROLE_ADMIN:
                $data = array_merge($data, $this->getAdminDashboardData());
                break;
                
            case ROLE_WAITER:
                $data = array_merge($data, $this->getWaiterDashboardData($user['id']));
                break;
                
            case ROLE_CASHIER:
                $data = array_merge($data, $this->getCashierDashboardData());
                break;
        }
        
        $this->view('dashboard/index', $data);
    }
    
    private function getAdminDashboardData() {
        // Table statistics
        $tableStats = $this->tableModel->getTableStats();
        
        // Daily sales
        $dailySales = $this->orderModel->getDailySales();
        
        // Recent orders
        $recentOrders = $this->orderModel->getOrdersWithDetails(['limit' => 5]);
        
        // Popular dishes
        $popularDishes = $this->dishModel->getPopularDishes(5);
        
        // Monthly revenue (simplified)
        $monthlyRevenue = $this->getMonthlyRevenue();
        
        // Expired orders count
        $expiredOrdersCount = $this->orderModel->getExpiredOrdersCount();
        
        return [
            'table_stats' => $tableStats,
            'daily_sales' => $dailySales,
            'recent_orders' => $recentOrders,
            'popular_dishes' => $popularDishes,
            'monthly_revenue' => $monthlyRevenue,
            'total_tables' => $this->tableModel->count(['active' => 1]),
            'total_dishes' => $this->dishModel->count(['active' => 1]),
            'pending_orders' => $this->orderModel->count(['status' => ORDER_PENDING]),
            'ready_orders' => $this->orderModel->count(['status' => ORDER_READY]),
            'expired_orders_count' => $expiredOrdersCount
        ];
    }
    
    private function getWaiterDashboardData($userId) {
        $waiterModel = new Waiter();
        $waiter = $waiterModel->getWaiterByUserId($userId);
        
        if (!$waiter) {
            $this->redirect('auth/logout', 'error', 'Usuario no válido como mesero');
        }
        
        // Get waiter's assigned tables
        $assignedTables = $this->tableModel->findAll(['waiter_id' => $waiter['id'], 'active' => 1]);
        
        // Get waiter's orders today
        $todayOrders = $this->orderModel->getOrdersWithDetails([
            'waiter_id' => $waiter['id'],
            'date' => date('Y-m-d')
        ]);
        
        // Get waiter stats
        $stats = $waiterModel->getWaiterStats($waiter['id'], 'today');
        
        // Get pending orders
        $pendingOrders = $this->orderModel->getOrdersWithDetails([
            'waiter_id' => $waiter['id'],
            'status' => ORDER_PENDING
        ]);
        
        // Get expired orders for this waiter
        $expiredOrdersCount = $this->orderModel->getExpiredOrdersCount($waiter['id']);
        
        return [
            'waiter' => $waiter,
            'assigned_tables' => $assignedTables,
            'today_orders' => $todayOrders,
            'waiter_stats' => $stats,
            'pending_orders' => $pendingOrders,
            'expired_orders_count' => $expiredOrdersCount
        ];
    }
    
    private function getCashierDashboardData() {
        // Today's tickets
        $todayTickets = $this->ticketModel->getTicketsByDate(date('Y-m-d'));
        
        // Daily sales report
        $salesReport = $this->ticketModel->getDailySalesReport();
        
        // Orders ready for payment
        $readyOrders = $this->orderModel->getOrdersWithDetails(['status' => ORDER_READY]);
        
        // Orders with bill requested
        $billRequestedTables = $this->tableModel->getTablesByStatus(TABLE_BILL_REQUESTED);
        
        return [
            'today_tickets' => $todayTickets,
            'sales_report' => $salesReport,
            'ready_orders' => $readyOrders,
            'bill_requested_tables' => $billRequestedTables
        ];
    }
    
    private function getMonthlyRevenue() {
        // Simple monthly revenue calculation
        $query = "SELECT 
                    MONTH(created_at) as month,
                    YEAR(created_at) as year,
                    SUM(total) as revenue
                  FROM tickets 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY YEAR(created_at), MONTH(created_at)
                  ORDER BY year ASC, month ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function quickStats() {
        $this->requireAuth();
        
        $stats = [
            'available_tables' => $this->tableModel->count(['status' => TABLE_AVAILABLE, 'active' => 1]),
            'occupied_tables' => $this->tableModel->count(['status' => TABLE_OCCUPIED, 'active' => 1]),
            'pending_orders' => $this->orderModel->count(['status' => ORDER_PENDING]),
            'ready_orders' => $this->orderModel->count(['status' => ORDER_READY]),
            'daily_sales' => $this->orderModel->getDailySales()
        ];
        
        $this->json($stats);
    }
}
?>