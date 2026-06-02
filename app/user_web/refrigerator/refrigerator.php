<?php
// 最安値比較検索（旧冷蔵庫機能ページ）
require_once __DIR__ . '/../includes/database.php';

$db = getDB();
$searchQuery = $_GET['search'] ?? '';
$searchResults = [];
$isComparisonMode = false;

// 検索ワード処理
if ($searchQuery !== '') {
    // 全角スペースを半角に変換して分割
    $keywords = preg_split('/[\s]+/', mb_convert_kana($searchQuery, 's'), -1, PREG_SPLIT_NO_EMPTY);
    
    if (count($keywords) > 0) {
        $isComparisonMode = true;
        // 複数商品比較ロジックを実行
        $searchResults = $db->searchProductPriceComparison($keywords);
    }
} else {
    // デフォルト：何かしらのセール品を安い順に出す（比較モードオフ）
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
    <title>最安値比較</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/refrigerator/refrigerator.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <div class="search-page-container">
            <p class="page-description">
                欲しいものをスペース区切りで入力すると、合計金額が一番安いスーパーを探せます。<br>
                （例：「牛乳 卵 キャベツ」）
            </p>

            <form method="GET" class="main-search-form">
                <input type="text" name="search" class="search-input" placeholder="商品名を入力（例：牛乳 卵）" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn">比較する</button>
            </form>

            <div class="search-results-area">
                <?php if ($searchQuery !== '' && empty($searchResults)): ?>
                    <div class="no-result">
                        <p>条件に合う商品は見つかりませんでした。</p>
                    </div>
                <?php elseif ($isComparisonMode): ?>
                    <div class="result-count">
                        <?php echo count($searchResults); ?>店舗が見つかりました（安い順）
                    </div>
                    
                    <div class="comparison-list">
                        <?php foreach ($searchResults as $rank => $store): ?>
                            <div class="comparison-card">
                                <div class="rank-badge">No.<?php echo $rank + 1; ?></div>
                                <div class="store-header">
                                    <h3 class="store-name"><?php echo htmlspecialchars($store['store_name']); ?></h3>
                                    <div class="total-price-area">
                                        <span class="match-info"><?php echo $store['match_count']; ?>点揃って</span>
                                        <span class="total-price"><?php echo number_format($store['total_price']); ?>円</span>
                                    </div>
                                </div>
                                <div class="item-list-row">
                                    <?php foreach ($store['items'] as $item): ?>
                                        <div class="mini-item <?php echo !empty($item['is_missing']) ? 'missing' : ''; ?>">
                                            <img src="<?php echo getProductImage($item['name']); ?>" alt="">
                                            <div class="mini-item-info">
                                                <span class="mini-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                                <?php if (empty($item['is_missing'])): ?>
                                                    <span class="mini-price"><?php echo number_format($item['price']); ?>円</span>
                                                    <?php if (!empty($item['is_sale'])): ?>
                                                        <span class="mini-sale-tag">SALE</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="mini-missing">取扱なし</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <div class="result-count">おすすめのセール商品</div>
                    <div class="result-cards">
                        <?php foreach ($searchResults as $item): ?>
                            <div class="result-card">
                                <?php if (!empty($item['is_sale'])): ?>
                                    <div class="card-rank-badge">SALE</div>
                                <?php endif; ?>
                                
                                <div class="card-img">
                                    <img src="<?php echo getProductImage($item['product_name']); ?>" alt="">
                                </div>
                                <div class="card-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                    <p class="store-name">📍 <?php echo htmlspecialchars($item['store_name']); ?></p>
                                    <div class="price-row">
                                        <span class="sale-price"><?php echo number_format($item['sale_price']); ?>円</span>
                                        
                                        <?php if (!empty($item['is_sale'])): ?>
                                            <span class="regular-price"><del><?php echo number_format($item['regular_price']); ?></del></span>
                                        <?php endif; ?>
                                    </div>
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