<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マップ</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/map/map.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* 地図コンテナのスタイル調整 */
        #map {
            height: 100%;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        /* Leafletコントロールのスタイル調整 */
        .leaflet-control-zoom {
            border: none !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
        }
        
        .leaflet-control-zoom a {
            background-color: white !important;
            color: #333 !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            font-size: 16px !important;
        }
        
        .leaflet-control-zoom a:hover {
            background-color: #f8f9fa !important;
        }
        
        /* カスタムマーカーポップアップのスタイル */
        .custom-popup {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-width: 200px;
        }
        
        .custom-popup h3 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
        
        .custom-popup p {
            margin: 4px 0;
            font-size: 14px;
            color: #666;
        }
        
        .custom-popup .sale-info {
            color: #e74c3c;
            font-weight: bold;
            background: #ffeaa7;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .custom-popup .congestion {
            color: #27ae60;
            font-weight: bold;
        }
        
        /* カスタムマーカーアイコンのスタイル */
        .custom-div-icon {
            background: transparent !important;
            border: none !important;
        }
        
        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            .custom-popup {
                min-width: 180px;
            }
            
            .custom-popup h3 {
                font-size: 14px;
            }
            
            .custom-popup p {
                font-size: 12px;
            }
        }
        
        /* 条件ボタンのホバー効果 */
        .conditions-button:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .conditions-button:active {
            transform: translateY(0);
        }
        
        /* 検索バーのフォーカス効果 */
        .map-search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 234, 167, 0.5);
        }
    </style>
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main class="map-main">
        <section class="map-filter-section">
            <div class="map-search-wrapper">
                <div class="map-search-bar">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                    <input type="text" class="map-search-input" value="ライフ">
                </div>
                <div class="filter-tags">
                    <span>6月2日</span>
                    <span>17:00~</span>
                    <span>40%以下</span>
                </div>
            </div>
            <button class="conditions-button">条件</button>
        </section>

        <hr class="yellow-separator">
        
        <div class="map-container">
            <!-- OpenStreetMapを使用した地図表示 -->
            <div id="map"></div>
        </div>

        <section class="map-store-info-section">
            <div class="store-item">
                <img src="/assets/image/life_logo.png" alt="ライフ" class="store-logo">
                <div class="store-details">
                    <p class="sale-time">セール 17:00~ ?????</p>
                    <p class="store-name">ライフ八戸ノ里店</p>
                    <p class="store-distance">2.3km</p>
                </div>
                <div class="congestion-info">
                    <p class="congestion-value">23%</p>
                </div>
            </div>
        </section>
        
    </main>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // 地図の初期化（八戸ノ里駅周辺）
        var map = L.map('map').setView([34.662625, 135.589097], 15);

        // OpenStreetMapタイルレイヤーを追加
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // 店舗データ（実際のデータに置き換え可能）
        var stores = [
            {
                name: 'ライフ八戸ノ里店',
                lat: 34.662625,
                lng: 135.589097,
                distance: '2.3km',
                saleTime: '17:00~',
                congestion: '23%',
                saleInfo: 'セール中',
                category: 'supermarket'
            },
        ];

        // カスタムアイコンの作成
        var supermarketIcon = L.divIcon({
            html: '<div style="background-color: #e74c3c; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>',
            iconSize: [20, 20],
            className: 'custom-div-icon'
        });

        var pharmacyIcon = L.divIcon({
            html: '<div style="background-color: #3498db; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>',
            iconSize: [20, 20],
            className: 'custom-div-icon'
        });

        // 店舗マーカーを地図に追加
        stores.forEach(function(store) {
            var icon = store.category === 'pharmacy' ? pharmacyIcon : supermarketIcon;
            
            var marker = L.marker([store.lat, store.lng], {icon: icon}).addTo(map);
            
            var popupContent = `
                <div class="custom-popup">
                    <h3>${store.name}</h3>
                    <p class="sale-info">${store.saleInfo} ${store.saleTime}</p>
                    <p>距離: ${store.distance}</p>
                    <p class="congestion">混雑率: ${store.congestion}</p>
                </div>
            `;
            
            marker.bindPopup(popupContent);
        });

        // 最初のマーカー（ライフ八戸ノ里店）のポップアップを開く
        map.on('ready', function() {
            setTimeout(function() {
                map.openPopup(stores[0].name, [stores[0].lat, stores[0].lng]);
            }, 500);
        });

        // 検索機能のイベントリスナー
        document.querySelector('.map-search-input').addEventListener('input', function(e) {
            var searchTerm = e.target.value.toLowerCase();
            
            if (searchTerm.length === 0) {
                // 検索語が空の場合、全体表示に戻す
                var group = new L.featureGroup();
                stores.forEach(function(store) {
                    group.addLayer(L.marker([store.lat, store.lng]));
                });
                map.fitBounds(group.getBounds().pad(0.1));
                return;
            }
            
            // 該当する店舗を検索
            var matchedStores = stores.filter(function(store) {
                return store.name.toLowerCase().includes(searchTerm);
            });
            
            if (matchedStores.length > 0) {
                // 最初に見つかった店舗にフォーカス
                var store = matchedStores[0];
                map.setView([store.lat, store.lng], 17);
                
                // 該当店舗のポップアップを表示
                setTimeout(function() {
                    map.eachLayer(function(layer) {
                        if (layer instanceof L.Marker) {
                            var pos = layer.getLatLng();
                            if (pos.lat === store.lat && pos.lng === store.lng) {
                                layer.openPopup();
                            }
                        }
                    });
                }, 300);
            }
        });

        // 条件ボタンのクリックイベント（フィルタリング機能）
        document.querySelector('.conditions-button').addEventListener('click', function() {
            // 簡易的なフィルタリングデモ
            var currentFilters = document.querySelectorAll('.filter-tags span');
            var congestionLimit = 40; // 40%以下の混雑率でフィルタ
            
            // 混雑率40%以下の店舗のみ表示
            map.eachLayer(function(layer) {
                if (layer instanceof L.Marker) {
                    map.removeLayer(layer);
                }
            });
            
            var filteredStores = stores.filter(function(store) {
                return parseFloat(store.congestion.replace('%', '')) <= congestionLimit;
            });
            
            filteredStores.forEach(function(store) {
                var icon = store.category === 'pharmacy' ? pharmacyIcon : supermarketIcon;
                var marker = L.marker([store.lat, store.lng], {icon: icon}).addTo(map);
                
                var popupContent = `
                    <div class="custom-popup">
                        <h3>${store.name}</h3>
                        <p class="sale-info">${store.saleInfo} ${store.saleTime}</p>
                        <p>距離: ${store.distance}</p>
                        <p class="congestion">混雑率: ${store.congestion}</p>
                        <small style="color: #27ae60;">✓ フィルタ条件に適合</small>
                    </div>
                `;
                
                marker.bindPopup(popupContent);
            });
            
            // フィルタ結果にフィット
            if (filteredStores.length > 0) {
                var group = new L.featureGroup();
                filteredStores.forEach(function(store) {
                    group.addLayer(L.marker([store.lat, store.lng]));
                });
                map.fitBounds(group.getBounds().pad(0.2));
            }
            
            // フィードバック表示
            var button = document.querySelector('.conditions-button');
            var originalText = button.textContent;
            button.textContent = `適用中 (${filteredStores.length}件)`;
            button.style.background = '#27ae60';
            
            setTimeout(function() {
                button.textContent = originalText;
                button.style.background = '';
            }, 2000);
        });

        // レスポンシブ対応: ウィンドウサイズ変更時に地図を再調整
        window.addEventListener('resize', function() {
            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        });
    </script>

</body>
</html>