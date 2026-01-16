
// 1. 変数の宣言（'Identifier already declared' を防ぐため、ここでのみ宣言）
// グローバル変数として保持することで、再初期化時に古い地図を削除できます
if (typeof map === 'undefined') {
    var map; 
}
let drawnItems;
let propertyMarkers;

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOMの読み込み完了。地図を初期化します。");

    // 2. 地図コンテナの存在確認
    const mapContainer = document.getElementById('mapid');
    if (!mapContainer) {
        console.error("エラー: id='mapid' のHTML要素が見つかりません。");
        return;
    }

    // 3. 既存の地図の破棄（'Map container is already initialized' エラー対策）
    if (map) {
        map.remove();
        console.log("既存の地図オブジェクトを破棄しました。");
    }

    // 4. 地図の初期化（ここでは let/const を使わず代入のみ行う）
    try {
        map = L.map('mapid').setView([35.6895, 139.7500], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // レイヤーグループの作成
        drawnItems = new L.FeatureGroup().addTo(map);
        propertyMarkers = new L.FeatureGroup().addTo(map);

        // 5. 物件データの配置
        if (typeof ALL_PROPERTIES !== 'undefined') {
            ALL_PROPERTIES.forEach(p => {
                const marker = L.marker([p.lat, p.lng]).addTo(propertyMarkers);
                marker.options.id = p.id; // IDを保持
                marker.bindPopup(`<b>${p.name}</b><br>価格: ${p.price}`);
            });
        }

        // 6. Leaflet.drawの設定
        initDrawTools();

    } catch (error) {
        console.error("地図の初期化中にエラーが発生しました:", error);
    }
});

/**
 * 描画ツールの設定とイベント登録
 */
function initDrawTools() {
    // ライブラリの存在チェック（'L.PolyUtil' エラー対策）
    if (!L.Control.Draw || !L.PolyUtil) {
        console.error("エラー: Leaflet.drawライブラリが読み込まれていません。");
        return;
    }

    const drawControl = new L.Control.Draw({
        edit: { featureGroup: drawnItems },
        draw: {
            polygon: {
                allowIntersection: false,
                shapeOptions: { color: '#38a169', fillOpacity: 0.3 }
            },
            polyline: false, circle: false, rectangle: false, marker: false, circlemarker: false
        }
    });
    map.addControl(drawControl);

    // 描画完了イベント（Finishボタンまたはダブルクリックで発火）
    map.on(L.Draw.Event.CREATED, function (e) {
        console.log("描画が完了しました。");
        const layer = e.layer;
        
        drawnItems.clearLayers();
        drawnItems.addLayer(layer);

        // ポリゴン座標の取得
        let latlngs = layer.getLatLngs();
        if (Array.isArray(latlngs[0])) {
            latlngs = latlngs[0]; // 単一ポリゴン用の座標配列
        }

        const selectedIds = [];

        // 囲まれた物件の判定（appendChildエラーを防ぐため安全なアクセス）
        // map.js の判定部分
        propertyMarkers.eachLayer(m => {
            // L.PolyUtil が存在するか、かつ containsLocation が関数かチェック
            if (L.PolyUtil && typeof L.PolyUtil.containsLocation === 'function') {
                const isInside = L.PolyUtil.containsLocation(m.getLatLng(), latlngs);
                if (isInside) {
                    selectedIds.push(m.options.id);
                    if (m._icon) m._icon.style.filter = "hue-rotate(120deg) saturate(3)";
                } else {
                    if (m._icon) m._icon.style.filter = "";
                }
            } else {
                console.error("判定エラー: Leaflet.drawライブラリが読み込まれていません。");
            }
        });

        // フォームへの反映
        const idInput = document.getElementById('selected_ids');
        const searchBtn = document.getElementById('search-btn');

        if (idInput) idInput.value = selectedIds.join(',');
        
        if (searchBtn) {
            searchBtn.disabled = false;
            searchBtn.textContent = `選択された ${selectedIds.length} 件を表示`;
        }
        
        console.log("判定完了。選択ID:", selectedIds);
    });
}