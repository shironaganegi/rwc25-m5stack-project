<?php
// result.php

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['polygon_coords'])) {
    $polygon_coords_raw = $_POST['polygon_coords'];
    
    // 座標文字列を配列に変換 (例: "35.689,139.691|...")
    $coords_array = explode('|', $polygon_coords_raw);
    
    $result_message = count($coords_array) . "点の座標を持つエリアで検索されました。";
    
    // --- 実際の検索ロジック (PHP + データベース) ---
    // ここで、PHPとSQL/GIS拡張機能（PostGISなど）を使用して、
    // データベース内の物件の緯度経度が、このポリゴン内にあるかどうかを判定します。
    // $found_count = search_properties_in_polygon($coords_array);
    $found_count = rand(5, 50); // ダミー件数
    // ---------------------------------------------
    
} else {
    $result_message = "有効なポリゴン座標が送信されませんでした。";
    $found_count = 0;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>検索結果</title>
</head>
<body>
    <h1>✅ 検索結果</h1>
    <p><?php echo htmlspecialchars($result_message); ?></p>
    <h2>見つかった物件件数: <?php echo $found_count; ?>件</h2>
    
    <p>送信されたポリゴン座標（抜粋）: <?php echo substr(htmlspecialchars($polygon_coords_raw ?? 'N/A'), 0, 100) . '...'; ?></p>
    
    <hr>
    <p><a href="map.php">地図に戻って別のエリアを検索する</a></p>
</body>
</html>