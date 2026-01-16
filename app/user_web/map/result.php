<?php
// 本来はDBから取得しますが、サンプルとして全データを配列で持っておきます
$all_properties = [
    101 => ['lat' => 35.6800, 'lng' => 139.7600, 'name' => '中央区マンション', 'price' => '8,000万円'],
    102 => ['lat' => 35.6586, 'lng' => 139.7454, 'name' => '港区タワー', 'price' => '1.2億円'],
    103 => ['lat' => 35.7100, 'lng' => 139.8107, 'name' => '墨田区戸建', 'price' => '5,500万円'],
    104 => ['lat' => 35.6950, 'lng' => 139.7000, 'name' => '新宿ビル', 'price' => '9,000万円'],
];

$selected_items = [];
if (!empty($_POST['selected_ids'])) {
    $ids = explode(',', $_POST['selected_ids']);
    foreach ($ids as $id) {
        if (isset($all_properties[$id])) {
            $selected_items[] = $all_properties[$id];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>検索結果</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body>
    <h1>検索結果: <?php echo count($selected_items); ?>件</h1>
    <div class="controls">
        <a href="map.php" class="btn" style="text-decoration:none;">← 戻って再検索</a>
    </div>

    <div id="mapid"></div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        const SELECTED_DATA = <?php echo json_encode($selected_items, JSON_UNESCAPED_UNICODE); ?>;
        
        const map = L.map('mapid').setView([35.6895, 139.7500], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        if (SELECTED_DATA.length > 0) {
            const markers = [];
            SELECTED_DATA.forEach(p => {
                const m = L.marker([p.lat, p.lng])
                    .addTo(map)
                    .bindPopup(`<b>${p.name}</b><br>価格: ${p.price}`);
                markers.push(m);
            });

            // 全てのピンが収まるように表示を調整
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.2));
        }
    </script>
</body>
</html>