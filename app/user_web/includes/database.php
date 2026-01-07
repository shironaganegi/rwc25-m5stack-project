<?php
/**
 * データベース接続ユーティリティクラス
 * スーパーマーケットアプリ用データベース接続管理
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // データベース設定
    private const HOST = 'mysql';    // Dockerコンテナ名
    private const PORT = 3306;       // コンテナ内部のポート
    private const DBNAME = 'supermarket_app';
    private const USERNAME = 'dev';
    private const PASSWORD = 'dev';
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . self::HOST . ";port=" . self::PORT . ";dbname=" . self::DBNAME . ";charset=utf8mb4",
                self::USERNAME,
                self::PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("データベース接続エラー: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // データベースアクセス用のヘルパーメソッド
    
    /**
     * SELECT文を実行
     */
    public function select($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * 単一レコードを取得
     */
    public function selectOne($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * INSERT文を実行
     */
    public function insert($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $this->connection->lastInsertId();
    }
    
    /**
     * UPDATE文を実行
     */
    public function update($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    /**
     * DELETE文を実行
     */
    public function delete($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    // スーパーマーケットアプリ用の専用メソッド
    
    /**
     * 全商品を取得（カテゴリ名付き）
     */
    public function getAllProducts() {
        return $this->select("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.name
        ");
    }
    
    /**
     * カテゴリ別商品を取得
     */
    public function getProductsByCategory($categoryId) {
        return $this->select("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = ?
            ORDER BY p.name
        ", [$categoryId]);
    }
    
    /**
     * アクティブなセール情報を取得
     */
    public function getActiveSales() {
        return $this->select("
            SELECT s.*, p.name as product_name 
            FROM sales s 
            JOIN products p ON s.product_id = p.id 
            WHERE s.is_active = 1 
            AND s.start_date <= CURDATE() 
            AND s.end_date >= CURDATE()
            ORDER BY s.start_date DESC
        ");
    }
    
    /**
     * 在庫が少ない商品を取得
     */
    public function getLowStockProducts($threshold = 10) {
        return $this->select("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.stock_quantity <= ?
            ORDER BY p.stock_quantity ASC
        ", [$threshold]);
    }
    
    /**
     * 売上目標の達成状況を取得
     */
    public function getSalesTargetStatus($userId, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $target = $this->selectOne("
            SELECT * FROM sales_targets 
            WHERE user_id = ? AND target_date = ?
        ", [$userId, $date]);
        
        if (!$target) {
            return null;
        }
        
        $actualSales = $this->selectOne("
            SELECT SUM(total_amount) as total 
            FROM sales_data 
            WHERE user_id = ? AND sale_date = ?
        ", [$userId, $date]);
        
        $target['actual_amount'] = $actualSales['total'] ?? 0;
        $target['achievement_rate'] = $target['target_amount'] > 0 
            ? ($target['actual_amount'] / $target['target_amount']) * 100 
            : 0;
            
        return $target;
    }
    
    /**
     * 商品を検索
     */
    public function searchProducts($keyword) {
        return $this->select("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.name LIKE ? OR c.name LIKE ?
            ORDER BY p.name
        ", ["%$keyword%", "%$keyword%"]);
    }
    
    /**
     * 店舗別のセール情報を取得（店舗ごとにグループ化）
     */
    public function getSalesByStore() {
        $sales = $this->select("
            SELECT 
                u.id as store_id,
                u.store_name,
                s.id as sale_id,
                s.sale_price,
                s.discount_amount,
                s.start_date,
                s.end_date,
                s.notes,
                p.id as product_id,
                p.name as product_name,
                p.regular_price,
                c.name as category_name
            FROM sales s
            JOIN products p ON s.product_id = p.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE s.is_active = 1 
            AND s.start_date <= CURDATE() 
            AND s.end_date >= CURDATE()
            ORDER BY u.store_name, s.start_date DESC
        ");
        
        // 店舗ごとにグループ化
        $grouped = [];
        foreach ($sales as $sale) {
            $storeId = $sale['store_id'];
            if (!isset($grouped[$storeId])) {
                // 店舗名からロゴパスを生成（デフォルト値）
                $storeNameForLogo = strtolower(str_replace([' ', '店', 'ー'], ['_', '', ''], $sale['store_name']));
                $grouped[$storeId] = [
                    'id' => $storeId,
                    'name' => $sale['store_name'],
                    'logo' => '/assets/image/' . $storeNameForLogo . '_logo.png',
                    'items' => []
                ];
            }
            
            // 商品名から画像パスを生成（デフォルト値）
            $productNameForImage = strtolower(str_replace([' ', '　'], '_', $sale['product_name']));
            
            $grouped[$storeId]['items'][] = [
                'id' => $sale['sale_id'],
                'product_id' => $sale['product_id'],
                'name' => $sale['product_name'],
                'image' => '/assets/image/' . $productNameForImage . '.png',
                'price_old' => number_format($sale['regular_price']) . '円',
                'price_new' => number_format($sale['sale_price']) . '円',
                'time' => $sale['notes'] ?: '終日'
            ];
        }
        
        return array_values($grouped);
    }
    
    /**
     * 全店舗情報を取得
     */
    public function getAllStores() {
        return $this->select("
            SELECT DISTINCT 
                u.id,
                u.store_name,
                u.created_at
            FROM users u
            WHERE u.store_name IS NOT NULL
            ORDER BY u.store_name
        ");
    }
}

// 使用例とヘルパー関数

/**
 * データベースインスタンスを取得
 */
function getDB() {
    return Database::getInstance();
}

/**
 * 接続テスト用関数
 */
function testConnection() {
    try {
        $db = Database::getInstance();
        $result = $db->selectOne("SELECT 'Connection OK' as status");
        return $result['status'] === 'Connection OK';
    } catch (Exception $e) {
        return false;
    }
}
?>