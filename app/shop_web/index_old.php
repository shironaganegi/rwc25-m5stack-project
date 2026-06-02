<?php
// データベース接続
require_once 'includes/database.php';

// データの取得
try {
    $db = getDB();
    
    // 今日の売上情報を取得
    $todaysSales = $db->getTodaysSales();
    $salesTarget = $db->getSalesTarget();
    
    // 在庫アラート情報を取得
    $lowStockCount = $db->getLowStockCount();
    $lowStockProducts = $db->getLowStockProducts(5); // 5個以下の商品
    
    // セール情報を取得
    $activeSalesCount = $db->getActiveSalesCount();
    $expiringSales = $db->getExpiringSales();
    
    // 全商品とカテゴリを取得
    $allProducts = $db->getAllProducts();
    $categories = $db->getAllCategories();
    
    // 売上金額の計算
    $dailySales = $todaysSales['total_sales'] ?? 0;
    $targetAmount = $salesTarget['target_amount'] ?? 1000000; // デフォルト目標
    $achievementRate = $salesTarget['achievement_rate'] ?? 0;
    $previousRate = max(0, $achievementRate - 10); // 前回比（仮）
    
} catch (Exception $e) {
    // エラーが発生した場合は静的データを使用
    $dailySales = 580000;
    $achievementRate = 80;
    $previousRate = 10;
    $lowStockCount = ['count' => 5];
    $activeSalesCount = ['count' => 3];
    $expiringSales = [];
    $allProducts = [];
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スーパーマーケット管理アプリ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .app-container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
            position: relative;
        }

        .header {
            background: #4CAF50;
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }

        .content {
            padding: 20px;
            padding-bottom: 80px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            padding: 15px;
        }

        .card h3 {
            margin-bottom: 10px;
            color: #333;
            font-size: 14px;
        }

        .sales-amount {
            font-size: 24px;
            font-weight: 700;
            color: #4CAF50;
            margin: 5px 0;
        }

        .achievement-rate {
            font-size: 14px;
            color: #666;
        }

        .alert-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .alert-item:last-child {
            border-bottom: none;
        }

        .btn {
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin: 10px 0;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #45a049;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        .form-input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .form-row {
            display: flex;
            gap: 10px;
        }

        .form-row .form-input {
            flex: 1;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 400px;
            background: white;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: around;
            padding: 10px 0;
        }

        .nav-item {
            flex: 1;
            text-align: center;
            padding: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .nav-item:hover {
            background: #f8f9fa;
        }

        .nav-item.active {
            color: #4CAF50;
            background: #f0f8f0;
        }

        .nav-icon {
            font-size: 20px;
            margin-bottom: 2px;
        }

        .nav-label {
            font-size: 12px;
        }

        .hidden {
            display: none;
        }

        .menu-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .hamburger {
            width: 24px;
            height: 18px;
            position: relative;
        }

        .hamburger span {
            display: block;
            height: 2px;
            width: 100%;
            background: white;
            margin: 3px 0;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transition: left 0.3s;
            z-index: 1000;
        }

        .sidebar.open {
            left: 0;
        }

        .sidebar-header {
            background: #4CAF50;
            color: white;
            padding: 20px;
            font-size: 18px;
            font-weight: 600;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.3s;
        }

        .sidebar-item:hover {
            background: #f8f9fa;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }

        .overlay.show {
            display: block;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #4CAF50;
            margin: 5px 0;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .menu-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .menu-card:hover {
            transform: translateY(-2px);
        }

        .menu-icon-large {
            font-size: 40px;
            color: #4CAF50;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- サイドバー -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                設定
            </div>
            <div class="sidebar-menu">
                <div class="sidebar-section">
                    <h4 style="padding: 0 20px; color: #666; font-size: 14px; margin-bottom: 10px;">アカウント設定</h4>
                    <div class="sidebar-item">プロフィール編集</div>
                    <div class="sidebar-item">パスワード変更</div>
                    <div class="sidebar-item">メールアドレス変更</div>
                    <div class="sidebar-item">ログアウト</div>
                </div>
                <div class="sidebar-section">
                    <h4 style="padding: 0 20px; color: #666; font-size: 14px; margin: 20px 0 10px;">店舗情報</h4>
                    <div class="sidebar-item">店舗情報の編集</div>
                    <div class="sidebar-item">登録済み店舗の管理</div>
                </div>
                <div class="sidebar-section">
                    <h4 style="padding: 0 20px; color: #666; font-size: 14px; margin: 20px 0 10px;">アプリ設定</h4>
                    <div class="sidebar-item">通知設定</div>
                    <div class="sidebar-item">データ同期設定</div>
                    <div class="sidebar-item">テーマ設定</div>
                    <div class="sidebar-item">言語設定</div>
                </div>
                <div class="sidebar-section">
                    <h4 style="padding: 0 20px; color: #666; font-size: 14px; margin: 20px 0 10px;">ヘルプとサポート</h4>
                    <div class="sidebar-item">よくある質問 (FAQ)</div>
                    <div class="sidebar-item">お問い合わせ</div>
                    <div class="sidebar-item">利用規約</div>
                    <div class="sidebar-item">プライバシーポリシー</div>
                </div>
                <div class="sidebar-section">
                    <h4 style="padding: 0 20px; color: #666; font-size: 14px; margin: 20px 0 10px;">アプリ情報</h4>
                    <div class="sidebar-item">バージョン情報</div>
                    <div class="sidebar-item">ライセンス情報</div>
                </div>
            </div>
        </div>

        <!-- オーバーレイ -->
        <div class="overlay" id="overlay"></div>

        <!-- メインコンテンツ -->
        <div class="header">
            <div class="menu-icon" onclick="toggleSidebar()">
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            <span id="header-title">ホーム</span>
        </div>

        <div class="content">
            <!-- ホーム画面 -->
            <div id="home-screen">
                <div class="card">
                    <h3>今日の売上概要</h3>
                    <div class="sales-amount" id="daily-sales"><?= number_format($dailySales) ?>円</div>
                    <div class="achievement-rate">目標達成率: <span id="achievement-rate"><?= number_format($achievementRate, 1) ?>% (+<?= number_format($previousRate, 1) ?>%)</span></div>
                </div>

                <div class="card">
                    <h3>在庫アラート</h3>
                    <div id="stock-alerts">在庫が少ない商品: <?= $lowStockCount['count'] ?? 0 ?>種類</div>
                </div>

                <div class="card">
                    <h3>期限が近いセール情報</h3>
                    <div id="expiring-sales">明日までのセール: <?= count($expiringSales) ?>件</div>
                </div>

                <div class="card">
                    <h3>新しいセール情報を追加しませんか？</h3>
                    <button class="btn" onclick="showScreen('sale-input')">セール情報入力へ</button>
                </div>

                <div class="menu-grid">
                    <div class="menu-card" onclick="showScreen('sale-list')">
                        <div class="menu-icon-large">📊</div>
                        <div>セール情報</div>
                    </div>
                    <div class="menu-card" onclick="showScreen('product-list')">
                        <div class="menu-icon-large">📦</div>