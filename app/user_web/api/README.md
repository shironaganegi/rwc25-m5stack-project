# M5Stack連携API

このディレクトリには、M5StackデバイスとWebアプリを連携するためのRESTful APIエンドポイントが含まれています。

## APIエンドポイント

### 1. 冷蔵庫商品登録API

**エンドポイント**: `POST /api/refrigerator_add.php`

**説明**: QRコードで読み取った商品を冷蔵庫リストに追加します。

**リクエスト例**:
```json
{
  "user_id": 1,
  "product_name": "りんご",
  "quantity": 1,
  "expiry_date": "2026-01-20"
}
```

**レスポンス例（成功）**:
```json
{
  "success": true,
  "message": "商品を追加しました",
  "id": 123
}
```

**レスポンス例（エラー）**:
```json
{
  "success": false,
  "message": "product_name is required"
}
```

**パラメータ**:
- `user_id` (必須): ユーザーID（整数）
- `product_name` (必須): 商品名（文字列）
- `quantity` (オプション): 数量（整数、デフォルト: 1）
- `expiry_date` (オプション): 消費期限（YYYY-MM-DD形式）

---

### 2. 混雑率送信API

**エンドポイント**: `POST /api/congestion_send.php`

**説明**: 店舗の混雑率データを送信します。

**リクエスト例**:
```json
{
  "store_id": 1,
  "congestion_level": 75,
  "device_id": "M5Stack_001"
}
```

**レスポンス例（成功）**:
```json
{
  "success": true,
  "message": "混雑率データを保存しました",
  "id": 456
}
```

**レスポンス例（エラー）**:
```json
{
  "success": false,
  "message": "congestion_level must be between 0 and 100"
}
```

**パラメータ**:
- `store_id` (必須): 店舗ID（整数）
- `congestion_level` (必須): 混雑率（0-100の整数）
- `device_id` (オプション): デバイスID（文字列）
- `notes` (オプション): 備考（文字列）

---

## データベースセットアップ

混雑率データを保存するには、`congestion_data`テーブルを作成する必要があります。

```bash
# Dockerコンテナ内で実行
docker exec -it mysql mysql -u dev -pdev supermarket_app < /docker-entrypoint-initdb.d/create_congestion_table.sql
```

または、手動でSQLを実行:

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

---

## M5Stack側の実装

### ユーザー側M5Stack

`app/user_m5stack/api_client.py`を使用して、QRコード読み取り後に商品を登録:

```python
from api_client import send_refrigerator_item

result = send_refrigerator_item(
    product_name="りんご",
    quantity=1,
    user_id=1
)

if result.get('success'):
    print("登録成功!")
```

### 店舗側M5Stack

`app/shop_m5stack/api_client.py`を使用して、混雑率を送信:

```python
from api_client import send_congestion_data

result = send_congestion_data(
    store_id=1,
    congestion_level=75,
    device_id="M5Stack_001"
)

if result.get('success'):
    print("送信成功!")
```

---

## テスト方法

### cURLでのテスト

**冷蔵庫商品登録API**:
```bash
curl -X POST http://localhost/api/refrigerator_add.php \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "product_name": "テスト商品",
    "quantity": 1
  }'
```

**混雑率送信API**:
```bash
curl -X POST http://localhost/api/congestion_send.php \
  -H "Content-Type: application/json" \
  -d '{
    "store_id": 1,
    "congestion_level": 50,
    "device_id": "test_device"
  }'
```

---

## 注意事項

1. **認証機能**: 現在は`user_id`を直接指定していますが、認証機能実装後はセッションやトークンを使用する必要があります。

2. **CORS設定**: 現在は`Access-Control-Allow-Origin: *`で全許可していますが、本番環境では適切に制限してください。

3. **エラーハンドリング**: M5Stack側ではネットワークエラーやタイムアウトに対する適切な処理を実装してください。

4. **サーバーIP設定**: M5Stack側の`api_client.py`で`SERVER_IP`を環境に応じて変更してください。
