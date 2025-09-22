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