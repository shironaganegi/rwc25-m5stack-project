<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マップ</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/map/map.css">
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
            <iframe
                width="100%"
                height="100%"
                style="border:0"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=ライフ八戸ノ里店">
            </iframe>
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

</body>
</html>