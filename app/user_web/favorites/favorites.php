<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お気に入り</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/favorites/favorites.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <section class="search-section">
            <div class="search-container favorites-search">
                <span class="search-arrow">></span>
                <input type="text" class="search-input" placeholder="検索">
            </div>
        </section>

        <hr class="yellow-separator">

        <section class="favorites-section">
            <h2 class="favorites-title">店舗情報</h2>
            <div class="store-list">
                <div class="store-item">
                    <img src="/assets/image/aeon_logo.png" alt="イオン" class="store-logo">
                    <div class="store-details">
                        <p class="sale-time">セール 18:00~20:00</p>
                        <p class="store-name">イオン登美ヶ丘店</p>
                        <p class="store-distance">11.9km</p>
                    </div>
                    <div class="congestion-info">
                        <p class="congestion-label">混雑度</p>
                        <p class="congestion-value">45%</p>
                    </div>
                </div>
                <div class="store-item">
                    <img src="/assets/image/life_logo.png" alt="ライフ" class="store-logo">
                    <div class="store-details">
                        <p class="sale-time">セール 17:00~ ?????</p>
                        <p class="store-name">ライフ八戸ノ里店</p>
                        <p class="store-distance">2.3km</p>
                    </div>
                    <div class="congestion-info">
                        <p class="congestion-label">混雑度</p>
                        <p class="congestion-value">23%</p>
                    </div>
                </div>
            </div>
        </section>

        <hr class="yellow-separator">

        <section class="favorites-section">
            <h2 class="favorites-title">お気に入り食べ物</h2>
            <div class="food-grid">
                <div class="food-item">
                    <img src="/assets/image/onigiri.png" alt="おにぎり">
                    <p>おにぎり</p>
                </div>
                <div class="food-item">
                    <img src="/assets/image/tomato.png" alt="トマト">
                    <p>トマト</p>
                </div>
                <div class="food-item">
                    <img src="/assets/image/bento.png" alt="弁当">
                    <p>弁当</p>
                </div>
                <div class="food-item">
                    <img src="/assets/image/sashimi.png" alt="刺し身">
                    <p>刺し身</p>
                </div>
                <div class="food-item">
                    <img src="/assets/image/milk.png" alt="牛乳">
                    <p>牛乳</p>
                </div>
            </div>
        </section>
    </main>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

</body>
</html>