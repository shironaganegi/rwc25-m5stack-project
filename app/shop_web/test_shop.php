<?php
// データベース接続テスト
require_once 'includes/database.php';

echo "<h1>Shop Web - データベース接続テスト</h1>";

try {
    $db = getDB();
    echo "<p style='color: green;'>✅ データベース接続: 成功</p>";
    
    // 今日の売上情報を取得
    echo "<h2>売上情報</h2>";
    $todaysSales = $db->getTodaysSales();
    echo "<pre>" . print_r($todaysSales, true) . "</pre>";
    
    // 売上目標を取得
    echo "<h2>売上目標</h2>";
    $salesTarget = $db->getSalesTarget();
    echo "<pre>" . print_r($salesTarget, true) . "</pre>";
    
    // 在庫アラート
    echo "<h2>在庫アラート</h2>";
    $lowStockCount = $db->getLowStockCount();
    echo "<p>在庫不足商品数: " . ($lowStockCount['count'] ?? 0) . "</p>";
    
    $lowStockProducts = $db->getLowStockProducts(10);
    echo "<h3>在庫不足商品一覧:</h3>";
    foreach ($lowStockProducts as $product) {
        echo "<p>- {$product['name']}: {$product['stock_quantity']}個 (カテゴリ: {$product['category_name']})</p>";
    }
    
    // セール情報
    echo "<h2>アクティブなセール</h2>";
    $activeSales = $db->getActiveSales();
    echo "<p>アクティブセール数: " . count($activeSales) . "</p>";
    foreach ($activeSales as $sale) {
        echo "<p>- {$sale['product_name']}: ¥{$sale['sale_price']} ({$sale['start_date']} 〜 {$sale['end_date']})</p>";
    }
    
    // 期限が近いセール
    echo "<h2>期限が近いセール</h2>";
    $expiringSales = $db->getExpiringSales();
    echo "<p>明日までのセール数: " . count($expiringSales) . "</p>";
    foreach ($expiringSales as $sale) {
        echo "<p>- {$sale['product_name']}: ¥{$sale['sale_price']} (終了: {$sale['end_date']})</p>";
    }
    
    // 全商品
    echo "<h2>商品一覧</h2>";
    $allProducts = $db->getAllProducts();
    echo "<p>登録商品数: " . count($allProducts) . "</p>";
    foreach (array_slice($allProducts, 0, 5) as $product) {
        echo "<p>- {$product['name']}: ¥{$product['regular_price']} (在庫: {$product['stock_quantity']}個)</p>";
    }
    
    // カテゴリ
    echo "<h2>カテゴリ一覧</h2>";
    $categories = $db->getAllCategories();
    echo "<p>カテゴリ数: " . count($categories) . "</p>";
    foreach ($categories as $category) {
        echo "<p>- {$category['name']}</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ エラー: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>