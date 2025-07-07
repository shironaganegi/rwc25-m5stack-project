<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>冷蔵庫内情報</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/refrigerator/refrigerator.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <section class="refrigerator-image-section">
            <img src="/assets/image/refrigerator.png" alt="冷蔵庫" class="refrigerator-image">
        </section>

        <hr class="yellow-separator">

        <section class="search-section">
            <div class="search-container">
                <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input type="text" class="search-input" placeholder="検索">
            </div>
        </section>

        <section class="item-list-section">
            <div class="list-header">
                <span class="header-shelf">上段</span>
                <span class="header-details">詳細情報</span>
                <span class="header-price">値段</span>
            </div>
            <div class="items-container">
                <div class="fridge-item">
                    <img src="/assets/image/eggs.png" alt="卵" class="item-image">
                    <div class="item-details">
                        <p class="item-brand">Brand</p>
                        <p class="item-name">卵</p>
                        <p class="item-info">購入日:4/27</p>
                        <p class="item-info">個数:5</p>
                    </div>
                    <div class="item-price">600円</div>
                </div>
                <div class="fridge-item">
                    <img src="/assets/image/mayonnaise.png" alt="マヨネーズ" class="item-image">
                    <div class="item-details">
                        <p class="item-brand">キユーピー</p>
                        <p class="item-name">キユーピーマヨネーズ</p>
                        <p class="item-info">購入日:3/4</p>
                        <p class="item-info">個数:1</p>
                    </div>
                    <div class="item-price">300円</div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

</body>
</html>