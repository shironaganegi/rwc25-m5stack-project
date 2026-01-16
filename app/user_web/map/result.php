<?php
// result.php

// ----------------------------------------------------------------------
// ★手順1: データベース接続
// ----------------------------------------------------------------------
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database.php';

$db = getDB();

// ----------------------------------------------------------------------
// ★手順2: 送信されたIDリストの取得と処理
// ----------------------------------------------------------------------
$selected_stores = [];
$selected_ids_raw = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['selected_ids'])) {
    $selected_ids_raw = $_POST['selected_ids'];
    
    // カンマ区切りのID文字列を配列に変換
    $selected_ids_array = array_filter(array_map('intval', explode(',', $selected_ids_raw)));
    
    // データベースから囲まれた店舗を取得
    if (!empty($selected_ids_array)) {
        $selected_stores = $db->getStoresByIds($selected_ids_array);
    }
}

$found_count = count($selected_stores);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>検索結果</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <style>
        main {
            padding: 20px;
        }
        .result-box {
            border: 2px solid #38a169;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            background-color: #f0fff0;
        }
        .store-list { 
            list-style: none; 
            padding: 0; 
        }
        .store-list li {
            border-bottom: 1px dashed #ccc;
            padding: 15px 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .store-list li:last-child { 
            border-bottom: none; 
        }
        .store-name {
            font-weight: bold;
            font-size: 1.1em;
        }
        .store-address {
            color: #666;
            font-size: 0.9em;
        }
        .store-category {
            display: inline-block;
            padding: 2px 8px;
            background-color: #e6f7ff;
            border-radius: 4px;
            font-size: 0.85em;
            margin-top: 5px;
        }
        .back-link {
            margin-top: 20px;
            display: inline-block;
            color: #38a169;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <h1>エリア検索結果</h1>

        <div class="result-box">
            <h2><?php echo $found_count; ?>件のスーパーマーケットが見つかりました</h2>
            <p>（この件数は、地図上で囲まれた店舗の数と一致します。）</p>
        </div>
        
        <h3>囲まれた店舗一覧</h3>

        <?php if ($found_count > 0): ?>
            <ul class="store-list">
                <?php foreach ($selected_stores as $store): ?>
                    <li>
                        <span class="store-name"><?php echo htmlspecialchars($store['name']); ?></span>
                        <?php if (!empty($store['address'])): ?>
                            <span class="store-address">住所: <?php echo htmlspecialchars($store['address']); ?></span>
                        <?php endif; ?>
                        <span class="store-category">カテゴリ: <?php echo htmlspecialchars($store['category']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>選択されたエリア内には、スーパーマーケットは見つかりませんでした。</p>
        <?php endif; ?>
        
        <a href="/map/map.php" class="back-link">← 地図に戻る</a>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

</body>
</html>