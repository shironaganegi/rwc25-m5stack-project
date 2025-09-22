#!/bin/bash

# スーパーマーケットアプリ データベース初期化スクリプト

echo "🗄️ スーパーマーケットアプリ データベース初期化開始..."

# 現在のディレクトリを確認
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ エラー: docker-compose.ymlが見つかりません"
    echo "app/ ディレクトリで実行してください"
    exit 1
fi

# Dockerコンテナを停止して、ボリュームも削除
echo "📁 既存のデータベースをクリア中..."
docker-compose down -v

# Dockerコンテナを起動
echo "🚀 Dockerコンテナを起動中..."
docker-compose up -d

# MySQLが完全に起動するまで待機
echo "⏳ MySQLの起動を待機中（20秒）..."
sleep 20

# データベースの状態を確認
echo "✅ データベースの状態を確認中..."
docker-compose exec mysql-container mysql -u root -pdev -e "SHOW DATABASES;" 2>/dev/null

echo "📊 テーブル一覧を確認中..."
docker-compose exec mysql-container mysql -u root -pdev supermarket_app -e "SHOW TABLES;" 2>/dev/null

echo "👤 ユーザーデータを確認中..."
docker-compose exec mysql-container mysql -u root -pdev supermarket_app -e "SELECT id, username, email, store_name FROM users;" 2>/dev/null

echo "🏷️ カテゴリデータを確認中..."
docker-compose exec mysql-container mysql -u root -pdev supermarket_app -e "SELECT * FROM categories;" 2>/dev/null

# 権限を設定
echo "🔐 ユーザー権限を設定中..."
docker-compose exec mysql-container mysql -u root -pdev -e "GRANT ALL PRIVILEGES ON supermarket_app.* TO 'dev'@'%'; FLUSH PRIVILEGES;" 2>/dev/null

echo ""
echo "🎉 データベース初期化完了！"
echo ""
echo "📋 動作確認用URL:"
echo "   データベーステスト: http://localhost:8000/test_db.php"
echo "   マップ機能:         http://localhost:8000/map/map.php"
echo ""
echo "💻 直接MySQL接続:"
echo "   ホスト: localhost"
echo "   ポート: 53306"
echo "   データベース: supermarket_app"
echo "   ユーザー: dev"
echo "   パスワード: dev"
echo ""
echo "🐳 Docker管理コマンド:"
echo "   ログ確認: docker-compose logs mysql-container"
echo "   コンテナ状態: docker-compose ps"
echo "   停止: docker-compose down"