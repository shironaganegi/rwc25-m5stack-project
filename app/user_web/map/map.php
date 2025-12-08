<?php
// PHPでユーザー入力を処理する場合は、ここでバリデーションなどを行う
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
        <p>左上の多角形アイコンをクリックし、地図上にエリアを描いてください。</p>
        
        <form id="search-form" method="POST" action="result.php">
            <input type="hidden" id="polygon_coords" name="polygon_coords" value="">
            
            <button type="button" id="search-btn" class="btn">
                このエリアで検索
            </button>
        </form>
    </div>

    <div id="mapid"></div>

    <script src="map.js"></script>
</body>
</html>