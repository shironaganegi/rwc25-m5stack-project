<?php
$meal_id = $_GET['id'] ?? null;
$recipe = null;

if ($meal_id) {
    $json_file_path = __DIR__ . '/recipes.json';
    if (file_exists($json_file_path)) {
        $json_data = file_get_contents($json_file_path);
        $all_recipes = json_decode($json_data, true);

        if (is_array($all_recipes)) {
            foreach ($all_recipes as $item) {
                if (isset($item['idMeal']) && $item['idMeal'] == $meal_id) {
                    $recipe = $item;
                    break;
                }
            }
        }
    }
}

// 材料と分量を整形する
$ingredients = [];
if ($recipe) {
    for ($i = 1; $i <= 20; $i++) {
        $ingredient = $recipe['strIngredient' . $i] ?? '';
        $measure = $recipe['strMeasure' . $i] ?? '';
        if (!empty(trim($ingredient))) {
            $ingredients[] = ['name' => $ingredient, 'measure' => $measure];
        }
    }
}

$page_title = $recipe ? htmlspecialchars($recipe['strMeal'] ?? 'レシピ詳細') : 'レシピが見つかりません';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/recipe/recipe-detail.css">
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>
    <main>
        <?php if ($recipe): ?>
            <div class="recipe-detail-container">
                <div class="recipe-title-container">
                    <h1><?php echo htmlspecialchars($recipe['strMeal'] ?? ''); ?></h1>
                    <span class="recipe-category"><?php echo htmlspecialchars($recipe['strCategory'] ?? ''); ?> / <?php echo htmlspecialchars($recipe['strArea'] ?? ''); ?></span>
                </div>
                <img src="<?php echo htmlspecialchars($recipe['strMealThumb'] ?? ''); ?>" alt="<?php echo htmlspecialchars($recipe['strMeal'] ?? ''); ?>" class="recipe-main-image">
                <div class="recipe-content-grid">
                    <div class="materials-section">
                        <h2>Ingredients</h2>
                        <ul>
                            <?php foreach ($ingredients as $item): ?>
                                <li><?php echo htmlspecialchars($item['name']); ?><span><?php echo htmlspecialchars($item['measure']); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="steps-section">
                        <h2>Instructions</h2>
                        <p class="instructions"><?php echo nl2br(htmlspecialchars($recipe['strInstructions'] ?? '')); ?></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p class="error-message">レシピが見つかりませんでした。</p>
        <?php endif; ?>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>
</body>
</html>