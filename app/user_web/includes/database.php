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
            // #region agent log
            file_put_contents('c:\Users\user\GitHub\RWC25-group07\.cursor\debug.log', json_encode(['id'=>'log_'.time().'_conn','timestamp'=>time()*1000,'location'=>'database.php:18','message'=>'DB接続開始','data'=>['host'=>self::HOST,'dbname'=>self::DBNAME,'username'=>self::USERNAME],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A,B,C'])."\n", FILE_APPEND);
            // #endregion
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
            // #region agent log
            $tables = $this->connection->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            file_put_contents('c:\Users\user\GitHub\RWC25-group07\.cursor\debug.log', json_encode(['id'=>'log_'.time().'_tables','timestamp'=>time()*1000,'location'=>'database.php:33','message'=>'存在するテーブル一覧','data'=>['tables'=>$tables,'sale_stores_exists'=>in_array('sale_stores',$tables)],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A,C'])."\n", FILE_APPEND);
            // #endregion
        } catch (PDOException $e) {
            // #region agent log
            file_put_contents('c:\Users\user\GitHub\RWC25-group07\.cursor\debug.log', json_encode(['id'=>'log_'.time().'_conn_err','timestamp'=>time()*1000,'location'=>'database.php:35','message'=>'DB接続エラー','data'=>['error'=>$e->getMessage()],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A,B,C'])."\n", FILE_APPEND);
            // #endregion
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
        // #region agent log
        file_put_contents('c:\Users\user\GitHub\RWC25-group07\.cursor\debug.log', json_encode(['id'=>'log_'.time().'_select_pre','timestamp'=>time()*1000,'location'=>'database.php:47','message'=>'SQL実行前','data'=>['sql'=>substr($sql,0,200),'params'=>$params,'has_sale_stores'=>strpos($sql,'sale_stores')!==false],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A,C'])."\n", FILE_APPEND);
        // #endregion
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll();
            // #region agent log
            file_put_contents('c:\Users\user\GitHub\RWC25-group07\.cursor\debug.log', json_encode(['id'=>'log_'.time().'_select_success','timestamp'=>time()*1000,'location'=>'database.php:50','message'=>'SQL実行成功','data'=>['row_count'=>count($result)],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A,C'])."\n", FILE_APPEND);
            // #endregion
            return $result;
        } catch (PDOException $e) {
            // #region agent log
            file_put_contents('c:\Users\user\GitHub\RWC25-group07\.cursor\debug.log', json_encode(['id'=>'log_'.time().'_select_err','timestamp'=>time()*1000,'location'=>'database.php:52','message'=>'SQL実行エラー','data'=>['error'=>$e->getMessage(),'code'=>$e->getCode(),'sql'=>substr($sql,0,200)],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A,C'])."\n", FILE_APPEND);
            // #endregion
            throw $e;
        }
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

    // ★修正箇所: ユーザーIDを受け取り、お気に入り店舗のみに絞り込む機能を追加
    public function getActiveSalesByStore($keyword = null, $userId = null) {
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
        ";

        // $userIdが指定されている場合、お気に入りテーブルとJOINして絞り込む
        if ($userId !== null) {
            $sql .= " JOIN user_favorite_stores ufs ON st.id = ufs.store_id AND ufs.user_id = ? ";
        }

        $sql .= "
            WHERE s.is_active = 1 
            AND s.start_date <= CURDATE() 
            AND s.end_date >= CURDATE()
            AND s.sale_price < p.regular_price
        ";

        $params = [];
        // パラメータの追加順序に注意（JOINの条件が先）
        if ($userId !== null) {
            $params[] = $userId;
        }

        if ($keyword) {
            $sql .= " AND (p.name LIKE ? OR c.name LIKE ? OR st.name LIKE ?)";
            $wildcard = "%{$keyword}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }
        
        $sql .= " ORDER BY st.id, s.start_date DESC";
        return $this->select($sql, $params);
    }

    public function searchCheapestSales($keyword = null) {
        // #region agent log
        file_put_contents('c:\Users\user\GitHub\RWC25-group07\.cursor\debug.log', json_encode(['id'=>'log_'.time().'_search_start','timestamp'=>time()*1000,'location'=>'database.php:127','message'=>'searchCheapestSales開始','data'=>['keyword'=>$keyword],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A,C'])."\n", FILE_APPEND);
        // #endregion
        $sql = "
            SELECT
                p.name as product_name,
                p.regular_price,
                s.sale_price,
                s.end_date,
                st.name as store_name,
                c.name as category_name,
                CASE WHEN s.sale_price < p.regular_price THEN 1 ELSE 0 END as is_sale
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

        $sql .= " ORDER BY s.sale_price ASC LIMIT 50";
        return $this->select($sql, $params);
    }

    public function searchProductPriceComparison($keywords) {
        $keywords = array_filter($keywords, function($v) { return trim($v) !== ''; });
        if (empty($keywords)) {
            return [];
        }

        $likeClauses = [];
        $params = [];
        foreach ($keywords as $k) {
            $likeClauses[] = "p.name LIKE ?";
            $params[] = "%{$k}%";
        }
        $whereClause = implode(' OR ', $likeClauses);

        $sql = "
            SELECT 
                st.id as store_id,
                st.name as store_name,
                p.name as product_name,
                s.sale_price as price,
                CASE WHEN s.sale_price < p.regular_price THEN 1 ELSE 0 END as is_sale,
                s.end_date as sale_end_date
            FROM sales s
            JOIN products p ON s.product_id = p.id
            JOIN sale_stores ss ON s.id = ss.sale_id
            JOIN stores st ON ss.store_id = st.id
            WHERE s.is_active = 1 
            AND s.start_date <= CURDATE() 
            AND s.end_date >= CURDATE()
            AND ($whereClause)

            UNION ALL

            SELECT 
                st.id as store_id,
                st.name as store_name,
                p.name as product_name,
                p.regular_price as price,
                0 as is_sale,
                NULL as sale_end_date
            FROM products p
            JOIN users u ON p.user_id = u.id
            JOIN stores st ON u.store_name = st.name
            WHERE ($whereClause)
        ";
        
        $allItems = $this->select($sql, array_merge($params, $params));

        $stores = [];

        foreach ($allItems as $item) {
            $storeId = $item['store_id'];
            if (!isset($stores[$storeId])) {
                $stores[$storeId] = [
                    'id' => $storeId,
                    'name' => $item['store_name'],
                    'found_items' => []
                ];
            }

            foreach ($keywords as $kw) {
                if (stripos($item['product_name'], $kw) !== false) {
                    if (!isset($stores[$storeId]['found_items'][$kw]) || 
                        $item['price'] < $stores[$storeId]['found_items'][$kw]['price']) {
                        
                        $stores[$storeId]['found_items'][$kw] = [
                            'name' => $item['product_name'],
                            'price' => $item['price'],
                            'is_sale' => $item['is_sale'],
                            'end_date' => $item['sale_end_date']
                        ];
                    }
                }
            }
        }

        $result = [];
        foreach ($stores as $storeId => $store) {
            $total = 0;
            $matchCount = 0;
            $itemsList = [];

            foreach ($keywords as $kw) {
                if (isset($store['found_items'][$kw])) {
                    $total += $store['found_items'][$kw]['price'];
                    $matchCount++;
                    $itemsList[] = $store['found_items'][$kw];
                } else {
                    $itemsList[] = ['name' => "{$kw} (なし)", 'price' => 0, 'is_missing' => true];
                }
            }

            if ($matchCount > 0) {
                $result[] = [
                    'store_id' => $storeId,
                    'store_name' => $store['name'],
                    'total_price' => $total,
                    'match_count' => $matchCount,
                    'items' => $itemsList
                ];
            }
        }

        usort($result, function($a, $b) {
            if ($a['match_count'] !== $b['match_count']) {
                return $b['match_count'] - $a['match_count'];
            }
            return $a['total_price'] - $b['total_price'];
        });

        return $result;
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

    /**
     * 全店舗データを取得（緯度・経度が設定されているもののみ）
     */
    public function getAllStores() {
        return $this->select("SELECT id, name, address, latitude, longitude, category FROM stores WHERE latitude IS NOT NULL AND longitude IS NOT NULL ORDER BY name");
    }

    /**
     * IDリストで店舗を取得
     */
    public function getStoresByIds($storeIds) {
        if (empty($storeIds)) return [];
        $placeholders = implode(',', array_fill(0, count($storeIds), '?'));
        return $this->select("SELECT id, name, address, latitude, longitude, category FROM stores WHERE id IN ($placeholders)", $storeIds);
    }

    /**
     * お気に入り店舗を取得
     */
    public function getFavoriteStores($userId) {
        return $this->select("
            SELECT s.id, s.name, s.address, s.latitude, s.longitude, s.category, ufs.created_at
            FROM user_favorite_stores ufs
            JOIN stores s ON ufs.store_id = s.id
            WHERE ufs.user_id = ?
            ORDER BY ufs.created_at DESC
        ", [$userId]);
    }

    /**
     * お気に入り商品を取得
     */
    public function getFavoriteProducts($userId) {
        return $this->select("
            SELECT p.id, p.name, p.regular_price, c.name as category_name, ufp.created_at
            FROM user_favorite_products ufp
            JOIN products p ON ufp.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE ufp.user_id = ?
            ORDER BY ufp.created_at DESC
        ", [$userId]);
    }

    /**
     * お気に入り店舗を追加
     */
    public function addFavoriteStore($userId, $storeId) {
        try {
            return $this->insert("
                INSERT INTO user_favorite_stores (user_id, store_id) 
                VALUES (?, ?)
            ", [$userId, $storeId]);
        } catch (PDOException $e) {
            // 既に存在する場合はエラーを無視（UNIQUE制約）
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * お気に入り店舗を削除
     */
    public function removeFavoriteStore($userId, $storeId) {
        return $this->delete("
            DELETE FROM user_favorite_stores 
            WHERE user_id = ? AND store_id = ?
        ", [$userId, $storeId]);
    }

    /**
     * お気に入り商品を追加
     */
    public function addFavoriteProduct($userId, $productId) {
        try {
            return $this->insert("
                INSERT INTO user_favorite_products (user_id, product_id) 
                VALUES (?, ?)
            ", [$userId, $productId]);
        } catch (PDOException $e) {
            // 既に存在する場合はエラーを無視（UNIQUE制約）
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * お気に入り商品を削除
     */
    public function removeFavoriteProduct($userId, $productId) {
        return $this->delete("
            DELETE FROM user_favorite_products 
            WHERE user_id = ? AND product_id = ?
        ", [$userId, $productId]);
    }

    /**
     * 店舗のセール情報を取得（簡易版）
     * セールが存在するかどうかのみを返す
     */
    public function getStoreSaleTimes($storeId) {
        $result = $this->selectOne("
            SELECT COUNT(*) as count
            FROM sales s
            JOIN sale_stores ss ON s.id = ss.sale_id
            WHERE ss.store_id = ? 
            AND s.is_active = 1
            AND s.start_date <= CURDATE()
            AND s.end_date >= CURDATE()
        ", [$storeId]);
        
        if ($result && $result['count'] > 0) {
            return [['has_sale' => true]];
        }
        return [];
    }

    // --- お気に入り機能用メソッド（リモート側のメソッドも追加） ---

    public function getUserFavoriteStores($userId) {
        $sql = "
            SELECT s.*, 
                   1 as is_favorite
            FROM stores s
            JOIN user_favorite_stores ufs ON s.id = ufs.store_id
            WHERE ufs.user_id = ?
        ";
        return $this->select($sql, [$userId]);
    }

    public function getUserFavoriteProducts($userId) {
        $sql = "
            SELECT p.name, MAX(p.id) as id 
            FROM products p
            JOIN user_favorite_products ufp ON p.id = ufp.product_id
            WHERE ufp.user_id = ?
            GROUP BY p.name
        ";
        return $this->select($sql, [$userId]);
    }

    public function searchStoresForFavorites($userId, $keyword) {
        $sql = "
            SELECT s.*, 
                   (SELECT COUNT(*) FROM user_favorite_stores WHERE store_id = s.id AND user_id = ?) as is_favorite
            FROM stores s
            WHERE s.name LIKE ?
        ";
        return $this->select($sql, [$userId, "%$keyword%"]);
    }

    public function searchProductsForFavorites($userId, $keyword) {
        $sql = "
            SELECT p.name, MIN(p.id) as id,
                   (SELECT COUNT(*) FROM user_favorite_products ufp JOIN products p2 ON ufp.product_id = p2.id WHERE p2.name = p.name AND ufp.user_id = ?) as is_favorite
            FROM products p
            WHERE p.name LIKE ?
            GROUP BY p.name
        ";
        return $this->select($sql, [$userId, "%$keyword%"]);
    }

    public function toggleFavoriteStore($userId, $storeId) {
        $exists = $this->selectOne("SELECT id FROM user_favorite_stores WHERE user_id = ? AND store_id = ?", [$userId, $storeId]);
        if ($exists) {
            $this->delete("DELETE FROM user_favorite_stores WHERE id = ?", [$exists['id']]);
            return false;
        } else {
            $this->insert("INSERT INTO user_favorite_stores (user_id, store_id) VALUES (?, ?)", [$userId, $storeId]);
            return true;
        }
    }

    public function toggleFavoriteProduct($userId, $productName) {
        $fav = $this->selectOne("
            SELECT ufp.id 
            FROM user_favorite_products ufp 
            JOIN products p ON ufp.product_id = p.id 
            WHERE ufp.user_id = ? AND p.name = ?
            LIMIT 1
        ", [$userId, $productName]);

        if ($fav) {
            $this->delete("
                DELETE ufp FROM user_favorite_products ufp 
                JOIN products p ON ufp.product_id = p.id 
                WHERE ufp.user_id = ? AND p.name = ?
            ", [$userId, $productName]);
            return false;
        } else {
            $prod = $this->selectOne("SELECT id FROM products WHERE name = ? LIMIT 1", [$productName]);
            if ($prod) {
                $this->insert("INSERT INTO user_favorite_products (user_id, product_id) VALUES (?, ?)", [$userId, $prod['id']]);
                return true;
            }
        }
        return false;
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