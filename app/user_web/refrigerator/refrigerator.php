<?php
// 冷蔵庫機能は廃止し、最安値検索ページとしてリニューアル
require_once __DIR__ . '/../includes/database.php';

$db = getDB();
$searchKeyword = $_GET['search'] ?? null;
$searchResults = [];

// 検索ワードがあれば検索実行（なければデフォルトで安い順に表示）
if ($searchKeyword !== null) {
    $searchResults = $db->searchCheapestSales($searchKeyword);
} else {
    $searchResults = $db->searchCheapestSales(''); 
}

// 画像ヘルパー
function getProductImage($name) {
    if (strpos($name, '卵') !== false) return '/assets/image/eggs.png';
    if (strpos($name, '牛乳') !== false) return '/assets/image/milk.png';
    if (strpos($name, 'トマト') !== false) return '/assets/image/tomato.png';
    return '/assets/image/tirasi.png';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>最安値検索</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/refrigerator/refrigerator.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <div class="search-page-container">
            <h2 class="page-title">スーパー横断 最安値検索</h2>
            <p class="page-description">登録されている全スーパーのセール情報から、一番安い商品を検索できます。</p>

            <form method="GET" class="main-search-form">
                <input type="text" name="search" class="search-input" placeholder="商品名を入力（例：牛乳、キャベツ）" value="<?php echo htmlspecialchars($searchKeyword ?? ''); ?>">
                <button type="submit" class="search-btn">最安値を検索</button>
            </form>

            <div class="search-results-area">
                <?php if ($searchKeyword !== null && empty($searchResults)): ?>
                    <div class="no-result">
                        <p>「<?php echo htmlspecialchars($searchKeyword); ?>」のセール商品は見つかりませんでした。</p>
                    </div>
                <?php else: ?>
                    <div class="result-count">
                        <?php echo count($searchResults); ?>件のセール商品が見つかりました（安い順）
                    </div>
                    <div class="result-cards">
                        <?php foreach ($searchResults as $item): ?>
                            <div class="result-card">
                                <div class="card-rank-badge">
                                    <span><?php echo number_format($item['sale_price']); ?>円</span>
                                </div>
                                <div class="card-img">
                                    <img src="<?php echo getProductImage($item['product_name']); ?>" alt="">
                                </div>
                                <div class="card-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                    <p class="store-name">📍 <?php echo htmlspecialchars($item['store_name']); ?></p>
                                    <div class="price-row">
                                        <span class="sale-price"><?php echo number_format($item['sale_price']); ?>円</span>
                                        <span class="regular-price"><del><?php echo number_format($item['regular_price']); ?></del></span>
                                    </div>
                                    <p class="limit-date">期間: <?php echo date('m/d', strtotime($item['end_date'])); ?>まで</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>
</body>
</html>