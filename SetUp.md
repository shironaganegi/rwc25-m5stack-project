# スーパーマーケットアプリ セットアップガイド

## 📋 目次

1. [必要な環境](#必要な環境)
2. [初回セットアップ](#初回セットアップ)
3. [アプリケーションの起動](#アプリケーションの起動)
4. [動作確認](#動作確認)
5. [トラブルシューティング](#トラブルシューティング)
6. [開発者向け情報](#開発者向け情報)

---

## 🛠️ 必要な環境

以下のソフトウェアがインストールされている必要があります：

- **Docker Desktop**
  - バージョン: 20.10以上推奨
  - [ダウンロード](https://www.docker.com/products/docker-desktop)
- **Git**
  - バージョン: 2.0以上
- **ターミナル/コマンドプロンプト**
  - macOS/Linux: ターミナル
  - Windows: PowerShellまたはコマンドプロンプト

---

## 🚀 初回セットアップ

### 1. リポジトリのクローン

プロジェクトを保存したいディレクトリに移動し、以下のコマンドを実行します：

```bash
# Gitリポジトリの初期化
git init

# リポジトリのクローン
git clone https://github.com/KDIX-RWCProjects/RWC25-group07.git

# プロジェクトディレクトリに移動
cd RWC25-group07
```

### 2. 環境変数の設定（オプション）

デフォルト設定で動作しますが、カスタマイズする場合は以下を参照してください：

**デフォルト値:**

- データベース名: `supermarket_app`
- ユーザー名: `dev`
- パスワード: `dev`
- ROOTパスワード: `dev`
- ポート: `3306`

環境変数をカスタマイズする場合は、`app/`ディレクトリに`.env`ファイルを作成し、以下の変数を設定してください：

```bash
DOCKER_DB_ROOT_PASS=dev
DOCKER_DB_USERNAME=dev
DOCKER_DB_PASSWORD=dev
DOCKER_DB_DATABASE=supermarket_app
DOCKER_DB_PORT=3306
```

---

## 🎯 アプリケーションの起動

### 方法1: Docker Composeで起動（推奨）

```bash
# appディレクトリに移動
cd app

# コンテナをバックグラウンドで起動
docker compose up -d
```

起動には30秒ほどかかります。MySQLの初期化が完了するまでお待ちください。

### 方法2: 初期化スクリプトを使用

データベースを完全にリセットして起動する場合：

```bash
# appディレクトリに移動
cd app

# スクリプトに実行権限を付与（初回のみ）
chmod +x init_database.sh

# 初期化スクリプトを実行
./init_database.sh
```

このスクリプトは以下を実行します：

- 既存のコンテナとボリュームを削除
- Dockerコンテナを起動
- データベースの初期化を確認
- テストデータの投入確認

---

## ✅ 動作確認

### 起動中のコンテナを確認

```bash
docker ps
```

以下の3つのコンテナが起動していることを確認してください：

- `mysql` - データベースサーバー
- `shop_web` - 店舗管理用Webアプリケーション
- `user_web` - ユーザー向けWebアプリケーション

### Webアプリケーションへアクセス

#### 1. ユーザー向けアプリケーション (user_web)

```
http://localhost:8000
```

**主な機能:**

- マップ機能: <http://localhost:8000/map/map.php>
- レシピ検索: <http://localhost:8000/recipe/recipe.php>
- お気に入り: <http://localhost:8000/favorites/favorites.php>
- 冷蔵庫管理: <http://localhost:8000/refrigerator/refrigerator.php>
- セール情報: <http://localhost:8000/sale/sale.php>

#### 2. 店舗管理アプリケーション (shop_web)

```
http://localhost:3000
```

**主な機能:**

- ホーム: <http://localhost:3000/home/home.php>
- セール入力: <http://localhost:3000/saleInput/saleInput.php>
- 分析: <http://localhost:3000/analys/analys.php>
- 設定: <http://localhost:3000/setting/setting.php>

#### 3. データベーステスト

```
http://localhost:8000/test_db.php
```

データベース接続が正常かどうか確認できます。

### MySQLへの直接接続

```bash
# MySQLコンテナに接続
docker exec -it mysql mysql -u dev -pdev supermarket_app

# またはrootユーザーで接続
docker exec -it mysql mysql -u root -pdev
```

**基本的なSQLコマンド:**

```sql
-- テーブル一覧を表示
SHOW TABLES;

-- ユーザーデータを確認
SELECT id, username, email, store_name FROM users;

-- カテゴリデータを確認
SELECT * FROM categories;

-- セール情報を確認
SELECT * FROM sales;
```

---

## 🔧 トラブルシューティング

### コンテナが起動しない場合

```bash
# コンテナのログを確認
docker compose logs

# 特定のコンテナのログを確認
docker compose logs mysql-container
docker compose logs shop_web
docker compose logs user_web
```

### ポートが既に使用されている場合

エラーメッセージに「port is already allocated」が表示される場合：

1. 使用中のポートを確認

   ```bash
   # macOS/Linux
   lsof -i :8000
   lsof -i :3000
   lsof -i :3306
   
   # Windows
   netstat -ano | findstr :8000
   ```

2. `docker-compose.yml`のポート設定を変更

   ```yaml
   ports:
     - "8001:80"  # 8000から8001に変更
   ```

### データベースが初期化されない場合

```bash
# 完全にクリーンアップ
docker compose down -v

# ボリュームを確認
docker volume ls

# 残っているボリュームを削除
docker volume rm app_laravel-railway-mysql-data

# 再起動
docker compose up -d
```

### コンテナを停止・再起動する

```bash
# 停止
docker compose down

# 停止してボリュームも削除（データベースをリセット）
docker compose down -v

# 再起動
docker compose restart

# 特定のコンテナだけ再起動
docker compose restart mysql-container
```

---

## 💻 開発者向け情報

### システム構成

```
┌─────────────────────────────────────────────────────┐
│                   Docker Network                     │
│                   (app-network)                      │
│                                                       │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────┐ │
│  │   shop_web   │  │   user_web   │  │   MySQL   │ │
│  │   (port:     │  │   (port:     │  │   (port:  │ │
│  │    3000)     │  │    8000)     │  │    3306)  │ │
│  │              │  │              │  │           │ │
│  │  PHP/Apache  │  │  PHP/Apache  │  │  Database │ │
│  └──────────────┘  └──────────────┘  └───────────┘ │
└─────────────────────────────────────────────────────┘
```

### データベース構造

**主要テーブル:**

- `users` - ユーザー（店舗スタッフ）情報
- `categories` - 商品カテゴリ
- `products` - 商品情報
- `sales` - セール情報
- `stores` - 店舗マスタ
- `sales_data` - 売上実績データ
- `sales_targets` - 売上目標設定
- `notification_settings` - 通知設定

**初期データ:**

- 管理ユーザー: `admin` / `admin@supermarket.com`
- 5つの基本カテゴリ（食品、飲料、日用品、冷凍食品、お菓子）
- サンプル商品データ
- 日次売上目標: ¥580,000

### マップ機能について

- **OpenStreetMap**: APIキー不要の無料地図サービス
- **Leaflet.js**: 軽量な地図描画ライブラリ
- **機能**: 店舗検索、セール情報表示、混雑率フィルタリング

### ディレクトリ構造

```
app/
├── docker-compose.yml      # Docker構成ファイル
├── init_database.sh        # データベース初期化スクリプト
├── server/                 # バックエンド
│   └── docker/mysql/       
│       ├── Dockerfile      
│       └── init/init.sql   # データベース初期化SQL
├── shop_web/               # 店舗管理アプリ
│   ├── Dockerfile
│   ├── index.php
│   └── (各機能ディレクトリ)
├── user_web/               # ユーザー向けアプリ
│   ├── Dockerfile
│   ├── index.php
│   └── (各機能ディレクトリ)
├── shop_m5stack/           # 店舗用M5Stack
│   └── qr_reader.py
└── user_m5stack/           # ユーザー用M5Stack
    └── m5stack.py
```

### 開発時のGitワークフロー

```bash
# 変更をステージング
git add .

# コミット（変更内容を簡潔に記述）
git commit -m "コミットメッセージ"

# プッシュ
git push
```

**開発ルール:**

- 1作業1ブランチ
- マージする時はレビューをもらう
- コミットメッセージは日本語で簡潔に

### よく使うDockerコマンド

```bash
# ログをリアルタイムで表示
docker compose logs -f

# コンテナ内でコマンド実行
docker exec -it shop_web bash
docker exec -it user_web bash

# MySQLにログイン
docker exec -it mysql mysql -u dev -pdev supermarket_app

# イメージの再ビルド
docker compose build

# イメージを再ビルドして起動
docker compose up -d --build

# 使用していないイメージ・コンテナ・ボリュームを削除
docker system prune -a
```

---

## 📝 更新履歴

- 2025-12-22: 初版作成

---

## 🆘 サポート

問題が解決しない場合は、以下を確認してください：

1. Dockerが正しくインストールされているか
2. Docker Desktopが起動しているか
3. ポートが他のアプリケーションで使用されていないか
4. ディスク容量が十分にあるか

それでも解決しない場合は、開発チームに連絡してください。
