# データベース管理ツール

データベース `supermarket_app` の全テーブルとデータを閲覧するための管理ツールです。

## 機能

- 全テーブル一覧の表示
- 各テーブルのレコード数表示
- テーブルデータの閲覧
- ページネーション対応（50件/ページ）

## アクセス方法

```
http://localhost:9000/
```

## データベース接続情報

- **ホスト**: mysql (Dockerコンテナ名)
- **ポート**: 3306
- **データベース**: supermarket_app
- **ユーザー**: dev
- **パスワード**: dev

## 起動方法

```bash
# app/ ディレクトリで実行
docker-compose up -d admin

# または、全体を再起動
docker-compose up -d
```

## テーブル一覧

このツールで閲覧可能なテーブル：

- users - ユーザー（店舗スタッフ）
- categories - カテゴリ
- products - 商品
- stores - 店舗マスタ
- sales - セール情報
- sale_stores - セール対象店舗（中間テーブル）
- sales_data - 売上データ
- sales_targets - 目標設定
- notification_settings - 通知設定
- user_favorite_stores - ユーザーお気に入り店舗
- user_favorite_products - ユーザーお気に入り商品
- user_refrigerator - ユーザー冷蔵庫管理
- recipes - レシピマスタ
- recipe_ingredients - レシピ材料
