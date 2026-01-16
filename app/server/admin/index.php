<?php
// 文字エンコーディングを明示的に設定
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

require_once 'includes/database.php';

$pageTitle = "データベース一覧 - supermarket_app";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .header .info {
            color: #666;
            font-size: 14px;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .table-list {
            list-style: none;
        }
        .table-list li {
            padding: 12px;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        .table-list li:hover {
            background: #f9f9f9;
        }
        .table-list li.selected {
            background: #e3f2fd;
        }
        .table-list a {
            text-decoration: none;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .count {
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 14px;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 4px;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
        }
        .pagination a:hover {
            background: #f5f5f5;
        }
        .pagination .current {
            background: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗄️ データベース一覧</h1>
            <div class="info">
                <strong>データベース:</strong> supermarket_app | 
                <strong>ホスト:</strong> mysql:3306 | 
                <strong>ユーザー:</strong> dev
            </div>
        </div>

        <?php
        try {
            $db = getDB();
            $conn = $db->getConnection();
            
            // 全テーブル一覧を取得
            $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            // 選択されたテーブル（GETパラメータ）
            $selectedTable = $_GET['table'] ?? null;
            
            // テーブル一覧カード
            echo "<div class='card'>";
            echo "<div class='card-title'>📋 テーブル一覧 (" . count($tables) . " テーブル)</div>";
            echo "<ul class='table-list'>";
            foreach ($tables as $table) {
                $count = $conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                $isSelected = ($selectedTable === $table) ? 'selected' : '';
                echo "<li class='$isSelected'>";
                echo "<a href='?table=" . urlencode($table) . "'>";
                echo "<span><strong>$table</strong></span>";
                echo "<span class='count'>$count 件</span>";
                echo "</a></li>";
            }
            echo "</ul>";
            echo "</div>";
            
            // 選択されたテーブルのデータ表示
            if ($selectedTable && in_array($selectedTable, $tables)) {
                // ページネーション
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $perPage = 50;
                $offset = ($page - 1) * $perPage;
                
                // 総件数取得
                $totalCount = $conn->query("SELECT COUNT(*) FROM `$selectedTable`")->fetchColumn();
                $totalPages = ceil($totalCount / $perPage);
                
                // データ取得
                $data = $conn->query("SELECT * FROM `$selectedTable` LIMIT $perPage OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);
                
                // カラム名取得
                $columns = [];
                if (!empty($data)) {
                    $columns = array_keys($data[0]);
                } else {
                    $stmt = $conn->query("DESCRIBE `$selectedTable`");
                    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                }
                
                echo "<div class='card'>";
                echo "<div class='card-title'>📊 テーブル: $selectedTable</div>";
                echo "<div style='color: #666; margin-bottom: 10px;'>全 $totalCount 件 (ページ $page / $totalPages)</div>";
                
                if (!empty($data)) {
                    echo "<div style='overflow-x: auto;'>";
                    echo "<table>";
                    echo "<thead><tr>";
                    foreach ($columns as $col) {
                        echo "<th>$col</th>";
                    }
                    echo "</tr></thead><tbody>";
                    
                    foreach ($data as $row) {
                        echo "<tr>";
                        foreach ($columns as $col) {
                            $value = $row[$col] ?? '';
                            // 長いテキストは切り詰め
                            if (strlen($value) > 100) {
                                $value = substr($value, 0, 100) . '...';
                            }
                            // NULLの場合は空文字として表示
                            if ($value === null) {
                                $value = '<em style="color: #999;">NULL</em>';
                            }
                            echo "<td>" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
                    echo "</div>";
                    
                    // ページネーション
                    if ($totalPages > 1) {
                        echo "<div class='pagination'>";
                        if ($page > 1) {
                            echo "<a href='?table=" . urlencode($selectedTable) . "&page=" . ($page - 1) . "'>« 前へ</a>";
                        }
                        for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++) {
                            $active = ($i === $page) ? 'current' : '';
                            echo "<a href='?table=" . urlencode($selectedTable) . "&page=$i' class='$active'>$i</a>";
                        }
                        if ($page < $totalPages) {
                            echo "<a href='?table=" . urlencode($selectedTable) . "&page=" . ($page + 1) . "'>次へ »</a>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<p style='padding: 20px; text-align: center; color: #666;'>データがありません</p>";
                }
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<strong>エラー:</strong> " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
