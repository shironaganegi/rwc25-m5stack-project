<?php
/**
 * ダミーデータ生成スクリプト
 * 使用方法: docker exec -it shop_web php /var/www/html/generate_dummy_data.php
 * または: php generate_dummy_data.php (ローカル実行時)
 */

require_once __DIR__ . '/includes/database.php';

echo "=== ダミーデータ生成開始 ===\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // トランザクション開始
    $pdo->beginTransaction();
    
    $userId = 1; // デフォルトユーザーID
    
    // 1. カテゴリデータの確認と追加
    echo "1. カテゴリデータを確認中...\n";
    $categories = $db->select("SELECT id, name FROM categories");
    $categoryMap = [];
    foreach ($categories as $cat) {
        $categoryMap[$cat['name']] = $cat['id'];
    }
    
    // カテゴリが不足している場合は追加
    $requiredCategories = [
        '食品' => 1,
        '飲料' => 2,
        '日用品' => 3,
        '冷凍食品' => 4,
        'お菓子' => 5,
        '野菜' => 6,
        '肉・魚' => 7,
        '乳製品' => 8
    ];
    
    foreach ($requiredCategories as $catName => $order) {
        if (!isset($categoryMap[$catName])) {
            $catId = $db->insert("INSERT INTO categories (name, user_id) VALUES (?, ?)", [$catName, $userId]);
            $categoryMap[$catName] = $catId;
            echo "  カテゴリ追加: {$catName} (ID: {$catId})\n";
        }
    }
    echo "  カテゴリ数: " . count($categoryMap) . "件\n\n";
    
    // 2. 商品データの追加
    echo "2. 商品データを追加中...\n";
    $products = [
        ['りんご', '食品', 150, 100],
        ['バナナ', '食品', 120, 80],
        ['トマト', '野菜', 200, 50],
        ['レタス', '野菜', 150, 30],
        ['牛乳', '乳製品', 198, 60],
        ['ヨーグルト', '乳製品', 128, 40],
        ['鶏もも肉', '肉・魚', 398, 25],
        ['鮭', '肉・魚', 498, 15],
        ['コーヒー', '飲料', 298, 50],
        ['お茶', '飲料', 128, 80],
        ['洗剤', '日用品', 380, 25],
        ['トイレットペーパー', '日用品', 298, 20],
        ['冷凍うどん', '冷凍食品', 120, 80],
        ['冷凍ピザ', '冷凍食品', 398, 30],
        ['チョコレート', 'お菓子', 200, 60],
        ['ポテトチップス', 'お菓子', 150, 45],
        ['おにぎり', '食品', 120, 35],
        ['弁当', '食品', 450, 20],
        ['刺し身', '肉・魚', 598, 10],
        ['卵', '食品', 250, 40]
    ];
    
    $productMap = [];
    foreach ($products as $product) {
        list($name, $categoryName, $price, $stock) = $product;
        $categoryId = $categoryMap[$categoryName] ?? null;
        
        if ($categoryId) {
            // 既存商品チェック
            $existing = $db->selectOne("SELECT id FROM products WHERE name = ?", [$name]);
            if ($existing) {
                $productMap[$name] = $existing['id'];
                echo "  既存商品: {$name} (ID: {$existing['id']})\n";
            } else {
                $productId = $db->insert("
                    INSERT INTO products (name, category_id, regular_price, stock_quantity, user_id) 
                    VALUES (?, ?, ?, ?, ?)
                ", [$name, $categoryId, $price, $stock, $userId]);
                $productMap[$name] = $productId;
                echo "  商品追加: {$name} (ID: {$productId})\n";
            }
        }
    }
    echo "  商品数: " . count($productMap) . "件\n\n";
    
    // 3. 店舗データの確認
    echo "3. 店舗データを確認中...\n";
    $stores = $db->select("SELECT id, name FROM stores LIMIT 5");
    $storeIds = array_column($stores, 'id');
    if (empty($storeIds)) {
        echo "  警告: 店舗データがありません。fetch_stores.phpを実行してください。\n";
        // 最低限の店舗データを追加
        $storeIds[] = $db->insert("
            INSERT INTO stores (name, address, latitude, longitude, category) 
            VALUES (?, ?, ?, ?, ?)
        ", ['テスト店舗', '大阪府東大阪市', 34.662625, 135.589097, 'supermarket']);
        echo "  テスト店舗を追加しました (ID: {$storeIds[0]})\n";
    }
    echo "  店舗数: " . count($storeIds) . "件\n\n";
    
    // 4. セールデータの追加
    echo "4. セールデータを追加中...\n";
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $nextWeek = date('Y-m-d', strtotime('+7 days'));
    
    $saleProducts = [
        ['りんご', 120, $today, $tomorrow, '期間限定セール'],
        ['トマト', 150, $today, $tomorrow, '朝採れセール'],
        ['牛乳', 168, $today, $nextWeek, '週末セール'],
        ['鶏もも肉', 298, $today, $nextWeek, 'お肉セール'],
        ['コーヒー', 248, $today, $nextWeek, 'コーヒーセール'],
        ['洗剤', 320, $today, $tomorrow, '日用品セール'],
        ['冷凍うどん', 98, $today, $tomorrow, '冷凍食品セール'],
        ['チョコレート', 150, $today, $nextWeek, 'お菓子セール']
    ];
    
    $saleIds = [];
    foreach ($saleProducts as $sale) {
        list($productName, $salePrice, $startDate, $endDate, $notes) = $sale;
        $productId = $productMap[$productName] ?? null;
        
        if ($productId) {
            // 既存セールチェック
            $existing = $db->selectOne("
                SELECT id FROM sales 
                WHERE product_id = ? AND start_date = ? AND end_date = ?
            ", [$productId, $startDate, $endDate]);
            
            if (!$existing) {
                $saleId = $db->insert("
                    INSERT INTO sales (product_id, sale_price, start_date, end_date, notes, is_active, user_id) 
                    VALUES (?, ?, ?, ?, ?, 1, ?)
                ", [$productId, $salePrice, $startDate, $endDate, $notes, $userId]);
                $saleIds[] = $saleId;
                
                // セール対象店舗を設定（最初の店舗に設定）
                if (!empty($storeIds)) {
                    $storeId = $storeIds[0];
                    try {
                        $db->insert("
                            INSERT INTO sale_stores (sale_id, store_id) 
                            VALUES (?, ?)
                        ", [$saleId, $storeId]);
                    } catch (Exception $e) {
                        // 既に存在する場合はスキップ
                    }
                }
                
                echo "  セール追加: {$productName} ({$salePrice}円, {$startDate}〜{$endDate})\n";
            }
        }
    }
    echo "  セール数: " . count($saleIds) . "件\n\n";
    
    // 5. 売上データの追加（今日と前日）
    echo "5. 売上データを追加中...\n";
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    // 今日の売上データを確認
    $todaySales = $db->selectOne("SELECT COUNT(*) as count FROM sales_data WHERE sale_date = ?", [$today]);
    if ($todaySales['count'] == 0) {
        // 今日の売上データを追加
        $todayProducts = ['りんご', 'トマト', '牛乳', '鶏もも肉', 'コーヒー', '洗剤', '冷凍うどん', 'チョコレート'];
        $todayTotal = 0;
        foreach ($todayProducts as $productName) {
            $productId = $productMap[$productName] ?? null;
            if ($productId) {
                $quantity = rand(1, 5);
                $unitPrice = rand(100, 500);
                $totalAmount = $quantity * $unitPrice;
                $todayTotal += $totalAmount;
                
                $db->insert("
                    INSERT INTO sales_data (product_id, quantity_sold, unit_price, total_amount, sale_date, user_id) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ", [$productId, $quantity, $unitPrice, $totalAmount, $today, $userId]);
            }
        }
        echo "  今日の売上データ追加: " . number_format($todayTotal) . "円\n";
    } else {
        echo "  今日の売上データは既に存在します\n";
    }
    
    // 前日の売上データを確認
    $yesterdaySales = $db->selectOne("SELECT COUNT(*) as count FROM sales_data WHERE sale_date = ?", [$yesterday]);
    if ($yesterdaySales['count'] == 0) {
        // 前日の売上データを追加（今日より少し少なめ）
        $yesterdayProducts = ['りんご', 'バナナ', '牛乳', 'ヨーグルト', 'コーヒー'];
        $yesterdayTotal = 0;
        foreach ($yesterdayProducts as $productName) {
            $productId = $productMap[$productName] ?? null;
            if ($productId) {
                $quantity = rand(1, 4);
                $unitPrice = rand(100, 450);
                $totalAmount = $quantity * $unitPrice;
                $yesterdayTotal += $totalAmount;
                
                $db->insert("
                    INSERT INTO sales_data (product_id, quantity_sold, unit_price, total_amount, sale_date, user_id) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ", [$productId, $quantity, $unitPrice, $totalAmount, $yesterday, $userId]);
            }
        }
        echo "  前日の売上データ追加: " . number_format($yesterdayTotal) . "円\n";
    } else {
        echo "  前日の売上データは既に存在します\n";
    }
    echo "\n";
    
    // 6. 売上目標データの追加
    echo "6. 売上目標データを確認中...\n";
    $target = $db->selectOne("SELECT id FROM sales_targets WHERE target_date = ?", [$today]);
    if (!$target) {
        $db->insert("
            INSERT INTO sales_targets (target_amount, target_period, target_date, user_id) 
            VALUES (?, 'daily', ?, ?)
        ", [580000, $today, $userId]);
        echo "  今日の売上目標を追加: 580,000円\n";
    } else {
        echo "  今日の売上目標は既に存在します\n";
    }
    
    $yesterdayTarget = $db->selectOne("SELECT id FROM sales_targets WHERE target_date = ?", [$yesterday]);
    if (!$yesterdayTarget) {
        $db->insert("
            INSERT INTO sales_targets (target_amount, target_period, target_date, user_id) 
            VALUES (?, 'daily', ?, ?)
        ", [550000, $yesterday, $userId]);
        echo "  前日の売上目標を追加: 550,000円\n";
    }
    echo "\n";
    
    // 7. お気に入り店舗データの追加（user_web用）
    echo "7. お気に入り店舗データを追加中...\n";
    $favUserId = 1; // user_webのユーザーID
    $favStoreCount = 0;
    foreach (array_slice($storeIds, 0, 2) as $storeId) {
        try {
            $db->insert("
                INSERT INTO user_favorite_stores (user_id, store_id) 
                VALUES (?, ?)
            ", [$favUserId, $storeId]);
            $favStoreCount++;
            echo "  お気に入り店舗追加: Store ID {$storeId}\n";
        } catch (Exception $e) {
            // 既に存在する場合はスキップ
        }
    }
    echo "  お気に入り店舗数: {$favStoreCount}件\n\n";
    
    // 8. お気に入り商品データの追加（user_web用）
    echo "8. お気に入り商品データを追加中...\n";
    $favProducts = ['りんご', 'トマト', '牛乳', 'おにぎり', '弁当'];
    $favProductCount = 0;
    foreach ($favProducts as $productName) {
        $productId = $productMap[$productName] ?? null;
        if ($productId) {
            try {
                $db->insert("
                    INSERT INTO user_favorite_products (user_id, product_id) 
                    VALUES (?, ?)
                ", [$favUserId, $productId]);
                $favProductCount++;
                echo "  お気に入り商品追加: {$productName}\n";
            } catch (Exception $e) {
                // 既に存在する場合はスキップ
            }
        }
    }
    echo "  お気に入り商品数: {$favProductCount}件\n\n";
    
    // トランザクションコミット
    $pdo->commit();
    
    echo "=== ダミーデータ生成完了 ===\n\n";
    echo "生成されたデータ:\n";
    echo "- カテゴリ: " . count($categoryMap) . "件\n";
    echo "- 商品: " . count($productMap) . "件\n";
    echo "- セール: " . count($saleIds) . "件\n";
    echo "- お気に入り店舗: {$favStoreCount}件\n";
    echo "- お気に入り商品: {$favProductCount}件\n";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("\nエラー: " . $e->getMessage() . "\n");
}
