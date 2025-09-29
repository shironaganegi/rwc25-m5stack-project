<?php
// レシピを50件取得してJSONファイルに保存するスクリプト

echo "レシピデータの取得を開始します...\n";

$recipes_data = [];
$unique_ids = []; // 重複を避けるためのIDリスト

// 50件のユニークなレシピを取得するまでループ
while (count($recipes_data) < 5) {
    $api_url = 'https://www.themealdb.com/api/json/v1/1/random.php';
    $json_data = @file_get_contents($api_url);

    if ($json_data) {
        $data = json_decode($json_data, true);
        if (isset($data['meals'][0])) {
            $recipe = $data['meals'][0];
            // IDがまだリストになければ追加
            if (!in_array($recipe['idMeal'], $unique_ids)) {
                $recipes_data[] = $recipe;
                $unique_ids[] = $recipe['idMeal'];
                echo count($recipes_data) . "件目のレシピを取得: " . $recipe['strMeal'] . "\n";
            }
        }
    }
    usleep(200000); // サーバー負荷軽減のため0.2秒待機
}

$json_file_path = __DIR__ . '/recipes.json';

// 取得したレシピデータをJSON形式でファイルに保存
// JSON_PRETTY_PRINTで見やすい形式に、JSON_UNESCAPED_UNICODEで日本語の文字化けを防ぐ
file_put_contents($json_file_path, json_encode($recipes_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n完了！ " . count($recipes_data) . "件のレシピを 'recipes.json' に保存しました。\n";
?>