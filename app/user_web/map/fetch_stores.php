<?php
/**
 * OpenStreetMap APIから近畿大学周辺のスーパーマーケットを取得してデータベースに追加するスクリプト
 * 使用方法: docker exec -it user_web php /var/www/html/map/fetch_stores.php
 */

require_once __DIR__ . '/../includes/database.php';

echo "=== スーパーマーケットデータ取得開始 ===\n\n";

// 大阪府全体を4つのエリアに分割して取得（タイムアウトを避けるため）
// 各エリア: 南、西、北、東の順（Overpass API形式）
$areas = [
    // エリア1: 北西部（大阪市西部、堺市北部、高槻市など）
    ['south' => 34.5, 'west' => 135.1, 'north' => 34.9, 'east' => 135.5, 'name' => '北西部'],
    // エリア2: 北東部（大阪市東部、東大阪市、八尾市、高槻市東部など）
    ['south' => 34.5, 'west' => 135.5, 'north' => 34.9, 'east' => 135.9, 'name' => '北東部'],
    // エリア3: 南西部（堺市南部、和泉市、泉佐野市など）
    ['south' => 34.1, 'west' => 135.1, 'north' => 34.5, 'east' => 135.5, 'name' => '南西部'],
    // エリア4: 南東部（大阪市南部、東大阪市南部など）
    ['south' => 34.1, 'west' => 135.5, 'north' => 34.5, 'east' => 135.9, 'name' => '南東部']
];

// Overpass APIのURL
$api_url = "https://overpass-api.de/api/interpreter";

$all_elements = [];

// 各エリアを順番に取得
foreach ($areas as $area_index => $area) {
    $south = $area['south'];
    $west = $area['west'];
    $north = $area['north'];
    $east = $area['east'];
    $area_name = $area['name'];
    
    echo "=== エリア" . ($area_index + 1) . ": {$area_name} を取得中 ===\n";
    echo "検索範囲: 南{$south}, 西{$west}, 北{$north}, 東{$east}\n";
    
    // Overpass APIクエリ（スーパーマーケットを検索）
    // 正しいタグ: shop=supermarket（amenity=supermarketは非推奨）
    // 念のため両方を検索（後方互換性のため）
    $overpass_query = "[out:json][timeout:60];
(
  node[\"shop\"=\"supermarket\"]({$south},{$west},{$north},{$east});
  way[\"shop\"=\"supermarket\"]({$south},{$west},{$north},{$east});
  relation[\"shop\"=\"supermarket\"]({$south},{$west},{$north},{$east});
  node[\"amenity\"=\"supermarket\"]({$south},{$west},{$north},{$east});
  way[\"amenity\"=\"supermarket\"]({$south},{$west},{$north},{$east});
  relation[\"amenity\"=\"supermarket\"]({$south},{$west},{$north},{$east});
);
out center meta;";
    
    // APIリクエスト - cURLを使用
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $overpass_query);
    curl_setopt($ch, CURLOPT_USERAGENT, 'SupermarketApp/1.0 (contact@example.com)');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $json_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($json_data === false || !empty($curl_error)) {
        echo "警告: エリア{$area_name}のAPIリクエストに失敗しました\nHTTPコード: {$http_code}\nエラー: {$curl_error}\n";
        echo "次のエリアに進みます...\n\n";
        sleep(2); // レート制限を考慮
        continue;
    }
    
    if ($http_code !== 200) {
        echo "警告: エリア{$area_name}のAPIリクエストが失敗しました\nHTTPコード: {$http_code}\n";
        echo "次のエリアに進みます...\n\n";
        sleep(2); // レート制限を考慮
        continue;
    }
    
    $result = json_decode($json_data, true);
    
    // デバッグ情報: レスポンスの構造を確認
    if (!$result) {
        echo "警告: エリア{$area_name}のJSON解析に失敗しました\n";
        echo "レスポンス（最初の500文字）: " . substr($json_data, 0, 500) . "\n";
        echo "次のエリアに進みます...\n\n";
        sleep(2);
        continue;
    }
    
    // デバッグ情報: レスポンスのキーを確認
    if (!isset($result['elements'])) {
        echo "警告: エリア{$area_name}のレスポンスに'elements'キーがありません\n";
        echo "レスポンスのキー: " . implode(', ', array_keys($result)) . "\n";
        if (isset($result['remark'])) {
            echo "備考: " . $result['remark'] . "\n";
        }
        echo "次のエリアに進みます...\n\n";
        sleep(2);
        continue;
    }
    
    if (!is_array($result['elements'])) {
        echo "警告: エリア{$area_name}の'elements'が配列ではありません\n";
        echo "次のエリアに進みます...\n\n";
        sleep(2);
        continue;
    }
    
    $elements = $result['elements'];
    $all_elements = array_merge($all_elements, $elements);
    echo "取得した要素数: " . count($elements) . "件\n";
    echo "累計: " . count($all_elements) . "件\n";
    
    // デバッグ情報: 最初の要素の構造を確認（最初のエリアのみ）
    if ($area_index === 0 && count($elements) > 0) {
        echo "デバッグ: 最初の要素の構造:\n";
        echo "  type: " . ($elements[0]['type'] ?? 'N/A') . "\n";
        if (isset($elements[0]['tags'])) {
            echo "  tags: " . json_encode($elements[0]['tags'], JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "  tags: N/A\n";
        }
        echo "\n";
    }
    
    echo "\n";
    
    // レート制限を考慮して待機（各エリアの取得後に2秒待機）
    if ($area_index < count($areas) - 1) {
        sleep(2);
    }
}

$elements = $all_elements;
echo "=== 全エリア取得完了 ===\n";
echo "合計取得要素数: " . count($elements) . "件\n\n";

// Overpass APIの結果を店舗データに変換
$stores = [];
foreach ($elements as $element) {
    if (!isset($element['tags']['name'])) {
        continue; // 名前がない場合はスキップ
    }
    
    $lat = null;
    $lon = null;
    
    // 座標を取得（nodeの場合は直接、way/relationの場合はcenter）
    if (isset($element['lat']) && isset($element['lon'])) {
        $lat = floatval($element['lat']);
        $lon = floatval($element['lon']);
    } elseif (isset($element['center'])) {
        $lat = floatval($element['center']['lat']);
        $lon = floatval($element['center']['lon']);
    }
    
    if ($lat === null || $lon === null) {
        continue;
    }
    
    // 住所を構築
    $address_parts = [];
    if (isset($element['tags']['addr:street'])) {
        $address_parts[] = $element['tags']['addr:street'];
    }
    if (isset($element['tags']['addr:housenumber'])) {
        $address_parts[] = $element['tags']['addr:housenumber'];
    }
    if (isset($element['tags']['addr:city'])) {
        $address_parts[] = $element['tags']['addr:city'];
    }
    if (isset($element['tags']['addr:postcode'])) {
        $address_parts[] = $element['tags']['addr:postcode'];
    }
    
    $stores[] = [
        'name' => $element['tags']['name'],
        'lat' => $lat,
        'lon' => $lon,
        'address' => implode(' ', $address_parts),
        'display_name' => $element['tags']['name'] . (count($address_parts) > 0 ? ' - ' . implode(' ', $address_parts) : '')
    ];
}

echo "有効なスーパーマーケット数: " . count($stores) . "件\n\n";

// データベース接続
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // トランザクション開始
    $pdo->beginTransaction();
    
    $importedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;
    
    foreach ($stores as $index => $store) {
        try {
            $name = $store['name'] ?? '不明な店舗';
            $lat = floatval($store['lat'] ?? 0);
            $lng = floatval($store['lon'] ?? 0);
            
            // 座標が無効な場合はスキップ
            if ($lat == 0 || $lng == 0) {
                $skippedCount++;
                echo "スキップ: {$name} (座標が無効)\n";
                continue;
            }
            
            // 住所を取得
            $address = $store['address'] ?? '';
            
            // 既存店舗チェック（名前と座標の両方で判定）
            // 座標の閾値: 0.0001度 ≈ 約11メートル（調整可能）
            $coordinate_threshold = 0.0001;
            
            $stmt = $pdo->prepare("
                SELECT id, name, latitude, longitude FROM stores 
                WHERE (
                    name = ? 
                    AND ABS(latitude - ?) < ? 
                    AND ABS(longitude - ?) < ?
                )
            ");
            $stmt->execute([$name, $lat, $coordinate_threshold, $lng, $coordinate_threshold]);
            $existingStore = $stmt->fetch();
            
            if ($existingStore) {
                $skippedCount++;
                $reason = "既存店舗と一致 (ID: {$existingStore['id']}, 名前: {$existingStore['name']})";
                if (($index + 1) % 10 == 0 || $skippedCount <= 5) {
                    echo "スキップ: {$name} - {$reason}\n";
                }
                continue;
            }
            
            // 座標のみの重複チェック（同じ場所に別名の店舗がある場合の警告用）
            $stmt = $pdo->prepare("
                SELECT id, name FROM stores 
                WHERE ABS(latitude - ?) < ? 
                AND ABS(longitude - ?) < ?
                AND name != ?
            ");
            $stmt->execute([$lat, $coordinate_threshold, $lng, $coordinate_threshold, $name]);
            $nearbyStore = $stmt->fetch();
            
            if ($nearbyStore) {
                // 警告のみ（登録は継続）
                echo "警告: {$name} は既存店舗「{$nearbyStore['name']}」(ID: {$nearbyStore['id']}) と近い位置にあります\n";
            }
            
            // 店舗情報を挿入
            $stmt = $pdo->prepare("
                INSERT INTO stores (
                    name, address, latitude, longitude, category
                ) VALUES (?, ?, ?, ?, 'supermarket')
            ");
            
            $stmt->execute([
                $name,
                $address,
                $lat,
                $lng
            ]);
            
            $importedCount++;
            
            // 進捗表示（10件ごと）
            if (($index + 1) % 10 == 0) {
                echo "処理中... " . ($index + 1) . "/" . count($stores) . " (インポート済み: {$importedCount}件)\n";
            }
            
        } catch (Exception $e) {
            $errorCount++;
            echo "エラー [店舗: {$name}]: " . $e->getMessage() . "\n";
        }
    }
    
    // トランザクションコミット
    $pdo->commit();
    
    echo "\n=== インポート完了 ===\n";
    echo "成功: {$importedCount}件\n";
    echo "スキップ: {$skippedCount}件 (既存データ)\n";
    echo "エラー: {$errorCount}件\n";
    
    // 統計情報を表示
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM stores WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    $totalStores = $stmt->fetch()['total'];
    
    echo "\n=== データベース統計 ===\n";
    echo "登録店舗総数: {$totalStores}件\n";
    
    // カテゴリ別集計
    $stmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM stores 
        WHERE latitude IS NOT NULL AND longitude IS NOT NULL
        GROUP BY category 
        ORDER BY count DESC
    ");
    
    echo "\n=== カテゴリ別店舗数 ===\n";
    while ($row = $stmt->fetch()) {
        echo sprintf("%-20s: %3d件\n", $row['category'], $row['count']);
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("\nエラー: " . $e->getMessage() . "\n");
}

echo "\n完了しました！\n";
