<?php
require_once __DIR__ . '/../includes/database.php';

// 検索キーワードを取得
$search_keyword = trim($_GET['s'] ?? '');

$recipes_to_display = [];
$error_message = '';

try {
    $db = Database::getInstance();

    if (!empty($search_keyword)) {
        // 【検索時】キーワードでレシピ名と材料を検索
        // DISTINCTを使って重複を除外
        $sql = "
            SELECT DISTINCT r.* FROM recipes r 
            LEFT JOIN recipe_ingredients ri ON r.id = ri.recipe_id 
            WHERE r.name LIKE ? OR ri.ingredient_name LIKE ?
        ";
        $param = "%{$search_keyword}%";
        $recipes_to_display = $db->select($sql, [$param, $param]);
    } else {
        // 【通常時】ランダムに20件選ぶ
        $recipes_to_display = $db->select("SELECT * FROM recipes ORDER BY RAND() LIMIT 20");
    }
} catch (Exception $e) {
    $error_message = "データベースエラー: " . $e->getMessage();
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
                        <a href="/recipe/recipe_detail.php?id=<?php echo htmlspecialchars($recipe['id'] ?? ''); ?>" class="recipe-item">
                            <img src="<?php echo htmlspecialchars($recipe['thumbnail_url'] ?? ''); ?>/preview" alt="<?php echo htmlspecialchars($recipe['name'] ?? ''); ?>">
                            <p><?php echo htmlspecialchars($recipe['name'] ?? ''); ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="error-message">
                    <?php if (!empty($error_message)): ?>
                        <?php echo htmlspecialchars($error_message); ?>
                    <?php elseif (!empty($search_keyword)): ?>
                        「<?php echo htmlspecialchars($search_keyword); ?>」の検索結果が見つかりませんでした。
                    <?php else: ?>
                        レシピが見つかりませんでした。データベースを確認してください。
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </section>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

</body>
</html>