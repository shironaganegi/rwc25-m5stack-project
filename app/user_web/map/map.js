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
    
    // 3. 全店舗マーカーの表示
    const storeMarkers = new L.FeatureGroup();
    map.addLayer(storeMarkers);
    
    if (typeof ALL_STORES_DATA !== 'undefined' && ALL_STORES_DATA.length > 0) {
        ALL_STORES_DATA.forEach(store => {
            const marker = L.marker([store.lat, store.lng]);
            
            // ポップアップ内容
            const popupContent = `
                <b>${store.name}</b><br>
                ${store.address ? `住所: ${store.address}<br>` : ''}
                カテゴリ: ${store.category}
            `;
            marker.bindPopup(popupContent);
            
            // マーカーにカスタムプロパティ（ID）を追加
            marker.options.store_id = store.id;
            
            storeMarkers.addLayer(marker);
        });
    }

    // 4. ポリゴン描画完了時のイベントハンドリング
    map.on(L.Draw.Event.CREATED, function (e) {
        // 既存のポリゴンをクリア
        drawnItems.clearLayers();
        drawnItems.addLayer(e.layer);

        if (e.layerType === 'polygon') {
            const latlngs = e.layer.getLatLngs()[0];
            
            // 描画されたポリゴンが最低3点を持つかチェック（無効なポリゴンの除外）
            if (latlngs.length < 3) {
                alert('ポリゴンを形成するには、最低3点が必要です。');
                drawnItems.clearLayers();
                document.getElementById('polygon_coords').value = '';
                document.getElementById('selected_ids').value = '';
                document.getElementById('search-btn').disabled = true;
                return; // 処理を終了
            }

            const coordinates = latlngs.map(latlng => `${latlng.lat},${latlng.lng}`).join('|');
            document.getElementById('polygon_coords').value = coordinates;

            // 囲まれた店舗IDを抽出
            const insideStoreIds = [];
            const polygon = L.polygon(latlngs);
            
            storeMarkers.eachLayer(function(marker) {
                const latLng = marker.getLatLng();
                const markerElement = marker._icon;
                
                // ポリゴン内判定（Leaflet標準APIを使用）
                // getBounds()で大まかな判定を行い、その後詳細判定
                if (polygon.getBounds().contains(latLng)) {
                    // より正確な判定: ポリゴンの境界内かどうか
                    // Leafletの標準機能でポリゴン内判定を行う
                    let isInside = false;
                    const point = latLng;
                    const polygonPoints = latlngs;
                    
                    // Ray casting algorithm (簡易版)
                    let inside = false;
                    for (let i = 0, j = polygonPoints.length - 1; i < polygonPoints.length; j = i++) {
                        const xi = polygonPoints[i].lat, yi = polygonPoints[i].lng;
                        const xj = polygonPoints[j].lat, yj = polygonPoints[j].lng;
                        const intersect = ((yi > point.lng) !== (yj > point.lng))
                            && (point.lat < (xj - xi) * (point.lng - yi) / (yj - yi) + xi);
                        if (intersect) inside = !inside;
                    }
                    isInside = inside;

                    if (isInside) {
                        // 囲まれた場合、ハイライト
                        if (markerElement) {
                            markerElement.classList.add('highlight-marker');
                        }
                        // IDをリストに追加
                        insideStoreIds.push(marker.options.store_id);
                    } else {
                        // 囲まれていない場合、ハイライト解除
                        if (markerElement) {
                            markerElement.classList.remove('highlight-marker');
                        }
                    }
                } else {
                    // 境界外の場合はハイライト解除
                    if (markerElement) {
                        markerElement.classList.remove('highlight-marker');
                    }
                }
            });
            
            // 抽出したIDリストをカンマ区切り文字列にして隠しフィールドにセット
            document.getElementById('selected_ids').value = insideStoreIds.join(',');

            // 検索ボタンを有効化
            document.getElementById('search-btn').disabled = false;
        }
    });

    // 5. その他のイベント処理（削除、検索ボタンクリック）
    map.on(L.Draw.Event.DELETED, function (e) {
        document.getElementById('polygon_coords').value = '';
        document.getElementById('selected_ids').value = '';
        document.getElementById('search-btn').disabled = true;
        // 全てのハイライトを解除
        storeMarkers.eachLayer(function(marker) {
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