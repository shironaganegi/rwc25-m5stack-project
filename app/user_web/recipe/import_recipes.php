<?php
/**
 * レシピJSONデータをデータベースにインポートするスクリプト
 * 使用方法: docker exec -it user_web php /var/www/html/recipe/import_recipes.php
 */

require_once __DIR__ . '/../includes/database.php';

echo "=== レシピデータインポート開始 ===\n\n";

// JSONファイルの読み込み
$jsonFile = __DIR__ . '/recipes.json';
if (!file_exists($jsonFile)) {
    die("エラー: recipes.json が見つかりません\n");
}

$jsonData = file_get_contents($jsonFile);
$recipes = json_decode($jsonData, true);

if (!$recipes || !is_array($recipes)) {
    die("エラー: JSONデータの解析に失敗しました\n");
}

echo "読み込んだレシピ数: " . count($recipes) . "件\n\n";

// データベース接続
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // トランザクション開始
    $pdo->beginTransaction();
    
    $importedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;
    
    foreach ($recipes as $index => $recipe) {
        try {
            // 既存レシピチェック
            $stmt = $pdo->prepare("SELECT id FROM recipes WHERE external_id = ?");
            $stmt->execute([$recipe['idMeal']]);
            $existingRecipe = $stmt->fetch();
            
            if ($existingRecipe) {
                $skippedCount++;
                if (($index + 1) % 50 == 0) {
                    echo "処理中... " . ($index + 1) . "/" . count($recipes) . " (スキップ: {$recipe['strMeal']})\n";
                }
                continue;
            }
            
            // レシピ情報を挿入
            $stmt = $pdo->prepare("
                INSERT INTO recipes (
                    external_id, name, alternate_name, category, area,
                    instructions, thumbnail_url, tags, youtube_url
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $recipe['idMeal'],
                $recipe['strMeal'],
                $recipe['strMealAlternate'],
                $recipe['strCategory'],
                $recipe['strArea'],
                $recipe['strInstructions'],
                $recipe['strMealThumb'],
                $recipe['strTags'],
                $recipe['strYoutube']
            ]);
            
            $recipeId = $pdo->lastInsertId();
            
            // 材料情報を挿入
            $ingredientStmt = $pdo->prepare("
                INSERT INTO recipe_ingredients (
                    recipe_id, ingredient_name, measure, ingredient_order
                ) VALUES (?, ?, ?, ?)
            ");
            
            $ingredientOrder = 1;
            for ($i = 1; $i <= 20; $i++) {
                $ingredientKey = "strIngredient{$i}";
                $measureKey = "strMeasure{$i}";
                
                if (isset($recipe[$ingredientKey]) && 
                    !empty($recipe[$ingredientKey]) && 
                    trim($recipe[$ingredientKey]) !== '') {
                    
                    $ingredientStmt->execute([
                        $recipeId,
                        trim($recipe[$ingredientKey]),
                        isset($recipe[$measureKey]) ? trim($recipe[$measureKey]) : null,
                        $ingredientOrder
                    ]);
                    
                    $ingredientOrder++;
                }
            }
            
            $importedCount++;
            
            // 進捗表示（50件ごと）
            if (($index + 1) % 50 == 0) {
                echo "処理中... " . ($index + 1) . "/" . count($recipes) . " (インポート済み: {$importedCount}件)\n";
            }
            
        } catch (Exception $e) {
            $errorCount++;
            echo "エラー [レシピID: {$recipe['idMeal']}]: " . $e->getMessage() . "\n";
        }
    }
    
    // トランザクションコミット
    $pdo->commit();
    
    echo "\n=== インポート完了 ===\n";
    echo "成功: {$importedCount}件\n";
    echo "スキップ: {$skippedCount}件 (既存データ)\n";
    echo "エラー: {$errorCount}件\n";
    
    // 統計情報を表示
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM recipes");
    $totalRecipes = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM recipe_ingredients");
    $totalIngredients = $stmt->fetch()['total'];
    
    echo "\n=== データベース統計 ===\n";
    echo "登録レシピ総数: {$totalRecipes}件\n";
    echo "登録材料総数: {$totalIngredients}件\n";
    
    // カテゴリ別集計
    $stmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM recipes 
        WHERE category IS NOT NULL 
        GROUP BY category 
        ORDER BY count DESC 
        LIMIT 10
    ");
    
    echo "\n=== カテゴリ別レシピ数（上位10） ===\n";
    while ($row = $stmt->fetch()) {
        echo sprintf("%-20s: %3d件\n", $row['category'], $row['count']);
    }
    
    // 地域別集計
    $stmt = $pdo->query("
        SELECT area, COUNT(*) as count 
        FROM recipes 
        WHERE area IS NOT NULL 
        GROUP BY area 
        ORDER BY count DESC 
        LIMIT 10
    ");
    
    echo "\n=== 地域別レシピ数（上位10） ===\n";
    while ($row = $stmt->fetch()) {
        echo sprintf("%-20s: %3d件\n", $row['area'], $row['count']);
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("\nエラー: " . $e->getMessage() . "\n");
}

echo "\n完了しました！\n";
