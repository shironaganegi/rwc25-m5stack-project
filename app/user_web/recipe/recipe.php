<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レシピ</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/recipe/recipe.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <section class="search-section">
            <div class="search-container">
                <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input type="text" class="search-input" placeholder="検索">
            </div>
        </section>

        <section class="recipe-list-section">
            <div class="recipe-grid">
                <a href="hamburg_recipe.html" class="recipe-item">
                    <img src="/assets/image/hamburg.png" alt="ハンバーグ">
                    <p>ハンバーグ</p>
                </a>
                <a href="meuniere_recipe.html" class="recipe-item">
                    <img src="/assets/image/meuniere.png" alt="ムニエル">
                    <p>ムニエル</p>
                </a>
                <a href="chahan_recipe.html" class="recipe-item">
                    <img src="/assets/image/chahan.png" alt="チャーハン">
                    <p>チャーハン</p>
                </a>
                <a href="shogayaki_recipe.html" class="recipe-item">
                    <img src="/assets/image/shogayaki.png" alt="生姜焼き">
                    <p>生姜焼き</p>
                </a>
                <a href="nikujaga_recipe.html" class="recipe-item">
                    <img src="/assets/image/nikujaga.png" alt="じゃが肉">
                    <p>じゃが肉</p>
                </a>
                <a href="curry_recipe.html" class="recipe-item">
                    <img src="/assets/image/curry.png" alt="カレー">
                    <p>カレー</p>
                </a>
            </div>
        </section>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

</body>
</html>