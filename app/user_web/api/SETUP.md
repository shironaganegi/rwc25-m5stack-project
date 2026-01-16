# M5Stack連携API セットアップガイド

## 概要

M5StackデバイスとWebアプリを連携するためのAPIが実装されました。
このガイドでは、セットアップ手順を説明します。

## 1. データベースセットアップ

### 混雑率データテーブルの作成

```bash
# Dockerコンテナ内で実行
docker exec -it mysql mysql -u dev -pdev supermarket_app < /docker-entrypoint-initdb.d/create_congestion_table.sql
```

または、MySQLに直接接続して実行:

```sql
CREATE TABLE IF NOT EXISTS congestion_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    congestion_level INT NOT NULL COMMENT '混雑率（0-100の整数）',
    measured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    device_id VARCHAR(100) COMMENT 'M5StackデバイスID',
    notes TEXT COMMENT '備考',
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
    INDEX idx_store_measured (store_id, measured_at),
    INDEX idx_measured_at (measured_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 2. APIエンドポイント

### 冷蔵庫商品登録API

- **URL**: `http://[サーバーIP]/api/refrigerator_add.php`
- **メソッド**: POST
- **Content-Type**: application/json

**リクエスト例**:
```json
{
  "user_id": 1,
  "product_name": "りんご",
  "quantity": 1,
  "expiry_date": "2026-01-20"
}
```

### 混雑率送信API

- **URL**: `http://[サーバーIP]/api/congestion_send.php`
- **メソッド**: POST
- **Content-Type**: application/json

**リクエスト例**:
```json
{
  "store_id": 1,
  "congestion_level": 75,
  "device_id": "M5Stack_001"
}
```

## 3. M5Stack側の設定

### ユーザー側M5Stack

1. `app/user_m5stack/api_client.py`の`SERVER_IP`を環境に合わせて変更
2. `app/user_m5stack/main_with_api.py`を使用（API連携版）

**設定例**:
```python
# api_client.py
SERVER_IP = "172.20.10.6"  # WebアプリのサーバーIP
DEFAULT_USER_ID = 1  # デフォルトユーザーID
```

### 店舗側M5Stack

1. `app/shop_m5stack/api_client.py`の`SERVER_IP`を環境に合わせて変更
2. `app/shop_m5stack/congestion_sender.py`を使用

**設定例**:
```python
# api_client.py
SERVER_IP = "172.20.10.6"  # WebアプリのサーバーIP

# congestion_sender.py
WIFI_SSID = "your_wifi_ssid"
WIFI_PASS = "your_wifi_password"
STORE_ID = 1  # 店舗ID
DEVICE_ID = "M5Stack_Shop_001"
```

## 4. 動作確認

### cURLでのテスト

**冷蔵庫商品登録**:
```bash
curl -X POST http://localhost/api/refrigerator_add.php \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "product_name": "テスト商品", "quantity": 1}'
```

**混雑率送信**:
```bash
curl -X POST http://localhost/api/congestion_send.php \
  -H "Content-Type: application/json" \
  -d '{"store_id": 1, "congestion_level": 50, "device_id": "test_device"}'
```

### データベース確認

```sql
-- 冷蔵庫データの確認
SELECT * FROM user_refrigerator ORDER BY created_at DESC LIMIT 10;

-- 混雑率データの確認
SELECT * FROM congestion_data ORDER BY measured_at DESC LIMIT 10;
```

## 5. トラブルシューティング

### エラー: "Database table not found"

`congestion_data`テーブルが作成されていません。上記のセットアップ手順を実行してください。

### エラー: "Connection refused"

- M5Stack側の`SERVER_IP`が正しいか確認
- Webアプリのサーバーが起動しているか確認
- ファイアウォール設定を確認

### APIが応答しない

- PHPのエラーログを確認
- データベース接続を確認
- CORS設定を確認（必要に応じて`Access-Control-Allow-Origin`を調整）

## 6. 次のステップ

- [ ] 認証機能の実装（現在はuser_id固定）
- [ ] WiFi設定機能の実装
- [ ] エラーハンドリングの強化
- [ ] 実際のセンサー連携（混雑率測定）
