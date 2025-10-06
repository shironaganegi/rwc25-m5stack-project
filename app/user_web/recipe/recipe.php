<?php
// 検索キーワードを取得
$search_keyword = trim($_GET['s'] ?? '');

// JSONファイルから全レシピデータを読み込む
$all_recipes = [];
$json_file_path = __DIR__ . '/recipes.json';
if (file_exists($json_file_path)) {
    $json_data = file_get_contents($json_file_path);
    $all_recipes = json_decode($json_data, true);
    if (!is_array($all_recipes)) {
        $all_recipes = [];
    }
}

$recipes_to_display = [];

if (!empty($search_keyword)) {
    // 【検索時】キーワードでレシピ名と材料をフィルタリング
    foreach ($all_recipes as $recipe) {
        $found = false;

        // 1. レシピ名(strMeal)を検索
        if (isset($recipe['strMeal']) && stristr($recipe['strMeal'], $search_keyword) !== false) {
            $found = true;
        }

        // 2. 材料(strIngredient1〜20)を検索
        if (!$found) {
            for ($i = 1; $i <= 20; $i++) {
                $ingredient_key = 'strIngredient' . $i;
                if (isset($recipe[$ingredient_key]) && !empty($recipe[$ingredient_key])) {
                    if (stristr($recipe[$ingredient_key], $search_keyword) !== false) {
                        $found = true;
                        break; // 材料で見つかったらループを抜ける
                    }
                }
            }
        }

        // レシピ名か材料のいずれかで見つかった場合、表示リストに追加
        if ($found) {
            $recipes_to_display[] = $recipe;
        }
    }
} else {
    // 【通常時】ランダムに10件選ぶ
    if (count($all_recipes) > 20) {
        $random_keys = array_rand($all_recipes, 20);
        foreach ($random_keys as $key) {
            $recipes_to_display[] = $all_recipes[$key];
        }
    } else {
        $recipes_to_display = $all_recipes;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レシピ検索</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/recipe/recipe.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <section class="search-section">
            <form action="/recipe/recipe.php" method="GET" class="search-container">
                <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input type="text" name="s" class="search-input" placeholder="レシピ名や材料で検索..." value="<?php echo htmlspecialchars($search_keyword); ?>">
            </form>
        </section>

        <section class="recipe-list-section">
            <?php if (!empty($recipes_to_display)): ?>
                <div class="recipe-grid">
                    <?php foreach ($recipes_to_display as $recipe): ?>
                        <a href="/recipe/recipe_detail.php?id=<?php echo htmlspecialchars($recipe['idMeal'] ?? ''); ?>" class="recipe-item">
                            <img src="<?php echo htmlspecialchars($recipe['strMealThumb'] ?? ''); ?>/preview" alt="<?php echo htmlspecialchars($recipe['strMeal'] ?? ''); ?>">
                            <p><?php echo htmlspecialchars($recipe['strMeal'] ?? ''); ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="error-message">
                    <?php if (!empty($search_keyword)): ?>
                        「<?php echo htmlspecialchars($search_keyword); ?>」の検索結果が見つかりませんでした。
                    <?php else: ?>
                        レシピが見つかりませんでした。「fetch_recipes.php」を再実行してください。
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </section>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

</body>
</html>