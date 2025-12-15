<?php
// result.php

// ----------------------------------------------------------------------
// ★手順1: map.phpで定義したものと同じ全物件データを再定義（DBを使用しないため）
// ----------------------------------------------------------------------
// 実際のアプリケーションでは、DBから全物件を取得する関数を呼び出します。
$all_properties_by_id = [
    101 => ['lat' => 35.6800, 'lng' => 139.7600, 'name' => '中央区高級マンション', 'price' => '8,000万円', 'id' => 101],
    102 => ['lat' => 35.6586, 'lng' => 139.7454, 'name' => '東京タワー付近 (港区)', 'price' => '1.2億円', 'id' => 102],
    103 => ['lat' => 35.7000, 'lng' => 139.7750, 'name' => '台東区の戸建', 'price' => '4,500万円', 'id' => 103],
    104 => ['lat' => 35.6950, 'lng' => 139.7000, 'name' => '新宿駅周辺物件', 'price' => '6,500万円', 'id' => 104],
    105 => ['lat' => 35.6700, 'lng' => 139.6900, 'name' => '渋谷駅周辺物件', 'price' => '7,200万円', 'id' => 105],
];


// ----------------------------------------------------------------------
// ★手順2: 送信されたIDリストの取得と処理
// ----------------------------------------------------------------------
$selected_properties = [];
$selected_ids_raw = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['selected_ids'])) {
    $selected_ids_raw = $_POST['selected_ids'];
    
    // カンマ区切りのID文字列を配列に変換
    $selected_ids_array = explode(',', $selected_ids_raw);
    
    // 囲まれたIDだけを $all_properties_by_id から抽出
    foreach ($selected_ids_array as $id_string) {
        $id = (int)$id_string;
        if (isset($all_properties_by_id[$id])) {
            $selected_properties[] = $all_properties_by_id[$id];
        }
    }
}

$found_count = count($selected_properties);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>検索結果</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .result-box {
            border: 2px solid #38a169;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            background-color: #f0fff0;
        }
        .property-list { list-style: none; padding: 0; }
        .property-list li {
            border-bottom: 1px dashed #ccc;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        .property-list li:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <h1>✅ エリア検索結果</h1>

    <div class="result-box">
        <h2>🎉 **<?php echo $found_count; ?>件** の物件が見つかりました！</h2>
        <p>（この件数は、地図上で囲まれた物件の数と一致します。）</p>
    </div>
    
    <h3>囲まれた物件一覧</h3>

    <?php if ($found_count > 0): ?>
        <ul class="property-list">
            <?php foreach ($selected_properties as $property): ?>
                <li>
                    <span>**<?php echo htmlspecialchars($property['name']); ?>** (ID: <?php echo $property['id']; ?>)</span>
                    <span>価格: <?php echo htmlspecialchars($property['price']); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>選択されたエリア内には、指定された物件は見つかりませんでした。</p>
    <?php endif; ?>
    
    <hr>
    <p><a href="map.php">← 地図に戻る</a></p>
</body>
</html>