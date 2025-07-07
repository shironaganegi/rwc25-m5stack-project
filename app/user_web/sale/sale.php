<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>セール情報</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/sale/sale.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <section class="sale-header-section">
            <div class="search-container">
                <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input type="text" class="search-input" placeholder="検索">
            </div>
            <a href="#" class="add-store-button">登録スーパー追加</a>
        </section>

        <hr class="yellow-separator">

        <section class="sale-store-list-section">
            <div class="sale-store-list">
                <a href="#" class="sale-store-link">
                    <img src="/assets/image/aeon_logo.png" alt="イオン" class="sale-store-logo">
                    <span class="sale-store-name">イオン登美ヶ丘店</span>
                </a>
                <a href="#" class="sale-store-link">
                    <img src="/assets/image/life_logo.png" alt="ライフ" class="sale-store-logo">
                    <span class="sale-store-name">ライフ八戸ノ里店</span>
                </a>
            </div>
        </section>

    </main>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

</body>
</html>