<?php
/**
 * ダミーデータ生成スクリプト
 * M5Stack連携APIのテスト用にダミーデータを生成
 * 
 * 使用方法:
 *   php generate_dummy_data.php
 *   または
 *   http://localhost:8000/api/generate_dummy_data.php
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "<h1>ダミーデータ生成</h1>\n";
    echo "<pre>\n";
    
    // 既存のダミーデータを削除（オプション）
    $deleteCount = $pdo->exec("DELETE FROM congestion_data WHERE device_id LIKE 'M5Stack_Dummy%'");
    echo "既存のダミーデータを削除: {$deleteCount}件\n\n";
    
    // 店舗一覧を取得
    $stores = $db->select("SELECT id, name FROM stores ORDER BY id LIMIT 5");
    
    if (empty($stores)) {
        throw new Exception("店舗データが見つかりません。先に店舗データを作成してください。");
    }
    
    $totalInserted = 0;
    
    // 各店舗に対して過去24時間分のデータを生成
    foreach ($stores as $store) {
        $storeId = $store['id'];
        $storeName = $store['name'];
        $deviceId = "M5Stack_Dummy_" . str_pad($storeId, 3, '0', STR_PAD_LEFT);
        
        echo "店舗: {$storeName} (ID: {$storeId})\n";
        
        $inserted = 0;
        
        // 24時間分のデータを生成（1時間ごと）
        for ($hoursAgo = 23; $hoursAgo >= 0; $hoursAgo--) {
            // 時間帯に応じた混雑率を計算
            $hour = (date('H') - $hoursAgo + 24) % 24;
            
            // 時間帯別の混雑率パターン
            if ($hour >= 6 && $hour < 9) {
                // 朝: 40-60%
                $congestionLevel = rand(40, 60);
                $notes = '朝の時間帯';
            } elseif ($hour >= 9 && $hour < 12) {
                // 午前: 55-75%
                $congestionLevel = rand(55, 75);
                $notes = '午前中';
            } elseif ($hour >= 12 && $hour < 14) {
                // ランチ: 70-85%
                $congestionLevel = rand(70, 85);
                $notes = 'ランチタイム';
            } elseif ($hour >= 14 && $hour < 17) {
                // 午後: 65-80%
                $congestionLevel = rand(65, 80);
                $notes = '午後';
            } elseif ($hour >= 17 && $hour < 20) {
                // 夕方: 80-95%
                $congestionLevel = rand(80, 95);
                $notes = '夕方ピーク';
            } elseif ($hour >= 20 && $hour < 22) {
                // 夜: 60-75%
                $congestionLevel = rand(60, 75);
                $notes = '夜';
            } elseif ($hour >= 22 || $hour < 6) {
                // 深夜・早朝: 25-50%
                $congestionLevel = rand(25, 50);
                $notes = $hour < 6 ? '早朝' : '深夜';
            } else {
                $congestionLevel = rand(50, 70);
                $notes = '通常';
            }
            
            // 店舗タイプによる調整（薬局は全体的に低め）
            if (strpos($storeName, '薬局') !== false || strpos($storeName, 'マツモトキヨシ') !== false) {
                $congestionLevel = max(20, $congestionLevel - 20);
            }
            
            $measuredAt = date('Y-m-d H:i:s', strtotime("-{$hoursAgo} hours"));
            
            $stmt = $pdo->prepare("
                INSERT INTO congestion_data (store_id, congestion_level, device_id, notes, measured_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $storeId,
                $congestionLevel,
                $deviceId,
                $notes,
                $measuredAt
            ]);
            
            $inserted++;
        }
        
        echo "  挿入: {$inserted}件\n";
        $totalInserted += $inserted;
    }
    
    echo "\n";
    echo "=== 統計情報 ===\n";
    
    // 統計情報を取得
    $stats = $db->selectOne("
        SELECT 
            COUNT(*) as total_records,
            COUNT(DISTINCT store_id) as store_count,
            AVG(congestion_level) as avg_congestion,
            MIN(congestion_level) as min_congestion,
            MAX(congestion_level) as max_congestion,
            MIN(measured_at) as oldest_record,
            MAX(measured_at) as newest_record
        FROM congestion_data
        WHERE device_id LIKE 'M5Stack_Dummy%'
    ");
    
    echo "総レコード数: " . number_format($stats['total_records']) . "件\n";
    echo "店舗数: {$stats['store_count']}店\n";
    echo "平均混雑率: " . number_format($stats['avg_congestion'], 1) . "%\n";
    echo "最小混雑率: {$stats['min_congestion']}%\n";
    echo "最大混雑率: {$stats['max_congestion']}%\n";
    echo "最古のレコード: {$stats['oldest_record']}\n";
    echo "最新のレコード: {$stats['newest_record']}\n";
    
    echo "\n✅ ダミーデータ生成完了！\n";
    echo "</pre>\n";
    
    echo "<h2>最新の混雑率データ（各店舗）</h2>\n";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>\n";
    echo "<tr><th>店舗名</th><th>混雑率</th><th>測定時刻</th><th>デバイスID</th></tr>\n";
    
    $latest = $db->select("
        SELECT 
            s.name as store_name,
            cd.congestion_level,
            cd.measured_at,
            cd.device_id
        FROM congestion_data cd
        JOIN stores s ON cd.store_id = s.id
        WHERE cd.device_id LIKE 'M5Stack_Dummy%'
        AND cd.measured_at = (
            SELECT MAX(measured_at) 
            FROM congestion_data 
            WHERE store_id = cd.store_id 
            AND device_id LIKE 'M5Stack_Dummy%'
        )
        ORDER BY s.id
    ");
    
    foreach ($latest as $row) {
        $level = $row['congestion_level'];
        $color = $level >= 80 ? 'red' : ($level >= 60 ? 'orange' : 'green');
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['store_name']) . "</td>";
        echo "<td style='color: {$color}; font-weight: bold;'>{$level}%</td>";
        echo "<td>" . htmlspecialchars($row['measured_at']) . "</td>";
        echo "<td>" . htmlspecialchars($row['device_id']) . "</td>";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
} catch (Exception $e) {
    echo "<pre>\n";
    echo "❌ エラー: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "</pre>\n";
}
