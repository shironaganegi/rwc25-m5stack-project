<?php
// JSONファイルからレシピデータを読み込む
$recipes = [];
$json_file_path = __DIR__ . '/recipes.json';

if (file_exists($json_file_path)) {
    $json_data = file_get_contents($json_file_path);
    $all_recipes = json_decode($json_data, true);

    // 全レシピの中からランダムに10件選ぶ
    if (is_array($all_recipes) && count($all_recipes) > 10) {
        $random_keys = array_rand($all_recipes, 10);
        foreach ($random_keys as $key) {
            $recipes[] = $all_recipes[$key];
        }
    } else {
        $recipes = $all_recipes ?? [];
    }
}
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
                <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input type="text" class="search-input" placeholder="レシピを検索">
            </div>
        </section>
        <section class="recipe-list-section">
            <?php if (!empty($recipes)): ?>
                <div class="recipe-grid">
                    <?php foreach ($recipes as $recipe): ?>
                        <a href="/recipe/recipe_detail.php?id=<?php echo htmlspecialchars($recipe['idMeal'] ?? ''); ?>" class="recipe-item">
                            <img src="<?php echo htmlspecialchars($recipe['strMealThumb'] ?? ''); ?>/preview" alt="<?php echo htmlspecialchars($recipe['strMeal'] ?? ''); ?>">
                            <p><?php echo htmlspecialchars($recipe['strMeal'] ?? ''); ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="error-message">レシピが見つかりませんでした。「fetch_recipes.php」を実行してください。</p>
            <?php endif; ?>
        </section>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>
</body>
</html>