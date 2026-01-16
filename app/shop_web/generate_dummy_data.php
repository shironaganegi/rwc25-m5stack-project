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
                
                // セール対象店舗を設定（複数店舗に関連付け）
                if (!empty($storeIds)) {
                    // ランダムに1〜3店舗を選択
                    $selectedStoreCount = min(rand(1, 3), count($storeIds));
                    $selectedStores = array_slice($storeIds, 0, $selectedStoreCount);
                    
                    foreach ($selectedStores as $storeId) {
                        try {
                            $db->insert("
                                INSERT INTO sale_stores (sale_id, store_id) 
                                VALUES (?, ?)
                            ", [$saleId, $storeId]);
                        } catch (Exception $e) {
                            // 既に存在する場合はスキップ
                        }
                    }
                }
                
                echo "  セール追加: {$productName} ({$salePrice}円, {$startDate}〜{$endDate})\n";
            }
        }
    }
    echo "  セール数: " . count($saleIds) . "件\n\n";
    
    // 5. 売上データの追加（過去3ヶ月分の日別データを生成）
    echo "5. 売上データを追加中...\n";
    $today = date('Y-m-d');
    
    // 過去3ヶ月分のデータを生成
    $startDate = date('Y-m-d', strtotime('-3 months'));
    $endDate = $today;
    
    // 既存データを確認
    $existingData = $db->selectOne("
        SELECT COUNT(DISTINCT sale_date) as count 
        FROM sales_data 
        WHERE sale_date >= ? AND sale_date <= ?
    ", [$startDate, $endDate]);
    
    if ($existingData['count'] < 30) {
        // 人気商品リスト（売上に影響を与える商品）
        $popularProducts = [
            'りんご' => ['base_price' => 150, 'popularity' => 8],
            'トマト' => ['base_price' => 200, 'popularity' => 7],
            '牛乳' => ['base_price' => 198, 'popularity' => 9],
            '鶏もも肉' => ['base_price' => 398, 'popularity' => 6],
            'コーヒー' => ['base_price' => 298, 'popularity' => 8],
            '洗剤' => ['base_price' => 380, 'popularity' => 5],
            '冷凍うどん' => ['base_price' => 120, 'popularity' => 7],
            'チョコレート' => ['base_price' => 200, 'popularity' => 6],
            'バナナ' => ['base_price' => 120, 'popularity' => 7],
            'ヨーグルト' => ['base_price' => 128, 'popularity' => 8],
            '卵' => ['base_price' => 250, 'popularity' => 9],
            'おにぎり' => ['base_price' => 120, 'popularity' => 8],
            '弁当' => ['base_price' => 450, 'popularity' => 7],
            'レタス' => ['base_price' => 150, 'popularity' => 5],
            'お茶' => ['base_price' => 128, 'popularity' => 6]
        ];
        
        $totalGenerated = 0;
        $currentDate = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        
        while ($currentDate <= $endTimestamp) {
            $dateStr = date('Y-m-d', $currentDate);
            $dayOfWeek = date('w', $currentDate); // 0=日曜, 6=土曜
            
            // 既にデータがある日はスキップ
            $existing = $db->selectOne("SELECT COUNT(*) as count FROM sales_data WHERE sale_date = ?", [$dateStr]);
            if ($existing['count'] > 0) {
                $currentDate = strtotime('+1 day', $currentDate);
                continue;
            }
            
            // 週末は売上が高め、平日は低め
            $weekendMultiplier = ($dayOfWeek == 0 || $dayOfWeek == 6) ? 1.3 : 1.0;
            
            // 1日あたりの取引数を決定（週末は多め）
            $transactionCount = ($dayOfWeek == 0 || $dayOfWeek == 6) ? rand(15, 25) : rand(10, 18);
            
            $dailyTotal = 0;
            
            for ($i = 0; $i < $transactionCount; $i++) {
                // 商品をランダムに選択（人気度に基づいて重み付け）
                $selectedProduct = null;
                $selectedProductName = null;
                $rand = rand(1, 100);
                $cumulative = 0;
                
                foreach ($popularProducts as $productName => $info) {
                    $productId = $productMap[$productName] ?? null;
                    if ($productId) {
                        $cumulative += $info['popularity'];
                        if ($rand <= $cumulative) {
                            $selectedProduct = $productId;
                            $selectedProductName = $productName;
                            break;
                        }
                    }
                }
                
                if ($selectedProduct && $selectedProductName) {
                    $basePrice = $popularProducts[$selectedProductName]['base_price'];
                    $quantity = rand(1, 5);
                    // 価格に変動を加える（±20%）
                    $priceVariation = 1 + (rand(-20, 20) / 100);
                    $unitPrice = round($basePrice * $priceVariation * $weekendMultiplier);
                    $totalAmount = $quantity * $unitPrice;
                    $dailyTotal += $totalAmount;
                    
                    $db->insert("
                        INSERT INTO sales_data (product_id, quantity_sold, unit_price, total_amount, sale_date, user_id) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ", [$selectedProduct, $quantity, $unitPrice, $totalAmount, $dateStr, $userId]);
                }
            }
            
            $totalGenerated++;
            if ($totalGenerated % 10 == 0) {
                echo "  {$dateStr} のデータを生成 (売上: " . number_format($dailyTotal) . "円)\n";
            }
            
            $currentDate = strtotime('+1 day', $currentDate);
        }
        
        echo "  過去3ヶ月分の売上データを生成しました: {$totalGenerated}日分\n";
    } else {
        echo "  売上データは既に十分に存在します (" . $existingData['count'] . "日分)\n";
    }
    echo "\n";
    
    // 6. 売上目標データの追加（過去3ヶ月分の日次目標）
    echo "6. 売上目標データを追加中...\n";
    $today = date('Y-m-d');
    
    // 過去3ヶ月分の日次目標を生成
    $targetStartDate = date('Y-m-d', strtotime('-3 months'));
    $targetEndDate = $today;
    
    $targetDate = strtotime($targetStartDate);
    $targetEndTimestamp = strtotime($targetEndDate);
    $targetCount = 0;
    
    // ベース目標額（平日と週末で変動）
    $baseDailyTarget = 580000;
    
    while ($targetDate <= $targetEndTimestamp) {
        $dateStr = date('Y-m-d', $targetDate);
        $dayOfWeek = date('w', $targetDate);
        
        // 既存チェック
        $existing = $db->selectOne("SELECT id FROM sales_targets WHERE target_date = ?", [$dateStr]);
        if (!$existing) {
            // 週末は目標を高めに設定
            $dailyTarget = ($dayOfWeek == 0 || $dayOfWeek == 6) 
                ? round($baseDailyTarget * 1.2) 
                : $baseDailyTarget;
            
            // 月によって目標を微調整（月の後半は高め）
            $dayOfMonth = date('d', $targetDate);
            if ($dayOfMonth > 20) {
                $dailyTarget = round($dailyTarget * 1.1);
            }
            
            $db->insert("
                INSERT INTO sales_targets (target_amount, target_period, target_date, user_id) 
                VALUES (?, 'daily', ?, ?)
            ", [$dailyTarget, $dateStr, $userId]);
            $targetCount++;
        }
        
        $targetDate = strtotime('+1 day', $targetDate);
    }
    
    if ($targetCount > 0) {
        echo "  過去3ヶ月分の売上目標を追加: {$targetCount}日分\n";
    } else {
        echo "  売上目標データは既に存在します\n";
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
    
    // 統計情報を取得
    $salesDataCount = $db->selectOne("SELECT COUNT(DISTINCT sale_date) as count FROM sales_data");
    $totalSalesAmount = $db->selectOne("SELECT SUM(total_amount) as total FROM sales_data");
    $targetDataCount = $db->selectOne("SELECT COUNT(DISTINCT target_date) as count FROM sales_targets");
    
    echo "=== ダミーデータ生成完了 ===\n\n";
    echo "📊 生成されたデータサマリー:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📁 マスタデータ:\n";
    echo "   - カテゴリ: " . count($categoryMap) . "件\n";
    echo "   - 商品: " . count($productMap) . "件\n";
    echo "   - 店舗: " . count($storeIds) . "件\n";
    echo "\n";
    echo "💰 売上データ:\n";
    echo "   - 売上記録日数: " . ($salesDataCount['count'] ?? 0) . "日分\n";
    echo "   - 総売上金額: " . number_format($totalSalesAmount['total'] ?? 0) . "円\n";
    echo "   - 売上目標データ: " . ($targetDataCount['count'] ?? 0) . "日分\n";
    echo "\n";
    echo "🏷️ セール情報:\n";
    echo "   - アクティブセール: " . count($saleIds) . "件\n";
    echo "\n";
    echo "⭐ お気に入りデータ:\n";
    echo "   - お気に入り店舗: {$favStoreCount}件\n";
    echo "   - お気に入り商品: {$favProductCount}件\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n";
    echo "✅ 分析画面で以下の期間を確認できます:\n";
    echo "   - 今月: " . date('Y年m月') . "\n";
    echo "   - 先月: " . date('Y年m月', strtotime('-1 month')) . "\n";
    echo "   - 今週: " . date('m/d', strtotime('monday this week')) . " 〜 " . date('m/d', strtotime('sunday this week')) . "\n";
    echo "\n";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("\nエラー: " . $e->getMessage() . "\n");
}
