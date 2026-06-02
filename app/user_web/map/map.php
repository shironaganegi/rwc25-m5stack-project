<?php
// ----------------------------------------------------
// ★ステップ1: データベースから店舗データを取得します★
// ----------------------------------------------------
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database.php';

$db = getDB();
$stores_raw = $db->getAllStores();

// データ構造をJavaScript用に変換（lat/lng形式に統一）
$store_data_list = [];
foreach ($stores_raw as $store) {
    $store_data_list[] = [
        'id' => (int)$store['id'],
        'name' => $store['name'],
        'address' => $store['address'] ?? '',
        'lat' => (float)$store['latitude'],
        'lng' => (float)$store['longitude'],
        'category' => $store['category'] ?? 'supermarket'
    ];
}

// JavaScriptに渡すデータ
$json_stores = json_encode($store_data_list, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>囲って検索</title>
    
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/map/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <h1>スーパーマーケット検索：エリアを囲って絞り込み</h1>
        
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
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script>
        // ★ステップ2: PHPからJSONデータを受け取り、JavaScript変数として定義する
        const ALL_STORES_DATA = <?php echo $json_stores; ?>;
    </script>
    <script src="/map/map.js"></script>
</body>
</html>