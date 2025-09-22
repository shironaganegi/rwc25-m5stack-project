<?php
// config/database.php
class Database {
    private $host = 'localhost';
    private $db_name = 'supermarket_app';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                                $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// models/Sale.php
class Sale {
    private $conn;
    private $table_name = "sales";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (product_id, sale_price, discount_amount, start_date, end_date, 
                   target_stores, inventory_status, notes, user_id) 
                  VALUES 
                  (:product_id, :sale_price, :discount_amount, :start_date, :end_date, 
                   :target_stores, :inventory_status, :notes, :user_id)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    public function getActiveSales($user_id) {
        $query = "SELECT s.*, p.name as product_name, c.name as category_name 
                  FROM " . $this->table_name . " s
                  JOIN products p ON s.product_id = p.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE s.user_id = :user_id AND s.is_active = 1 
                  AND s.end_date >= CURDATE()
                  ORDER BY s.end_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExpiringSoon($user_id, $days = 1) {
        $query = "SELECT s.*, p.name as product_name 
                  FROM " . $this->table_name . " s
                  JOIN products p ON s.product_id = p.id
                  WHERE s.user_id = :user_id AND s.is_active = 1 
                  AND s.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':days', $days);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// models/Product.php
class Product {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, category_id, regular_price, stock_quantity, user_id) 
                  VALUES 
                  (:name, :category_id, :regular_price, :stock_quantity, :user_id)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    public function getLowStockItems($user_id, $threshold = 10) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND stock_quantity <= :threshold
                  ORDER BY stock_quantity ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':threshold', $threshold);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($user_id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.user_id = :user_id
                  ORDER BY p.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// models/SalesData.php
class SalesData {
    private $conn;
    private $table_name = "sales_data";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDailySales($user_id, $date = null) {
        if (!$date) $date = date('Y-m-d');
        
        $query = "SELECT SUM(total_amount) as total_sales, COUNT(*) as total_transactions
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND sale_date = :date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMonthlySales($user_id, $year = null, $month = null) {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');
        
        $query = "SELECT 
                    SUM(total_amount) as total_sales,
                    COUNT(*) as total_transactions,
                    AVG(unit_price) as avg_unit_price
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  AND YEAR(sale_date) = :year 
                  AND MONTH(sale_date) = :month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTopProducts($user_id, $limit = 10, $period = 'month') {
        $date_condition = "";
        switch($period) {
            case 'today':
                $date_condition = "AND sale_date = CURDATE()";
                break;
            case 'week':
                $date_condition = "AND sale_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $date_condition = "AND sale_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                break;
        }

        $query = "SELECT 
                    p.name as product_name,
                    SUM(sd.quantity_sold) as total_quantity,
                    SUM(sd.total_amount) as total_revenue
                  FROM " . $this->table_name . " sd
                  JOIN products p ON sd.product_id = p.id
                  WHERE sd.user_id = :user_id " . $date_condition . "
                  GROUP BY sd.product_id, p.name
                  ORDER BY total_revenue DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// api/dashboard.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../models/Sale.php';
require_once '../models/Product.php';
require_once '../models/SalesData.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => '未ログイン']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$sale = new Sale($db);
$product = new Product($db);
$salesData = new SalesData($db);

$user_id = $_SESSION['user_id'];

// 今日の売上データ
$todaySales = $salesData->getDailySales($user_id);
$dailyTarget = 580000; // これは設定から取得すべき
$achievementRate = $todaySales['total_sales'] ? ($todaySales['total_sales'] / $dailyTarget) * 100 : 0;

// 在庫アラート
$lowStockItems = $product->getLowStockItems($user_id, 10);

// 期限が近いセール
$expiringSales = $sale->getExpiringSoon($user_id, 1);

// 月間分析データ
$monthlySales = $salesData->getMonthlySales($user_id);

$response = [
    'today_sales' => [
        'amount' => $todaySales['total_sales'] ?? 0,
        'target' => $dailyTarget,
        'achievement_rate' => round($achievementRate, 1)
    ],
    'stock_alerts' => [
        'count' => count($lowStockItems),
        'items' => array_slice($lowStockItems, 0, 5)
    ],
    'expiring_sales' => [
        'count' => count($expiringSales),
        'items' => $expiringSales
    ],
    'monthly_analysis' => $monthlySales
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);

// api/sales.php - セール情報の CRUD 操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $data = [
        ':product_id' => $input['product_id'],
        ':sale_price' => $input['sale_price'],
        ':discount_amount' => $input['discount_amount'],
        ':start_date' => $input['start_date'],
        ':end_date' => $input['end_date'],
        ':target_stores' => $input['target_stores'],
        ':inventory_status' => $input['inventory_status'],
        ':notes' => $input['notes'] ?? '',
        ':user_id' => $_SESSION['user_id']
    ];
    
    if ($sale->create($data)) {
        echo json_encode(['success' => true, 'message' => 'セール情報が登録されました']);
    } else {
        echo json_encode(['success' => false, 'message' => '登録に失敗しました']);
    }
}
?>