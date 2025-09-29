<?php
// レシピデータを配列で定義
$recipes = [
    ['id' => 1, 'name' => 'ハンバーグ', 'image' => '/assets/image/recipe.png'],
    ['id' => 2, 'name' => 'ムニエル', 'image' => '/assets/image/meuniere.png'],
    ['id' => 3, 'name' => 'チャーハン', 'image' => '/assets/image/chahan.png'],
    ['id' => 4, 'name' => '生姜焼き', 'image' => '/assets/image/shogayaki.png'],
    ['id' => 5, 'name' => '肉じゃが', 'image' => '/assets/image/nikujaga.png'],
    ['id' => 6, 'name' => 'カレー', 'image' => '/assets/image/curry.png'],
    // 麻婆豆腐を追加
    ['id' => 7, 'name' => '麻婆豆腐', 'image' => '/assets/image/mapo-tofu.png'], // 画像パスは後で配置するファイル名に合わせます
];
?>
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
                <?php foreach ($recipes as $recipe): ?>
                    <a href="/recipe/recipe_detail.php?id=<?php echo htmlspecialchars($recipe['id'], ENT_QUOTES, 'UTF-8'); ?>" class="recipe-item">
                        <img src="<?php echo htmlspecialchars($recipe['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($recipe['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <p><?php echo htmlspecialchars($recipe['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

</body>
</html>