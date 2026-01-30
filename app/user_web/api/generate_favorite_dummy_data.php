<?php
// エラー表示設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = Database::getInstance();
    $userId = 1; // ダミーユーザーID
    
    echo "<h1>お気に入り店舗ダミーデータ生成</h1>";
    
    // 全店舗を取得
    $allStores = $db->getAllStores();
    
    if (empty($allStores)) {
        echo "<p>店舗データが見つかりません。先に店舗データを作成してください。</p>";
        exit;
    }
    
    echo "<p>全店舗数: " . count($allStores) . "</p>";
    
    // 追加した店舗のカウンター
    $addedCount = 0;
    $skippedCount = 0;
    
    // ランダムに3〜5件選択するためにシャッフル
    shuffle($allStores);
    $targetStores = array_slice($allStores, 0, 5); // 最大5件抽出
    
    echo "<ul>";
    foreach ($targetStores as $store) {
        $result = $db->addFavoriteStore($userId, $store['id']);
        
        echo "<li>{$store['name']} (ID: {$store['id']}) - ";
        if ($result !== false) {
            echo "<span style='color:green;'>追加成功</span>";
            $addedCount++;
        } else {
            echo "<span style='color:gray;'>既に追加済み</span>";
            $skippedCount++;
        }
        echo "</li>";
    }
    echo "</ul>";
    
    echo "<p>処理完了: 追加 {$addedCount} 件, スキップ {$skippedCount} 件</p>";
    echo "<p><a href='/favorites/favorites.php'>お気に入り一覧へ戻る</a></p>";
    
} catch (Exception $e) {
    http_response_code(500);
    echo "<h2 style='color:red;'>エラーが発生しました</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
