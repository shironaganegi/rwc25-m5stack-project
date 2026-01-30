-- 混雑率データのダミーデータ作成スクリプト
-- M5Stack連携APIのテスト用

USE supermarket_app;

-- 既存のダミーデータを削除（オプション）
INSERT INTO user_favorite_stores (user_id, store_id) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5);