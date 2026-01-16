# ダミーデータ生成ガイド

M5Stack連携APIのテスト用に、混雑率データと冷蔵庫データのダミーデータを生成できます。

## 混雑率データのダミーデータ

### SQLスクリプトで生成

```bash
# Dockerコンテナ内で実行
cd app
Get-Content server/docker/mysql/init/create_dummy_congestion_data.sql | docker exec -i mysql mysql -u dev -pdev supermarket_app
```

または、MySQLに直接接続:

```bash
docker exec -i mysql mysql -u dev -pdev supermarket_app < server/docker/mysql/init/create_dummy_congestion_data.sql
```

### PHPスクリプトで生成

ブラウザでアクセス:
```
http://localhost:8000/api/generate_dummy_data.php
```

または、コマンドライン:
```bash
docker exec -it user_web php /var/www/html/api/generate_dummy_data.php
```

### 生成されるデータ

- **店舗数**: 5店舗
- **データ数**: 各店舗24時間分（1時間ごと）= 合計120件
- **期間**: 過去24時間から現在まで
- **混雑率**: 時間帯に応じたリアルな値（20-95%）

### データの確認

```sql
-- 全データを確認
SELECT 
    s.name as store_name,
    cd.congestion_level,
    cd.measured_at,
    cd.device_id
FROM congestion_data cd
JOIN stores s ON cd.store_id = s.id
WHERE cd.device_id LIKE 'M5Stack_Dummy%'
ORDER BY cd.measured_at DESC;

-- 店舗別の統計
SELECT 
    s.name as store_name,
    COUNT(*) as count,
    AVG(cd.congestion_level) as avg_congestion,
    MIN(cd.congestion_level) as min_level,
    MAX(cd.congestion_level) as max_level
FROM congestion_data cd
JOIN stores s ON cd.store_id = s.id
WHERE cd.device_id LIKE 'M5Stack_Dummy%'
GROUP BY s.id, s.name
ORDER BY s.id;

-- 最新の混雑率（各店舗）
SELECT 
    s.name as store_name,
    cd.congestion_level,
    cd.measured_at
FROM congestion_data cd
JOIN stores s ON cd.store_id = s.id
WHERE cd.device_id LIKE 'M5Stack_Dummy%'
AND cd.measured_at = (
    SELECT MAX(measured_at) 
    FROM congestion_data 
    WHERE store_id = cd.store_id 
    AND device_id LIKE 'M5Stack_Dummy%'
)
ORDER BY s.id;
```

---

## 冷蔵庫データのダミーデータ

### SQLスクリプトで生成

```bash
# Dockerコンテナ内で実行
cd app
Get-Content server/docker/mysql/init/create_dummy_refrigerator_data.sql | docker exec -i mysql mysql -u dev -pdev supermarket_app
```

### 生成されるデータ

- **ユーザーID**: 1
- **商品数**: 18件
  - 通常商品: 15件
  - 期限切れ商品: 2件
  - 今日期限の商品: 1件

### データの確認

```sql
-- 全データを確認
SELECT 
    product_name,
    quantity,
    expiry_date,
    CASE 
        WHEN expiry_date < CURDATE() THEN '期限切れ'
        WHEN expiry_date = CURDATE() THEN '今日期限'
        WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN '期限間近'
        ELSE '正常'
    END as status
FROM user_refrigerator
WHERE user_id = 1 
AND (product_name LIKE '%(テスト)%' OR product_name LIKE '%期限%')
ORDER BY expiry_date ASC;

-- 期限切れ・期限間近の商品
SELECT 
    product_name,
    quantity,
    expiry_date,
    DATEDIFF(expiry_date, CURDATE()) as days_until_expiry
FROM user_refrigerator
WHERE user_id = 1
AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
AND (product_name LIKE '%(テスト)%' OR product_name LIKE '%期限%')
ORDER BY expiry_date ASC;
```

---

## ダミーデータの削除

### 混雑率データを削除

```sql
DELETE FROM congestion_data WHERE device_id LIKE 'M5Stack_Dummy%';
```

### 冷蔵庫データを削除

```sql
DELETE FROM user_refrigerator 
WHERE user_id = 1 
AND (product_name LIKE '%(テスト)%' OR product_name LIKE '%期限%');
```

---

## 注意事項

- ダミーデータはテスト用です。本番環境では使用しないでください。
- ダミーデータを削除する際は、WHERE条件を確認してから実行してください。
- デバイスIDは`M5Stack_Dummy_XXX`形式で識別できます。
