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

// セール情報追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    try {
        $product_id = $_POST['product_id'];
        $sale_price = $_POST['sale_price'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $description = $_POST['description'] ?? '';
        
        $saleId = $db->addSale($product_id, $sale_price, $start_date, $end_date, $description);
        
        if ($saleId) {
            $success_message = "セール情報を追加しました。";
            // データを再取得
            $activeSalesCount = $db->getActiveSalesCount();
            $expiringSales = $db->getExpiringSales();
        }
    } catch (Exception $e) {
        $error_message = "セール情報の追加に失敗しました: " . $e->getMessage();
    }
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

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .product-details {
            font-size: 14px;
            color: #666;
        }

        .stock-warning {
            color: #e74c3c;
            font-weight: 600;
        }

        .stock-ok {
            color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- サイドバー -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">設定</div>
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
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <!-- ホーム画面 -->
            <div id="home-screen">
                <div class="card">
                    <h3>今日の売上概要</h3>
                    <div class="sales-amount"><?= number_format($dailySales) ?>円</div>
                    <div class="achievement-rate">目標達成率: <?= number_format($achievementRate, 1) ?>% (+<?= number_format($previousRate, 1) ?>%)</div>
                </div>

                <div class="card">
                    <h3>在庫アラート</h3>
                    <div>在庫が少ない商品: <?= $lowStockCount['count'] ?? 0 ?>種類</div>
                    <?php if (!empty($lowStockProducts)): ?>
                        <div style="margin-top: 10px; max-height: 150px; overflow-y: auto;">
                            <?php foreach (array_slice($lowStockProducts, 0, 3) as $product): ?>
                                <div class="product-item">
                                    <div class="product-info">
                                        <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                        <div class="product-details">
                                            <span class="stock-warning">残り<?= $product['stock_quantity'] ?>個</span>
                                            | <?= htmlspecialchars($product['category_name'] ?? 'カテゴリなし') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3>期限が近いセール情報</h3>
                    <div>明日までのセール: <?= count($expiringSales) ?>件</div>
                    <?php if (!empty($expiringSales)): ?>
                        <div style="margin-top: 10px; max-height: 150px; overflow-y: auto;">
                            <?php foreach ($expiringSales as $sale): ?>
                                <div class="product-item">
                                    <div class="product-info">
                                        <div class="product-name"><?= htmlspecialchars($sale['product_name']) ?></div>
                                        <div class="product-details">
                                            セール価格: ¥<?= number_format($sale['sale_price']) ?> 
                                            | 終了: <?= date('m/d', strtotime($sale['end_date'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
                        <div>商品管理</div>
                    </div>
                    <div class="menu-card" onclick="showScreen('analytics')">
                        <div class="menu-icon-large">📈</div>
                        <div>分析</div>
                    </div>
                    <div class="menu-card" onclick="showScreen('stock-alert')">
                        <div class="menu-icon-large">⚠️</div>
                        <div>在庫アラート</div>
                    </div>
                </div>
            </div>

            <!-- セール情報入力画面 -->
            <div id="sale-input-screen" class="hidden">
                <form method="POST">
                    <input type="hidden" name="add_sale" value="1">
                    
                    <div class="form-group">
                        <label class="form-label">商品選択</label>
                        <select class="form-input" name="product_id" required>
                            <option value="">商品を選択してください</option>
                            <?php foreach ($allProducts as $product): ?>
                                <option value="<?= $product['id'] ?>">
                                    <?= htmlspecialchars($product['name']) ?> (¥<?= number_format($product['regular_price']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">セール価格</label>
                        <input type="number" class="form-input" name="sale_price" placeholder="セール価格を入力" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">開始日</label>
                            <input type="date" class="form-input" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">終了日</label>
                            <input type="date" class="form-input" name="end_date" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">説明（任意）</label>
                        <textarea class="form-input" name="description" rows="3" placeholder="セール情報の詳細を入力"></textarea>
                    </div>

                    <button type="submit" class="btn">セール情報を追加</button>
                    <button type="button" class="btn btn-secondary" onclick="showScreen('home')">キャンセル</button>
                </form>
            </div>

            <!-- 商品一覧画面 -->
            <div id="product-list-screen" class="hidden">
                <div class="card">
                    <h3>商品一覧</h3>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($allProducts as $product): ?>
                            <div class="product-item">
                                <div class="product-info">
                                    <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                    <div class="product-details">
                                        価格: ¥<?= number_format($product['price']) ?> | 
                                        在庫: <span class="<?= $product['stock_quantity'] <= 5 ? 'stock-warning' : 'stock-ok' ?>">
                                            <?= $product['stock_quantity'] ?>個
                                        </span> | 
                                        <?= htmlspecialchars($product['category_name'] ?? 'カテゴリなし') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- アクティブなセール一覧画面 -->
            <div id="sale-list-screen" class="hidden">
                <div class="card">
                    <h3>現在のセール情報</h3>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php 
                        $activeSales = $db->getActiveSales();
                        if (!empty($activeSales)): 
                        ?>
                            <?php foreach ($activeSales as $sale): ?>
                                <div class="product-item">
                                    <div class="product-info">
                                        <div class="product-name"><?= htmlspecialchars($sale['product_name']) ?></div>
                                        <div class="product-details">
                                            セール価格: ¥<?= number_format($sale['sale_price']) ?> | 
                                            <?= date('m/d', strtotime($sale['start_date'])) ?> 〜 <?= date('m/d', strtotime($sale['end_date'])) ?>
                                            <?php if ($sale['description']): ?>
                                                <br><?= htmlspecialchars($sale['description']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>現在アクティブなセール情報はありません。</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 在庫アラート画面 -->
            <div id="stock-alert-screen" class="hidden">
                <div class="card">
                    <h3>在庫アラート</h3>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php if (!empty($lowStockProducts)): ?>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <div class="product-item">
                                    <div class="product-info">
                                        <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                        <div class="product-details">
                                            <span class="stock-warning">残り<?= $product['stock_quantity'] ?>個</span> | 
                                            価格: ¥<?= number_format($product['regular_price']) ?> | 
                                            <?= htmlspecialchars($product['category_name'] ?? 'カテゴリなし') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>在庫アラートはありません。</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 分析画面 -->
            <div id="analytics-screen" class="hidden">
                <div class="card">
                    <h3>売上分析</h3>
                    <div class="product-details">
                        <p>今日の売上: ¥<?= number_format($dailySales) ?></p>
                        <p>目標達成率: <?= number_format($achievementRate, 1) ?>%</p>
                        <p>取引件数: <?= $todaysSales['transaction_count'] ?? 0 ?>件</p>
                        <p>登録商品数: <?= count($allProducts) ?>種類</p>
                        <p>アクティブセール: <?= $activeSalesCount['count'] ?? 0 ?>件</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ボトムナビゲーション -->
        <div class="bottom-nav">
            <div class="nav-item active" onclick="showScreen('home')">
                <div class="nav-icon">🏠</div>
                <div class="nav-label">ホーム</div>
            </div>
            <div class="nav-item" onclick="showScreen('sale-list')">
                <div class="nav-icon">📊</div>
                <div class="nav-label">セール</div>
            </div>
            <div class="nav-item" onclick="showScreen('product-list')">
                <div class="nav-icon">📦</div>
                <div class="nav-label">商品</div>
            </div>
            <div class="nav-item" onclick="showScreen('analytics')">
                <div class="nav-icon">📈</div>
                <div class="nav-label">分析</div>
            </div>
        </div>
    </div>

    <script>
        // 画面切り替え機能
        function showScreen(screenName) {
            // すべての画面を非表示
            const screens = document.querySelectorAll('[id$="-screen"]');
            screens.forEach(screen => screen.classList.add('hidden'));

            // 指定された画面を表示
            const targetScreen = document.getElementById(screenName + '-screen');
            if (targetScreen) {
                targetScreen.classList.remove('hidden');
            }

            // ナビゲーションの状態更新
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => item.classList.remove('active'));

            // ヘッダータイトルの更新
            const headerTitle = document.getElementById('header-title');
            const titles = {
                'home': 'ホーム',
                'sale-input': 'セール情報入力',
                'sale-list': 'セール情報',
                'product-list': '商品管理',
                'stock-alert': '在庫アラート',
                'analytics': '分析'
            };

            headerTitle.textContent = titles[screenName] || 'ホーム';

            // サイドバーを閉じる
            closeSidebar();
        }

        // サイドバー機能
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }

        // オーバーレイクリックでサイドバーを閉じる
        document.getElementById('overlay').addEventListener('click', closeSidebar);

        // 初期化
        document.addEventListener('DOMContentLoaded', function() {
            showScreen('home');
            
            // 開始日と終了日のデフォルト値を設定
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');
            
            if (startDateInput) {
                startDateInput.value = today.toISOString().split('T')[0];
            }
            if (endDateInput) {
                endDateInput.value = tomorrow.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>