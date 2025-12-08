// map.js

document.addEventListener('DOMContentLoaded', function() {
    // 1. マップの初期化
    const map = L.map('mapid').setView([35.6895, 139.6917], 10); // 東京駅付近を中心に設定

    // 2. タイルレイヤー（地図データ）の追加
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // 3. 描画機能（Leaflet.draw）の準備
    // 描画された図形を保持するためのレイヤーグループ
    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // Leaflet.drawのオプション設定
    const drawControl = new L.Control.Draw({
        edit: {
            featureGroup: drawnItems, // 描画したものを編集可能にする
            poly: {
                allowIntersection: false // ポリゴンが交差することを許可しない
            }
        },
        draw: {
            // 描画ツールを設定
            polygon: {
                shapeOptions: {
                    color: '#FF0000', // ポリゴンの色
                    weight: 3,
                    opacity: 0.8
                },
                allowIntersection: false,
                drawError: {
                    color: '#e1e100',
                    timeout: 1000
                },
            },
            // 他のツール（マーカー、円など）は無効化
            marker: false,
            polyline: false,
            rectangle: false,
            circle: false,
            circlemarker: false
        }
    });
    map.addControl(drawControl);

    // 4. ポリゴン描画完了時のイベントハンドリング
    map.on(L.Draw.Event.CREATED, function (e) {
        const type = e.layerType;
        const layer = e.layer;

        // 既存のポリゴンを全てクリア
        drawnItems.clearLayers();
        
        // 新しく描かれたレイヤーを追加
        drawnItems.addLayer(layer);

        if (type === 'polygon') {
            // 描かれたポリゴンの座標（緯度, 経度）を取得
            const latlngs = layer.getLatLngs()[0];
            
            // 座標配列をシンプルな文字列に変換（PHPで扱いやすい形式）
            // 例: "35.689,139.691|35.700,139.700|..."
            const coordinates = latlngs.map(latlng => `${latlng.lat},${latlng.lng}`).join('|');
            
            // 隠しフィールドに座標をセット
            document.getElementById('polygon_coords').value = coordinates;
            
            // 検索ボタンを有効化
            document.getElementById('search-btn').disabled = false;
        }
    });

    // 5. ポリゴンを削除した時のイベント（検索を無効化）
    map.on(L.Draw.Event.DELETED, function (e) {
        document.getElementById('polygon_coords').value = '';
        document.getElementById('search-btn').disabled = true;
    });

    // 6. 検索実行ボタンの処理
    document.getElementById('search-btn').addEventListener('click', function() {
        if (document.getElementById('polygon_coords').value) {
            // フォームを送信
            document.getElementById('search-form').submit();
        } else {
            alert('地図上で検索エリアを囲んでください。');
        }
    });

    // 初期状態では検索ボタンを無効化
    document.getElementById('search-btn').disabled = true;
});