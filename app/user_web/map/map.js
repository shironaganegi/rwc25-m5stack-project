// map.js

document.addEventListener('DOMContentLoaded', function() {
    // 1. マップの初期化
    const map = L.map('mapid').setView([35.6895, 139.7500], 12); // 初期表示座標を指定
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // 2. 描画機能（Leaflet.draw）の準備とコントロール
    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    const drawControl = new L.Control.Draw({
        edit: { featureGroup: drawnItems },
        draw: {
            polygon: { shapeOptions: { color: '#38a169', weight: 3, opacity: 0.8 }, allowIntersection: false,
            // ★変更点: ツールチップメッセージをカスタマイズ
            repeatMode: true, // 連続描画を可能にする
            showLength: true, },
            marker: false, polyline: false, rectangle: false, circle: false, circlemarker: false
            
        }
    });
    
    map.addControl(drawControl);
    
    // 3. 全物件マーカーの表示
    const propertyMarkers = new L.FeatureGroup();
    map.addLayer(propertyMarkers);
    
    if (typeof ALL_PROPERTIES_DATA !== 'undefined' && ALL_PROPERTIES_DATA.length > 0) {
        ALL_PROPERTIES_DATA.forEach(property => {
            const marker = L.marker([property.lat, property.lng]);
            
            // ポップアップ内容
            const popupContent = `
                <b>${property.name}</b><br>
                価格: ${property.price}<br>
                <a href="/property/${property.id}">詳細へ</a>
            `;
            marker.bindPopup(popupContent);
            
            // マーカーにカスタムプロパティ（ID）を追加
            marker.options.property_id = property.id;
            
            propertyMarkers.addLayer(marker);
        });
    }

    // 4. ポリゴン描画完了時のイベントハンドリング
    // map.js (抜粋)

// ... (中略 - 既存のマップ初期化、マーカー表示ロジック)

// 4. ポリゴン描画完了時のイベントハンドリング
map.on(L.Draw.Event.CREATED, function (e) {
    // ... (中略 - 既存のポリゴンクリア、座標セット処理)

    if (type === 'polygon') {
        const latlngs = layer.getLatLngs()[0];
        const coordinates = latlngs.map(latlng => `${latlng.lat},${latlng.lng}`).join('|');
        
        document.getElementById('polygon_coords').value = coordinates;

        // ★★★ 変更・追加箇所: 囲まれた物件IDを抽出 ★★★
        const insidePropertiesIds = [];
        
        propertyMarkers.eachLayer(function(marker) {
            const latLng = marker.getLatLng();
            // L.PolyUtil.containsLocationを使用して正確にポリゴン内の判定を行う
            const isInside = L.PolyUtil.containsLocation(latLng, latlngs, false);
            
            const markerElement = marker._icon;

            if (isInside) {
                // 囲まれた場合、ハイライト
                markerElement.classList.add('highlight-marker');
                // IDをリストに追加
                insidePropertiesIds.push(marker.options.property_id);
            } else {
                // 囲まれていない場合、ハイライト解除
                markerElement.classList.remove('highlight-marker');
            }
        });

        if (type === 'polygon') {
        const latlngs = layer.getLatLngs()[0];
        
        // ★変更点: 描画されたポリゴンが最低3点を持つかチェック（無効なポリゴンの除外）
        if (latlngs.length < 3) {
            alert('ポリゴンを形成するには、最低3点が必要です。');
            drawnItems.clearLayers();
            document.getElementById('polygon_coords').value = '';
            document.getElementById('selected_ids').value = '';
            document.getElementById('search-btn').disabled = true;
            return; // 処理を終了
        }}
        
        // 抽出したIDリストをカンマ区切り文字列にして隠しフィールドにセット
        document.getElementById('selected_ids').value = insidePropertiesIds.join(',');

        // 検索ボタンを有効化
        document.getElementById('search-btn').disabled = false;
    }
});

    // 5. その他のイベント処理（削除、検索ボタンクリック）
    map.on(L.Draw.Event.DELETED, function (e) {
        document.getElementById('polygon_coords').value = '';
        document.getElementById('search-btn').disabled = true;
        // 全てのハイライトを解除
        propertyMarkers.eachLayer(function(marker) {
             if (marker._icon) {
                marker._icon.classList.remove('highlight-marker');
            }
        });
    });

    document.getElementById('search-btn').addEventListener('click', function() {
        if (document.getElementById('polygon_coords').value) {
            document.getElementById('search-form').submit();
        } else {
            alert('検索エリアを囲んでください。');
        }
    });
});