<?php
require_once __DIR__ . '/../includes/database.php';

$recipe_id = $_GET['id'] ?? null;
$external_id = $_GET['external_id'] ?? null;
$recipe = null;
$ingredients = [];
$use_json_fallback = false;

if ($recipe_id) {
    try {
        $db = Database::getInstance();
        // レシピ情報を取得
        $recipe = $db->selectOne("SELECT * FROM recipes WHERE id = ?", [$recipe_id]);
        
        if ($recipe) {
            // 材料情報を取得
            $ingredients = $db->select("SELECT * FROM recipe_ingredients WHERE recipe_id = ? ORDER BY ingredient_order ASC", [$recipe_id]);
        }
    } catch (Exception $e) {
        // エラーハンドリング（必要であれば）
    }
} elseif ($external_id) {
    // JSONファイルから読み込む（データベースにデータがない場合）
    $jsonFile = __DIR__ . '/recipes.json';
    if (file_exists($jsonFile)) {
        $jsonData = file_get_contents($jsonFile);
        $recipes_json = json_decode($jsonData, true);
        
        if ($recipes_json && is_array($recipes_json)) {
            foreach ($recipes_json as $r) {
                if (($r['idMeal'] ?? '') === $external_id) {
                    $use_json_fallback = true;
                    $recipe = $r;
                    
                    // 材料情報を構築
                    for ($i = 1; $i <= 20; $i++) {
                        $ingredient = $r["strIngredient{$i}"] ?? '';
                        $measure = $r["strMeasure{$i}"] ?? '';
                        if (!empty($ingredient) && trim($ingredient) !== '') {
                            $ingredients[] = [
                                'ingredient_name' => trim($ingredient),
                                'measure' => trim($measure)
                            ];
                        }
                    }
                    break;
                }
            }
        }
    }
}

$page_title = $recipe ? htmlspecialchars($use_json_fallback ? ($recipe['strMeal'] ?? 'レシピ詳細') : ($recipe['name'] ?? 'レシピ詳細')) : 'レシピが見つかりません';
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
                    <h1><?php echo htmlspecialchars($use_json_fallback ? ($recipe['strMeal'] ?? '') : ($recipe['name'] ?? '')); ?></h1>
                    <span class="recipe-category"><?php echo htmlspecialchars($use_json_fallback ? ($recipe['strCategory'] ?? '') : ($recipe['category'] ?? '')); ?> / <?php echo htmlspecialchars($use_json_fallback ? ($recipe['strArea'] ?? '') : ($recipe['area'] ?? '')); ?></span>
                </div>
                <img src="<?php echo htmlspecialchars($use_json_fallback ? ($recipe['strMealThumb'] ?? '') : ($recipe['thumbnail_url'] ?? '')); ?>" alt="<?php echo htmlspecialchars($use_json_fallback ? ($recipe['strMeal'] ?? '') : ($recipe['name'] ?? '')); ?>" class="recipe-main-image">
                <div class="recipe-content-grid">
                    <div class="materials-section">
                        <h2>Ingredients</h2>
                        <ul>
                            <?php foreach ($ingredients as $item): ?>
                                <li>
                                    <?php echo htmlspecialchars($item['ingredient_name']); ?>
                                    <span><?php echo htmlspecialchars($item['measure'] ?? ''); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="steps-section">
                        <h2>Instructions</h2>
                        <p class="instructions"><?php echo nl2br(htmlspecialchars($use_json_fallback ? ($recipe['strInstructions'] ?? '') : ($recipe['instructions'] ?? ''))); ?></p>
                    </div>
                </div>

                <?php 
                $youtube_url = $use_json_fallback ? ($recipe['strYoutube'] ?? '') : ($recipe['youtube_url'] ?? '');
                if (!empty($youtube_url)): ?>
                    <div class="youtube-section">
                        <h2>Video</h2>
                        <div class="video-container">
                            <?php
                            $embed_url = str_replace('watch?v=', 'embed/', $youtube_url);
                            ?>
                            <iframe 
                                src="<?php echo htmlspecialchars($embed_url); ?>" 
                                title="YouTube video player for <?php echo htmlspecialchars($use_json_fallback ? ($recipe['strMeal'] ?? '') : ($recipe['name'] ?? '')); ?>" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php else: ?>
            <p class="error-message">レシピが見つかりませんでした。</p>
        <?php endif; ?>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>
</body>
</html>