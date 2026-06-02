
// 1. 変数の宣言（'Identifier already declared' を防ぐため、ここでのみ宣言）
// グローバル変数として保持することで、再初期化時に古い地図を削除できます
if (typeof map === 'undefined') {
    var map;
}
let drawnItems;
let propertyMarkers;

document.addEventListener('DOMContentLoaded', function () {
    console.log("DOMの読み込み完了。地図を初期化します。");

    // 1. 地図コンテナの存在確認
    const mapContainer = document.getElementById('mapid');
    if (!mapContainer) {
        console.error("エラー: id='mapid' のHTML要素が見つかりません。");
        return;
    }

    // 2. 既存の地図の破棄（'Map container is already initialized' エラー対策）
    if (typeof map !== 'undefined' && map) {
        map.remove();
        console.log("既存の地図オブジェクトを破棄しました。");
    }

    // 3. 地図の初期化
    try {
        map = L.map('mapid').setView([34.65200, 135.586964], 15); // 近畿大学（東大阪キャンパス）を中心に設定

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // レイヤーグループの作成
        drawnItems = new L.FeatureGroup().addTo(map);

        // 4. Leaflet.drawの設定
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
                shapeOptions: { color: '#38a169', weight: 3, opacity: 0.8 },
                allowIntersection: false,
                // ★変更点: ツールチップメッセージをカスタマイズ
                repeatMode: true, // 連続描画を可能にする
                showLength: true,
            },
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
        console.log("描画が完了しました。");
        // 既存のポリゴンをクリア
        drawnItems.clearLayers();
        drawnItems.addLayer(e.layer);

        if (e.layerType === 'polygon') {
            const latlngs = e.layer.getLatLngs()[0];

            // 描画されたポリゴンが最低3点を持つかチェック（無効なポリゴンの除外）
            if (latlngs.length < 3) {
                alert('ポリゴンを形成するには、最低3点が必要です。');
                drawnItems.clearLayers();
                const polygonCoordsInput = document.getElementById('polygon_coords');
                const selectedIdsInput = document.getElementById('selected_ids');
                const searchBtn = document.getElementById('search-btn');
                if (polygonCoordsInput) polygonCoordsInput.value = '';
                if (selectedIdsInput) selectedIdsInput.value = '';
                if (searchBtn) searchBtn.disabled = true;
                return; // 処理を終了
            }

            const coordinates = latlngs.map(latlng => `${latlng.lat},${latlng.lng}`).join('|');
            const polygonCoordsInput = document.getElementById('polygon_coords');
            if (polygonCoordsInput) polygonCoordsInput.value = coordinates;

            // 囲まれた店舗IDを抽出
            const insideStoreIds = [];
            const polygon = L.polygon(latlngs);

            storeMarkers.eachLayer(function (marker) {
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
            const selectedIdsInput = document.getElementById('selected_ids');
            const searchBtn = document.getElementById('search-btn');
            if (selectedIdsInput) selectedIdsInput.value = insideStoreIds.join(',');

            // 検索ボタンを有効化
            if (searchBtn) {
                searchBtn.disabled = false;
                searchBtn.textContent = `選択された ${insideStoreIds.length} 件を表示`;
            }

            console.log("判定完了。選択ID:", insideStoreIds);
        }
    });

    // 5. その他のイベント処理（削除、検索ボタンクリック）
    map.on(L.Draw.Event.DELETED, function (e) {
        const polygonCoordsInput = document.getElementById('polygon_coords');
        const selectedIdsInput = document.getElementById('selected_ids');
        const searchBtn = document.getElementById('search-btn');
        if (polygonCoordsInput) polygonCoordsInput.value = '';
        if (selectedIdsInput) selectedIdsInput.value = '';
        if (searchBtn) searchBtn.disabled = true;
        // 全てのハイライトを解除
        storeMarkers.eachLayer(function (marker) {
            if (marker._icon) {
                marker._icon.classList.remove('highlight-marker');
            }
        });
    });

    const searchBtn = document.getElementById('search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function () {
            const polygonCoordsInput = document.getElementById('polygon_coords');
            if (polygonCoordsInput && polygonCoordsInput.value) {
                const searchForm = document.getElementById('search-form');
                if (searchForm) searchForm.submit();
            } else {
                alert('検索エリアを囲んでください。');
            }
        });
    }
}