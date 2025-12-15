<?php
// ----------------------------------------------------
// ★ステップ1: ここに、表示したい物件データを定義/取得します★
// ----------------------------------------------------
// 実際のアプリケーションではデータベースからデータを取得します。
// 緯度(lat)と経度(lng)は必須です。

$property_data_list = [
    [
        'lat' => 35.6800, 'lng' => 139.7600, 
        'name' => '中央区高級マンション', 'price' => '8,000万円', 'id' => 101
    ],
    [
        'lat' => 35.6586, 'lng' => 139.7454, 
        'name' => '東京タワー付近 (港区)', 'price' => '1.2億円', 'id' => 102
    ],
    [
        'lat' => 35.7000, 'lng' => 139.7750, 
        'name' => '台東区の戸建', 'price' => '4,500万円', 'id' => 103
    ],
    [
        'lat' => 35.6950, 'lng' => 139.7000, 
        'name' => '新宿駅周辺物件', 'price' => '6,500万円', 'id' => 104
    ],
    [
        'lat' => 35.6700, 'lng' => 139.6900, 
        'name' => '渋谷駅周辺物件', 'price' => '7,200万円', 'id' => 105
    ],
];

// ★追加: IDをキーとする連想配列に変換（result.phpで使用するため）
$all_properties_by_id = [];
foreach ($property_data_list as $property) {
    $all_properties_by_id[$property['id']] = $property;
}

// JavaScriptに渡すのはIDリストではない、元のリスト（ALL_PROPERTIES_DATA）のまま
$json_properties = json_encode($property_data_list, JSON_UNESCAPED_UNICODE);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>囲って検索</title>
    
    <link rel="stylesheet" href="style.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
</head>
<body>
    <h1>物件検索：エリアを囲って絞り込み</h1>
    
    <div class="controls">
        <p>地図上の左上にある**多角形アイコン**をクリックし、検索したいエリアをマウスで描いてください。</p>
        <form id="search-form" method="POST" action="result.php">
            <input type="hidden" id="polygon_coords" name="polygon_coords" value="">
            
            <input type="hidden" id="selected_ids" name="selected_ids" value="">
            
            <button type="button" id="search-btn" class="btn">
                このエリアで検索
            </button>
        </form>
    </div>

    <div id="mapid"></div>

    <script>
        // ★ステップ2: PHPからJSONデータを受け取り、JavaScript変数として定義する
        const ALL_PROPERTIES_DATA = <?php echo $json_properties; ?>;
    </script>
    <script src="map.js"></script>
</body>
</html>