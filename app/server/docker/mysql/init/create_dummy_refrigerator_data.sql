-- 冷蔵庫データのダミーデータ作成スクリプト
-- M5Stack連携APIのテスト用

USE supermarket_app;

-- 既存のダミーデータを削除（オプション）
-- DELETE FROM user_refrigerator WHERE user_id = 1 AND product_name LIKE '%(テスト)%';

-- ユーザー1の冷蔵庫にダミーデータを追加
INSERT INTO user_refrigerator (user_id, product_name, quantity, expiry_date, created_at) VALUES
(1, 'りんご(テスト)', 3, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, '牛乳(テスト)', 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, '卵(テスト)', 10, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'トマト(テスト)', 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'キャベツ(テスト)', 1, DATE_ADD(CURDATE(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'にんじん(テスト)', 3, DATE_ADD(CURDATE(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'じゃがいも(テスト)', 5, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, '玉ねぎ(テスト)', 2, DATE_ADD(CURDATE(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'チーズ(テスト)', 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 'ヨーグルト(テスト)', 4, DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'パン(テスト)', 1, CURDATE(), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'バター(テスト)', 1, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'ハム(テスト)', 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'レタス(テスト)', 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'きゅうり(テスト)', 2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));

-- 期限切れのテストデータ（アラート確認用）
INSERT INTO user_refrigerator (user_id, product_name, quantity, expiry_date, created_at) VALUES
(1, '期限切れテスト商品1', 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(1, '期限切れテスト商品2', 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 12 DAY)),
(1, '今日期限のテスト商品', 1, CURDATE(), DATE_SUB(NOW(), INTERVAL 5 DAY));

-- 統計情報を表示
SELECT 
    '冷蔵庫ダミーデータ作成完了' as status,
    COUNT(*) as total_items,
    COUNT(DISTINCT user_id) as user_count,
    COUNT(CASE WHEN expiry_date < CURDATE() THEN 1 END) as expired_items,
    COUNT(CASE WHEN expiry_date = CURDATE() THEN 1 END) as expiring_today,
    COUNT(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 1 END) as expiring_soon
FROM user_refrigerator
WHERE product_name LIKE '%(テスト)%' OR product_name LIKE '%期限%';
