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
            // 文字エンコーディングを明示的に設定
            $this->connection->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
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
    
    // 店舗管理アプリ用の専用メソッド
    
    /**
     * 今日の売上合計を取得
     */
    public function getTodaysSales() {
        return $this->selectOne("
            SELECT SUM(total_amount) as total_sales,
                   COUNT(*) as transaction_count
            FROM sales_data 
            WHERE sale_date = CURDATE()
        ");
    }
    
    /**
     * 前日の売上合計を取得
     */
    public function getYesterdaysSales() {
        return $this->selectOne("
            SELECT SUM(total_amount) as total_sales,
                   COUNT(*) as transaction_count
            FROM sales_data 
            WHERE sale_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        ");
    }
    
    /**
     * 売上目標と達成率を取得
     */
    public function getSalesTarget() {
        return $this->selectOne("
            SELECT st.target_amount,
                   COALESCE(SUM(sd.total_amount), 0) as actual_amount,
                   CASE 
                       WHEN st.target_amount > 0 THEN 
                           ROUND((COALESCE(SUM(sd.total_amount), 0) / st.target_amount) * 100, 1)
                       ELSE 0 
                   END as achievement_rate
            FROM sales_targets st
            LEFT JOIN sales_data sd ON st.target_date = sd.sale_date
            WHERE st.target_date = CURDATE()
            GROUP BY st.target_amount
        ");
    }
    
    /**
     * 在庫が少ない商品の数を取得
     */
    public function getLowStockCount($threshold = 10) {
        return $this->selectOne("
            SELECT COUNT(*) as count
            FROM products 
            WHERE stock_quantity <= ?
        ", [$threshold]);
    }
    
    /**
     * 在庫が少ない商品一覧を取得
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
     * アクティブなセール情報の数を取得
     */
    public function getActiveSalesCount() {
        return $this->selectOne("
            SELECT COUNT(*) as count
            FROM sales 
            WHERE is_active = 1 
            AND start_date <= CURDATE() 
            AND end_date >= CURDATE()
        ");
    }
    
    /**
     * アクティブなセール情報を取得
     */
    public function getActiveSales() {
        return $this->select("
            SELECT s.*, p.name as product_name, c.name as category_name
            FROM sales s 
            JOIN products p ON s.product_id = p.id 
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE s.is_active = 1 
            AND s.start_date <= CURDATE() 
            AND s.end_date >= CURDATE()
            ORDER BY s.start_date DESC
        ");
    }
    
    /**
     * 期限が近いセール情報（明日まで）を取得
     */
    public function getExpiringSales() {
        return $this->select("
            SELECT s.*, p.name as product_name, c.name as category_name
            FROM sales s 
            JOIN products p ON s.product_id = p.id 
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE s.is_active = 1 
            AND s.end_date <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            AND s.end_date >= CURDATE()
            ORDER BY s.end_date ASC
        ");
    }
    
    /**
     * 全カテゴリを取得
     */
    public function getAllCategories() {
        return $this->select("SELECT * FROM categories ORDER BY name");
    }
    
    /**
     * 商品を追加
     */
    public function addProduct($name, $category_id, $regular_price, $stock_quantity, $description = '') {
        return $this->insert("
            INSERT INTO products (name, category_id, regular_price, stock_quantity, description) 
            VALUES (?, ?, ?, ?, ?)
        ", [$name, $category_id, $regular_price, $stock_quantity, $description]);
    }
    
    /**
     * セール情報を追加
     */
    public function addSale($product_id, $sale_price, $start_date, $end_date, $user_id, $notes = '') {
        // 通常価格を取得して割引額を計算
        $product = $this->selectOne("SELECT regular_price FROM products WHERE id = ?", [$product_id]);
        $regular_price = $product['regular_price'] ?? $sale_price;
        $discount_amount = $regular_price - $sale_price;
        
        return $this->insert("
            INSERT INTO sales (product_id, sale_price, discount_amount, start_date, end_date, notes, is_active, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, 1, ?)
        ", [$product_id, $sale_price, $discount_amount, $start_date, $end_date, $notes, $user_id]);
    }
    
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
     * 全店舗を取得
     */
    public function getAllStores() {
        return $this->select("SELECT id, name, address, category FROM stores ORDER BY name");
    }
    
    /**
     * セールと店舗の関連付け
     */
    public function linkSaleToStores($saleId, $storeIds) {
        if (empty($storeIds)) {
            return 0;
        }
        
        $count = 0;
        foreach ($storeIds as $storeId) {
            try {
                $this->insert("
                    INSERT INTO sale_stores (sale_id, store_id) 
                    VALUES (?, ?)
                ", [$saleId, $storeId]);
                $count++;
            } catch (PDOException $e) {
                // 既に存在する場合はスキップ（UNIQUE制約）
                if ($e->getCode() != 23000) {
                    throw $e;
                }
            }
        }
        return $count;
    }
    
    /**
     * 期間別売上を取得
     */
    public function getSalesByPeriod($startDate, $endDate, $userId = null) {
        $sql = "
            SELECT SUM(total_amount) as total_sales,
                   COUNT(*) as transaction_count
            FROM sales_data 
            WHERE sale_date >= ? AND sale_date <= ?
        ";
        $params = [$startDate, $endDate];
        
        if ($userId !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        return $this->selectOne($sql, $params);
    }
    
    /**
     * 日別売上を取得
     */
    public function getDailySales($startDate, $endDate, $userId = null) {
        $sql = "
            SELECT sale_date,
                   SUM(total_amount) as daily_total,
                   COUNT(*) as transaction_count
            FROM sales_data 
            WHERE sale_date >= ? AND sale_date <= ?
        ";
        $params = [$startDate, $endDate];
        
        if ($userId !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " GROUP BY sale_date ORDER BY sale_date ASC";
        
        return $this->select($sql, $params);
    }
    
    /**
     * 期間比較（現在期間と前期間の比較）
     */
    public function getSalesComparison($currentStart, $currentEnd, $previousStart, $previousEnd, $userId = null) {
        $current = $this->getSalesByPeriod($currentStart, $currentEnd, $userId);
        $previous = $this->getSalesByPeriod($previousStart, $previousEnd, $userId);
        
        $currentTotal = $current['total_sales'] ?? 0;
        $previousTotal = $previous['total_sales'] ?? 0;
        
        $change = $currentTotal - $previousTotal;
        $changePercent = 0;
        if ($previousTotal > 0) {
            $changePercent = round(($change / $previousTotal) * 100, 1);
        } elseif ($currentTotal > 0) {
            $changePercent = 100;
        }
        
        return [
            'current' => $currentTotal,
            'previous' => $previousTotal,
            'change' => $change,
            'change_percent' => $changePercent
        ];
    }
}

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