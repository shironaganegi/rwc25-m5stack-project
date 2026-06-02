-- 混雑率データのダミーデータ作成スクリプト
-- M5Stack連携APIのテスト用

USE supermarket_app;

-- 既存のダミーデータを削除（オプション）
-- DELETE FROM congestion_data WHERE device_id LIKE 'M5Stack_Dummy%';

-- 各店舗に対して過去24時間分の混雑率データを生成
-- 1時間ごとにデータを挿入

-- 店舗1（ライフ八戸ノ里店）のダミーデータ
INSERT INTO congestion_data (store_id, congestion_level, device_id, notes, measured_at) VALUES
(1, 45, 'M5Stack_Dummy_001', '朝の時間帯', DATE_SUB(NOW(), INTERVAL 23 HOUR)),
(1, 60, 'M5Stack_Dummy_001', '午前中', DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(1, 75, 'M5Stack_Dummy_001', 'ランチタイム', DATE_SUB(NOW(), INTERVAL 21 HOUR)),
(1, 80, 'M5Stack_Dummy_001', '午後', DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(1, 85, 'M5Stack_Dummy_001', '夕方', DATE_SUB(NOW(), INTERVAL 19 HOUR)),
(1, 90, 'M5Stack_Dummy_001', '夕方ピーク', DATE_SUB(NOW(), INTERVAL 18 HOUR)),
(1, 70, 'M5Stack_Dummy_001', '夜', DATE_SUB(NOW(), INTERVAL 17 HOUR)),
(1, 50, 'M5Stack_Dummy_001', '夜遅く', DATE_SUB(NOW(), INTERVAL 16 HOUR)),
(1, 40, 'M5Stack_Dummy_001', '深夜', DATE_SUB(NOW(), INTERVAL 15 HOUR)),
(1, 35, 'M5Stack_Dummy_001', '早朝', DATE_SUB(NOW(), INTERVAL 14 HOUR)),
(1, 50, 'M5Stack_Dummy_001', '朝', DATE_SUB(NOW(), INTERVAL 13 HOUR)),
(1, 65, 'M5Stack_Dummy_001', '午前', DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(1, 70, 'M5Stack_Dummy_001', '昼前', DATE_SUB(NOW(), INTERVAL 11 HOUR)),
(1, 75, 'M5Stack_Dummy_001', '昼', DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(1, 80, 'M5Stack_Dummy_001', '午後', DATE_SUB(NOW(), INTERVAL 9 HOUR)),
(1, 85, 'M5Stack_Dummy_001', '夕方', DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(1, 88, 'M5Stack_Dummy_001', '夕方ピーク', DATE_SUB(NOW(), INTERVAL 7 HOUR)),
(1, 75, 'M5Stack_Dummy_001', '夜', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(1, 60, 'M5Stack_Dummy_001', '夜', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(1, 45, 'M5Stack_Dummy_001', '夜遅く', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(1, 40, 'M5Stack_Dummy_001', '深夜', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(1, 35, 'M5Stack_Dummy_001', '深夜', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 30, 'M5Stack_Dummy_001', '早朝', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 55, 'M5Stack_Dummy_001', '現在', NOW());

-- 店舗2（イオン登美ヶ丘店）のダミーデータ
INSERT INTO congestion_data (store_id, congestion_level, device_id, notes, measured_at) VALUES
(2, 50, 'M5Stack_Dummy_002', '朝の時間帯', DATE_SUB(NOW(), INTERVAL 23 HOUR)),
(2, 65, 'M5Stack_Dummy_002', '午前中', DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(2, 70, 'M5Stack_Dummy_002', 'ランチタイム', DATE_SUB(NOW(), INTERVAL 21 HOUR)),
(2, 75, 'M5Stack_Dummy_002', '午後', DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(2, 80, 'M5Stack_Dummy_002', '夕方', DATE_SUB(NOW(), INTERVAL 19 HOUR)),
(2, 85, 'M5Stack_Dummy_002', '夕方ピーク', DATE_SUB(NOW(), INTERVAL 18 HOUR)),
(2, 75, 'M5Stack_Dummy_002', '夜', DATE_SUB(NOW(), INTERVAL 17 HOUR)),
(2, 60, 'M5Stack_Dummy_002', '夜遅く', DATE_SUB(NOW(), INTERVAL 16 HOUR)),
(2, 45, 'M5Stack_Dummy_002', '深夜', DATE_SUB(NOW(), INTERVAL 15 HOUR)),
(2, 40, 'M5Stack_Dummy_002', '早朝', DATE_SUB(NOW(), INTERVAL 14 HOUR)),
(2, 55, 'M5Stack_Dummy_002', '朝', DATE_SUB(NOW(), INTERVAL 13 HOUR)),
(2, 70, 'M5Stack_Dummy_002', '午前', DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(2, 75, 'M5Stack_Dummy_002', '昼前', DATE_SUB(NOW(), INTERVAL 11 HOUR)),
(2, 78, 'M5Stack_Dummy_002', '昼', DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(2, 82, 'M5Stack_Dummy_002', '午後', DATE_SUB(NOW(), INTERVAL 9 HOUR)),
(2, 88, 'M5Stack_Dummy_002', '夕方', DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(2, 92, 'M5Stack_Dummy_002', '夕方ピーク', DATE_SUB(NOW(), INTERVAL 7 HOUR)),
(2, 80, 'M5Stack_Dummy_002', '夜', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(2, 65, 'M5Stack_Dummy_002', '夜', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(2, 50, 'M5Stack_Dummy_002', '夜遅く', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(2, 45, 'M5Stack_Dummy_002', '深夜', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 40, 'M5Stack_Dummy_002', '深夜', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 35, 'M5Stack_Dummy_002', '早朝', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 60, 'M5Stack_Dummy_002', '現在', NOW());

-- 店舗3（マツモトキヨシ八戸ノ里駅前店）のダミーデータ
INSERT INTO congestion_data (store_id, congestion_level, device_id, notes, measured_at) VALUES
(3, 30, 'M5Stack_Dummy_003', '朝の時間帯', DATE_SUB(NOW(), INTERVAL 23 HOUR)),
(3, 40, 'M5Stack_Dummy_003', '午前中', DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(3, 50, 'M5Stack_Dummy_003', 'ランチタイム', DATE_SUB(NOW(), INTERVAL 21 HOUR)),
(3, 55, 'M5Stack_Dummy_003', '午後', DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(3, 60, 'M5Stack_Dummy_003', '夕方', DATE_SUB(NOW(), INTERVAL 19 HOUR)),
(3, 70, 'M5Stack_Dummy_003', '夕方ピーク', DATE_SUB(NOW(), INTERVAL 18 HOUR)),
(3, 65, 'M5Stack_Dummy_003', '夜', DATE_SUB(NOW(), INTERVAL 17 HOUR)),
(3, 50, 'M5Stack_Dummy_003', '夜遅く', DATE_SUB(NOW(), INTERVAL 16 HOUR)),
(3, 35, 'M5Stack_Dummy_003', '深夜', DATE_SUB(NOW(), INTERVAL 15 HOUR)),
(3, 30, 'M5Stack_Dummy_003', '早朝', DATE_SUB(NOW(), INTERVAL 14 HOUR)),
(3, 45, 'M5Stack_Dummy_003', '朝', DATE_SUB(NOW(), INTERVAL 13 HOUR)),
(3, 55, 'M5Stack_Dummy_003', '午前', DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(3, 60, 'M5Stack_Dummy_003', '昼前', DATE_SUB(NOW(), INTERVAL 11 HOUR)),
(3, 65, 'M5Stack_Dummy_003', '昼', DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(3, 70, 'M5Stack_Dummy_003', '午後', DATE_SUB(NOW(), INTERVAL 9 HOUR)),
(3, 75, 'M5Stack_Dummy_003', '夕方', DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(3, 80, 'M5Stack_Dummy_003', '夕方ピーク', DATE_SUB(NOW(), INTERVAL 7 HOUR)),
(3, 70, 'M5Stack_Dummy_003', '夜', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(3, 55, 'M5Stack_Dummy_003', '夜', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(3, 45, 'M5Stack_Dummy_003', '夜遅く', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(3, 35, 'M5Stack_Dummy_003', '深夜', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(3, 30, 'M5Stack_Dummy_003', '深夜', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, 25, 'M5Stack_Dummy_003', '早朝', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(3, 50, 'M5Stack_Dummy_003', '現在', NOW());

-- 店舗4（スギ薬局小阪店）のダミーデータ
INSERT INTO congestion_data (store_id, congestion_level, device_id, notes, measured_at) VALUES
(4, 25, 'M5Stack_Dummy_004', '朝の時間帯', DATE_SUB(NOW(), INTERVAL 23 HOUR)),
(4, 35, 'M5Stack_Dummy_004', '午前中', DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(4, 45, 'M5Stack_Dummy_004', 'ランチタイム', DATE_SUB(NOW(), INTERVAL 21 HOUR)),
(4, 50, 'M5Stack_Dummy_004', '午後', DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(4, 55, 'M5Stack_Dummy_004', '夕方', DATE_SUB(NOW(), INTERVAL 19 HOUR)),
(4, 65, 'M5Stack_Dummy_004', '夕方ピーク', DATE_SUB(NOW(), INTERVAL 18 HOUR)),
(4, 60, 'M5Stack_Dummy_004', '夜', DATE_SUB(NOW(), INTERVAL 17 HOUR)),
(4, 45, 'M5Stack_Dummy_004', '夜遅く', DATE_SUB(NOW(), INTERVAL 16 HOUR)),
(4, 30, 'M5Stack_Dummy_004', '深夜', DATE_SUB(NOW(), INTERVAL 15 HOUR)),
(4, 25, 'M5Stack_Dummy_004', '早朝', DATE_SUB(NOW(), INTERVAL 14 HOUR)),
(4, 40, 'M5Stack_Dummy_004', '朝', DATE_SUB(NOW(), INTERVAL 13 HOUR)),
(4, 50, 'M5Stack_Dummy_004', '午前', DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(4, 55, 'M5Stack_Dummy_004', '昼前', DATE_SUB(NOW(), INTERVAL 11 HOUR)),
(4, 60, 'M5Stack_Dummy_004', '昼', DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(4, 65, 'M5Stack_Dummy_004', '午後', DATE_SUB(NOW(), INTERVAL 9 HOUR)),
(4, 70, 'M5Stack_Dummy_004', '夕方', DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(4, 75, 'M5Stack_Dummy_004', '夕方ピーク', DATE_SUB(NOW(), INTERVAL 7 HOUR)),
(4, 65, 'M5Stack_Dummy_004', '夜', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(4, 50, 'M5Stack_Dummy_004', '夜', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(4, 40, 'M5Stack_Dummy_004', '夜遅く', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(4, 30, 'M5Stack_Dummy_004', '深夜', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(4, 25, 'M5Stack_Dummy_004', '深夜', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(4, 20, 'M5Stack_Dummy_004', '早朝', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(4, 45, 'M5Stack_Dummy_004', '現在', NOW());

-- 店舗5（関西スーパー若江店）のダミーデータ
INSERT INTO congestion_data (store_id, congestion_level, device_id, notes, measured_at) VALUES
(5, 40, 'M5Stack_Dummy_005', '朝の時間帯', DATE_SUB(NOW(), INTERVAL 23 HOUR)),
(5, 55, 'M5Stack_Dummy_005', '午前中', DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(5, 65, 'M5Stack_Dummy_005', 'ランチタイム', DATE_SUB(NOW(), INTERVAL 21 HOUR)),
(5, 70, 'M5Stack_Dummy_005', '午後', DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(5, 75, 'M5Stack_Dummy_005', '夕方', DATE_SUB(NOW(), INTERVAL 19 HOUR)),
(5, 85, 'M5Stack_Dummy_005', '夕方ピーク', DATE_SUB(NOW(), INTERVAL 18 HOUR)),
(5, 75, 'M5Stack_Dummy_005', '夜', DATE_SUB(NOW(), INTERVAL 17 HOUR)),
(5, 60, 'M5Stack_Dummy_005', '夜遅く', DATE_SUB(NOW(), INTERVAL 16 HOUR)),
(5, 45, 'M5Stack_Dummy_005', '深夜', DATE_SUB(NOW(), INTERVAL 15 HOUR)),
(5, 35, 'M5Stack_Dummy_005', '早朝', DATE_SUB(NOW(), INTERVAL 14 HOUR)),
(5, 50, 'M5Stack_Dummy_005', '朝', DATE_SUB(NOW(), INTERVAL 13 HOUR)),
(5, 65, 'M5Stack_Dummy_005', '午前', DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(5, 70, 'M5Stack_Dummy_005', '昼前', DATE_SUB(NOW(), INTERVAL 11 HOUR)),
(5, 75, 'M5Stack_Dummy_005', '昼', DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(5, 80, 'M5Stack_Dummy_005', '午後', DATE_SUB(NOW(), INTERVAL 9 HOUR)),
(5, 85, 'M5Stack_Dummy_005', '夕方', DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(5, 90, 'M5Stack_Dummy_005', '夕方ピーク', DATE_SUB(NOW(), INTERVAL 7 HOUR)),
(5, 80, 'M5Stack_Dummy_005', '夜', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(5, 65, 'M5Stack_Dummy_005', '夜', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(5, 50, 'M5Stack_Dummy_005', '夜遅く', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(5, 40, 'M5Stack_Dummy_005', '深夜', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(5, 35, 'M5Stack_Dummy_005', '深夜', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(5, 30, 'M5Stack_Dummy_005', '早朝', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(5, 55, 'M5Stack_Dummy_005', '現在', NOW());

-- 統計情報を表示
SELECT 
    'ダミーデータ作成完了' as status,
    COUNT(*) as total_records,
    COUNT(DISTINCT store_id) as store_count,
    MIN(measured_at) as oldest_record,
    MAX(measured_at) as newest_record
FROM congestion_data
WHERE device_id LIKE 'M5Stack_Dummy%';
