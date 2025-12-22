<?php
// 物件データ（サンプル）
$property_data_list = [
    ['lat' => 35.6800, 'lng' => 139.7600, 'name' => '中央区マンション', 'price' => '8,000万円', 'id' => 101],
    ['lat' => 35.6586, 'lng' => 139.7454, 'name' => '港区タワー', 'price' => '1.2億円', 'id' => 102],
    ['lat' => 35.7100, 'lng' => 139.8107, 'name' => '墨田区戸建', 'price' => '5,500万円', 'id' => 103],
    ['lat' => 35.6950, 'lng' => 139.7000, 'name' => '新宿ビル', 'price' => '9,000万円', 'id' => 104],
];
$json_properties = json_encode($property_data_list, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>物件を囲って検索</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script src="map.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>物件を囲って検索</h1>
    <div class="controls">
        <p>1. 多角形ツールを選択してエリアを囲います。<br>
           2. 最後の点を置いたら「Finish」ボタンか「ダブルクリック」で確定してください。</p>
        
        <form id="search-form" method="POST" action="result.php">
            <input type="hidden" id="selected_ids" name="selected_ids" value="">
            <button type="submit" id="search-btn" class="btn" disabled>このエリアの物件を表示</button>
        </form>
    </div>

    <div id="mapid"></div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script>
        const ALL_PROPERTIES = <?php echo $json_properties; ?>;
    </script>
    <script src="map.js"></script>
</body>
</html>