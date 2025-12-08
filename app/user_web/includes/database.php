<?php
/**
 * データベース接続ユーティリティクラス
 * スーパーマーケットアプリ用データベース接続管理
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // データベース設定
    private const HOST = 'mysql';
    private const PORT = 3306;
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
    
    // 基本メソッド
    public function select($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function selectOne($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function insert($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $this->connection->lastInsertId();
    }
    
    public function update($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    public function delete($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    // アプリ専用メソッド

    public function getAllProducts() {
        return $this->select("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name");
    }
    
    public function getProductsByCategory($categoryId) {
        return $this->select("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? ORDER BY p.name", [$categoryId]);
    }
    
    public function getActiveSales() {
        return $this->select("SELECT s.*, p.name as product_name FROM sales s JOIN products p ON s.product_id = p.id WHERE s.is_active = 1 AND s.start_date <= CURDATE() AND s.end_date >= CURDATE() ORDER BY s.start_date DESC");
    }

    public function getActiveSalesByStore($keyword = null) {
        $sql = "
            SELECT 
                st.id as store_id,
                st.name as store_name,
                st.category as store_category,
                s.id as sale_id,
                s.sale_price,
                s.end_date,
                p.name as product_name,
                p.regular_price,
                c.name as category_name
            FROM sales s
            JOIN products p ON s.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            JOIN sale_stores ss ON s.id = ss.sale_id
            JOIN stores st ON ss.store_id = st.id
            WHERE s.is_active = 1 
            AND s.start_date <= CURDATE() 
            AND s.end_date >= CURDATE()
        ";
        $params = [];
        if ($keyword) {
            $sql .= " AND (p.name LIKE ? OR c.name LIKE ? OR st.name LIKE ?)";
            $wildcard = "%{$keyword}%";
            $params = [$wildcard, $wildcard, $wildcard];
        }
        $sql .= " ORDER BY st.id, s.start_date DESC";
        return $this->select($sql, $params);
    }

    // ★★★ これが不足していたメソッドです ★★★
    public function searchCheapestSales($keyword = null) {
        $sql = "
            SELECT
                p.name as product_name,
                p.regular_price,
                s.sale_price,
                s.end_date,
                st.name as store_name,
                c.name as category_name
            FROM sales s
            JOIN products p ON s.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            JOIN sale_stores ss ON s.id = ss.sale_id
            JOIN stores st ON ss.store_id = st.id
            WHERE s.is_active = 1
            AND s.start_date <= CURDATE()
            AND s.end_date >= CURDATE()
        ";

        $params = [];
        if ($keyword) {
            $sql .= " AND (p.name LIKE ? OR c.name LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }

        // 安い順にソートして上位20件を取得
        $sql .= " ORDER BY s.sale_price ASC LIMIT 20";

        return $this->select($sql, $params);
    }
    
    public function getLowStockProducts($threshold = 10) {
        return $this->select("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock_quantity <= ? ORDER BY p.stock_quantity ASC", [$threshold]);
    }
    
    public function getSalesTargetStatus($userId, $date = null) {
        if ($date === null) { $date = date('Y-m-d'); }
        $target = $this->selectOne("SELECT * FROM sales_targets WHERE user_id = ? AND target_date = ?", [$userId, $date]);
        if (!$target) { return null; }
        $actualSales = $this->selectOne("SELECT SUM(total_amount) as total FROM sales_data WHERE user_id = ? AND sale_date = ?", [$userId, $date]);
        $target['actual_amount'] = $actualSales['total'] ?? 0;
        $target['achievement_rate'] = $target['target_amount'] > 0 ? ($target['actual_amount'] / $target['target_amount']) * 100 : 0;
        return $target;
    }
    
    public function searchProducts($keyword) {
        return $this->select("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE ? OR c.name LIKE ? ORDER BY p.name", ["%$keyword%", "%$keyword%"]);
    }

    // 冷蔵庫機能用メソッド
    public function getRefrigeratorItems($userId) {
        return $this->select("SELECT * FROM user_refrigerator WHERE user_id = ? ORDER BY expiry_date ASC", [$userId]);
    }

    public function addRefrigeratorItem($userId, $productName, $quantity, $expiryDate) {
        return $this->insert(
            "INSERT INTO user_refrigerator (user_id, product_name, quantity, expiry_date) VALUES (?, ?, ?, ?)",
            [$userId, $productName, $quantity, $expiryDate]
        );
    }

    public function deleteRefrigeratorItem($id, $userId) {
        return $this->delete("DELETE FROM user_refrigerator WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

} // Class End

function getDB() { return Database::getInstance(); }

function testConnection() {
    try {
        $db = Database::getInstance();
        $result = $db->selectOne("SELECT 'Connection OK' as status");
        return $result['status'] === 'Connection OK';
    } catch (Exception $e) { return false; }
}
?>