<?php
// データベース接続テスト用スクリプト

// データベース接続設定
// Dockerコンテナ間通信の場合はコンテナ名を使用
$host = 'mysql';  // Dockerコンテナ名
$port = 3306;     // コンテナ内部のポート
$dbname = 'supermarket_app';
$username = 'dev';
$password = 'dev';

echo "<h1>🗄️ データベース接続テスト - supermarket_app</h1>";
echo "<div style='background: #f0f8ff; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
echo "<p><strong>接続設定:</strong></p>";
echo "<ul>";
echo "<li>ホスト: $host</li>";
echo "<li>ポート: $port</li>";
echo "<li>データベース: $dbname</li>";
echo "<li>ユーザー: $username</li>";
echo "</ul>";
echo "</div>";

try {
    // PDO接続を作成
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green; font-size: 18px;'>✅ データベース接続成功！</p>";
    
    // データベース情報を取得
    $dbInfo = $pdo->query("SELECT DATABASE() as current_db, VERSION() as mysql_version")->fetch(PDO::FETCH_ASSOC);
    echo "<div style='background: #f0fff0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>データベース情報:</strong></p>";
    echo "<ul>";
    echo "<li>現在のデータベース: {$dbInfo['current_db']}</li>";
    echo "<li>MySQLバージョン: {$dbInfo['mysql_version']}</li>";
    echo "</ul>";
    echo "</div>";
    
    // テーブル一覧を取得
    echo "<h2>📋 テーブル一覧</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ テーブルが見つかりませんでした。データベースの初期化を確認してください。</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li><strong>$table</strong></li>";
        }
        echo "</ul>";
        
        // 各テーブルのレコード数を表示
        echo "<h2>📊 各テーブルのデータ件数</h2>";
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr><th style='padding: 8px; background: #f0f0f0;'>テーブル名</th><th style='padding: 8px; background: #f0f0f0;'>レコード数</th></tr>";
        
        foreach ($tables as $table) {
            try {
                $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                echo "<tr><td style='padding: 8px;'>$table</td><td style='padding: 8px; text-align: center;'>$count</td></tr>";
            } catch (PDOException $e) {
                echo "<tr><td style='padding: 8px;'>$table</td><td style='padding: 8px; text-align: center; color: red;'>エラー</td></tr>";
            }
        }
        echo "</table>";
    }
    
    // テーブルが存在する場合のみデータを表示
    if (!empty($tables)) {
        // サンプルデータの詳細表示
        if (in_array('users', $tables)) {
            echo "<h2>👤 ユーザーデータ</h2>";
            $users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
            if ($users) {
                echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
                echo "<tr><th style='padding: 8px; background: #f0f0f0;'>ID</th><th style='padding: 8px; background: #f0f0f0;'>ユーザー名</th><th style='padding: 8px; background: #f0f0f0;'>メール</th><th style='padding: 8px; background: #f0f0f0;'>店舗名</th></tr>";
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td style='padding: 8px;'>{$user['id']}</td>";
                    echo "<td style='padding: 8px;'>{$user['username']}</td>";
                    echo "<td style='padding: 8px;'>{$user['email']}</td>";
                    echo "<td style='padding: 8px;'>" . ($user['store_name'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>ユーザーデータが見つかりませんでした。</p>";
            }
        }
        
        if (in_array('categories', $tables)) {
            echo "<h2>🏷️ カテゴリデータ</h2>";
            $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
            if ($categories) {
                echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
                echo "<tr><th style='padding: 8px; background: #f0f0f0;'>ID</th><th style='padding: 8px; background: #f0f0f0;'>カテゴリ名</th></tr>";
                foreach ($categories as $category) {
                    echo "<tr>";
                    echo "<td style='padding: 8px;'>{$category['id']}</td>";
                    echo "<td style='padding: 8px;'>{$category['name']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>カテゴリデータが見つかりませんでした。</p>";
            }
        }
        
        if (in_array('products', $tables) && in_array('categories', $tables)) {
            echo "<h2>🛍️ 商品データ</h2>";
            $products = $pdo->query("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
            ")->fetchAll(PDO::FETCH_ASSOC);
            if ($products) {
                echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
                echo "<tr><th style='padding: 8px; background: #f0f0f0;'>ID</th><th style='padding: 8px; background: #f0f0f0;'>商品名</th><th style='padding: 8px; background: #f0f0f0;'>カテゴリ</th><th style='padding: 8px; background: #f0f0f0;'>価格</th><th style='padding: 8px; background: #f0f0f0;'>在庫</th></tr>";
                foreach ($products as $product) {
                    echo "<tr>";
                    echo "<td style='padding: 8px;'>{$product['id']}</td>";
                    echo "<td style='padding: 8px;'>{$product['name']}</td>";
                    echo "<td style='padding: 8px;'>" . ($product['category_name'] ?? 'N/A') . "</td>";
                    echo "<td style='padding: 8px;'>¥{$product['regular_price']}</td>";
                    echo "<td style='padding: 8px;'>{$product['stock_quantity']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>商品データが見つかりませんでした。</p>";
            }
        }
        
        if (in_array('sales_targets', $tables)) {
            echo "<h2>🎯 売上目標データ</h2>";
            $targets = $pdo->query("SELECT * FROM sales_targets")->fetchAll(PDO::FETCH_ASSOC);
            if ($targets) {
                echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
                echo "<tr><th style='padding: 8px; background: #f0f0f0;'>ID</th><th style='padding: 8px; background: #f0f0f0;'>目標金額</th><th style='padding: 8px; background: #f0f0f0;'>期間</th><th style='padding: 8px; background: #f0f0f0;'>対象日</th></tr>";
                foreach ($targets as $target) {
                    echo "<tr>";
                    echo "<td style='padding: 8px;'>{$target['id']}</td>";
                    echo "<td style='padding: 8px;'>¥" . number_format($target['target_amount']) . "</td>";
                    echo "<td style='padding: 8px;'>{$target['target_period']}</td>";
                    echo "<td style='padding: 8px;'>{$target['target_date']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>売上目標データが見つかりませんでした。</p>";
            }
        }
        
        if (in_array('stores', $tables)) {
            echo "<h2>🏪 店舗マスタデータ</h2>";
            $stores = $pdo->query("SELECT * FROM stores ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            if ($stores) {
                echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
                echo "<tr><th style='padding: 8px; background: #f0f0f0;'>ID</th><th style='padding: 8px; background: #f0f0f0;'>店舗名</th><th style='padding: 8px; background: #f0f0f0;'>住所</th><th style='padding: 8px; background: #f0f0f0;'>カテゴリ</th><th style='padding: 8px; background: #f0f0f0;'>緯度</th><th style='padding: 8px; background: #f0f0f0;'>経度</th></tr>";
                foreach ($stores as $store) {
                    echo "<tr>";
                    echo "<td style='padding: 8px;'>{$store['id']}</td>";
                    echo "<td style='padding: 8px;'>{$store['name']}</td>";
                    echo "<td style='padding: 8px;'>" . ($store['address'] ?? 'N/A') . "</td>";
                    echo "<td style='padding: 8px;'>{$store['category']}</td>";
                    echo "<td style='padding: 8px;'>" . ($store['latitude'] ?? 'N/A') . "</td>";
                    echo "<td style='padding: 8px;'>" . ($store['longitude'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>店舗データが見つかりませんでした。</p>";
            }
        }
        
        if (in_array('recipes', $tables)) {
            echo "<h2>🍳 レシピデータ（上位10件）</h2>";
            $recipes = $pdo->query("SELECT * FROM recipes ORDER BY id LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
            if ($recipes) {
                echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
                echo "<tr><th style='padding: 8px; background: #f0f0f0;'>ID</th><th style='padding: 8px; background: #f0f0f0;'>レシピ名</th><th style='padding: 8px; background: #f0f0f0;'>カテゴリ</th><th style='padding: 8px; background: #f0f0f0;'>地域</th><th style='padding: 8px; background: #f0f0f0;'>材料数</th></tr>";
                foreach ($recipes as $recipe) {
                    // 材料数を取得
                    $ingredientCount = $pdo->query("SELECT COUNT(*) FROM recipe_ingredients WHERE recipe_id = {$recipe['id']}")->fetchColumn();
                    echo "<tr>";
                    echo "<td style='padding: 8px;'>{$recipe['id']}</td>";
                    echo "<td style='padding: 8px;'>{$recipe['name']}</td>";
                    echo "<td style='padding: 8px;'>" . ($recipe['category'] ?? 'N/A') . "</td>";
                    echo "<td style='padding: 8px;'>" . ($recipe['area'] ?? 'N/A') . "</td>";
                    echo "<td style='padding: 8px; text-align: center;'>{$ingredientCount}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // レシピ統計
                $recipeStats = $pdo->query("
                    SELECT 
                        COUNT(*) as total_recipes,
                        COUNT(DISTINCT category) as total_categories,
                        COUNT(DISTINCT area) as total_areas
                    FROM recipes
                ")->fetch(PDO::FETCH_ASSOC);
                
                echo "<div style='background: #f0f8ff; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "<p><strong>レシピ統計:</strong></p>";
                echo "<ul>";
                echo "<li>総レシピ数: {$recipeStats['total_recipes']}件</li>";
                echo "<li>カテゴリ数: {$recipeStats['total_categories']}種類</li>";
                echo "<li>地域数: {$recipeStats['total_areas']}種類</li>";
                echo "</ul>";
                echo "</div>";
            } else {
                echo "<p>レシピデータが見つかりませんでした。</p>";
            }
        }
        
        // 新規テーブルの統計サマリー
        echo "<h2>📈 新規追加テーブルのサマリー</h2>";
        echo "<div style='background: #f0fff0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>フェーズ2: 機能拡張テーブル</h3>";
        echo "<ul>";
        if (in_array('stores', $tables)) {
            $storeCount = $pdo->query("SELECT COUNT(*) FROM stores")->fetchColumn();
            echo "<li>✅ 店舗マスタ (stores): {$storeCount}件</li>";
        }
        if (in_array('user_favorite_stores', $tables)) {
            $favStoreCount = $pdo->query("SELECT COUNT(*) FROM user_favorite_stores")->fetchColumn();
            echo "<li>✅ お気に入り店舗 (user_favorite_stores): {$favStoreCount}件</li>";
        }
        if (in_array('user_favorite_products', $tables)) {
            $favProductCount = $pdo->query("SELECT COUNT(*) FROM user_favorite_products")->fetchColumn();
            echo "<li>✅ お気に入り商品 (user_favorite_products): {$favProductCount}件</li>";
        }
        if (in_array('user_refrigerator', $tables)) {
            $refrigeratorCount = $pdo->query("SELECT COUNT(*) FROM user_refrigerator")->fetchColumn();
            echo "<li>✅ 冷蔵庫管理 (user_refrigerator): {$refrigeratorCount}件</li>";
        }
        echo "</ul>";
        
        echo "<h3>フェーズ3: 設計改善</h3>";
        echo "<ul>";
        if (in_array('sale_stores', $tables)) {
            $saleStoreCount = $pdo->query("SELECT COUNT(*) FROM sale_stores")->fetchColumn();
            echo "<li>✅ セール×店舗中間テーブル (sale_stores): {$saleStoreCount}件</li>";
        }
        // deleted_atカラムの確認
        $columns = $pdo->query("SHOW COLUMNS FROM users LIKE 'deleted_at'")->fetchAll();
        if (!empty($columns)) {
            echo "<li>✅ 論理削除機能 (users.deleted_at): 実装済み</li>";
        }
        echo "</ul>";
        
        echo "<h3>レシピ機能テーブル</h3>";
        echo "<ul>";
        if (in_array('recipes', $tables)) {
            $recipeCount = $pdo->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
            echo "<li>✅ レシピマスタ (recipes): {$recipeCount}件</li>";
        }
        if (in_array('recipe_ingredients', $tables)) {
            $ingredientCount = $pdo->query("SELECT COUNT(*) FROM recipe_ingredients")->fetchColumn();
            echo "<li>✅ レシピ材料 (recipe_ingredients): {$ingredientCount}件</li>";
        }
        echo "</ul>";
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<h1>❌ データベース接続エラー</h1>";
    echo "<div style='background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p style='color: red;'><strong>エラー:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>エラーコード:</strong> " . $e->getCode() . "</p>";
    echo "</div>";
    
    echo "<h3>🔧 トラブルシューティング:</h3>";
    echo "<div style='background: #fff3e0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<ol>";
    echo "<li>Dockerコンテナが起動しているか確認: <code>docker-compose ps</code></li>";
    echo "<li>MySQLコンテナのログを確認: <code>docker-compose logs mysql-container</code></li>";
    echo "<li>データベースが初期化されているか確認</li>";
    echo "<li>ネットワーク設定を確認</li>";
    echo "</ol>";
    echo "</div>";
    
    // 接続情報の再表示
    echo "<h3>📋 現在の接続設定:</h3>";
    echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<ul>";
    echo "<li>ホスト: $host</li>";
    echo "<li>ポート: $port</li>";
    echo "<li>データベース名: $dbname</li>";
    echo "<li>ユーザー名: $username</li>";
    echo "</ul>";
    echo "</div>";
}
?>