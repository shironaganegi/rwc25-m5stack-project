<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database.php';

// 画像パス生成ヘルパー関数
function getProductImage($productName, $categoryName) {
    if (strpos($productName, '卵') !== false) return '/assets/image/eggs.png';
    if (strpos($productName, '牛乳') !== false) return '/assets/image/milk.png';
    if (strpos($productName, 'トマト') !== false) return '/assets/image/tomato.png';
    if (strpos($productName, 'レシピ') !== false) return '/assets/image/レシピ.png';
    return '/assets/image/tirasi.png';
}

// 店舗ロゴヘルパー
function getStoreLogo($storeName) {
    if (strpos($storeName, 'イオン') !== false) return '/assets/image/logo_icon.png';
    if (strpos($storeName, 'ライフ') !== false) return '/assets/image/logo_icon.png';
    return '/assets/image/logo_icon.png';
}

// ユーザーID (仮)
$userId = 1;

// 検索キーワードの取得
$keyword = $_GET['search'] ?? null;

// データベースからセール情報を取得（キーワードとお気に入りで絞り込み）
$db = getDB();

// 第2引数にユーザーIDを渡して、お気に入り店舗のみに限定する
$raw_sales = $db->getActiveSalesByStore($keyword, $userId);

$sale_data = [];
foreach ($raw_sales as $row) {
    $storeId = $row['store_id'];
    
    if (!isset($sale_data[$storeId])) {
        $sale_data[$storeId] = [
            'id' => $storeId,
            'name' => $row['store_name'],
            'logo' => getStoreLogo($row['store_name']),
            'items' => []
        ];
    }
    
    $sale_data[$storeId]['items'][] = [
        'image' => getProductImage($row['product_name'], $row['category_name']),
        'name' => $row['product_name'],
        'price_old' => number_format($row['regular_price']) . '円',
        'price_new' => number_format($row['sale_price']) . '円',
        'time' => date('m/d', strtotime($row['end_date'])) . 'まで'
    ];
}

$sale_data = array_values($sale_data);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>セール情報</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/sale/sale.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <section class="sale-header-section">
            <form action="" method="GET" class="search-container">
                <button type="submit" style="background:none; border:none; padding:0; cursor:pointer; color:inherit; display:flex;">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </button>
                <input type="text" name="search" class="search-input" placeholder="食材、店舗名で検索" value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
            </form>
        </section>

        <hr class="yellow-separator">

        <section class="sale-store-list-section">
            <?php if (empty($sale_data)): ?>
                <div class="no-sales-message" style="text-align: center; padding: 20px;">
                    <?php if ($keyword): ?>
                        <p>条件に一致するセール商品は見つかりませんでした。</p>
                        <a href="/sale/sale.php" style="color: #007bff; text-decoration: underline;">条件をクリア</a>
                    <?php else: ?>
                        <p>お気に入り店舗のセール情報はありません。</p>
                        <p style="font-size:0.9em; color:#666; margin-top:10px; line-height:1.6;">
                            店舗をお気に入り登録すると、ここにセール情報が表示されます。<br>
                            <a href="/favorites/favorites.php?tab=store" style="color:#007bff; text-decoration:underline; font-weight:bold;">
                                ＞ お気に入り店舗を登録する
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="sale-store-list">
                    <?php foreach ($sale_data as $store): ?>
                        <div class="sale-store-container">
                            <div class="sale-store-header">
                                <img src="<?php echo htmlspecialchars($store['logo']); ?>" alt="<?php echo htmlspecialchars($store['name']); ?>" class="sale-store-logo">
                                <span class="sale-store-name"><?php echo htmlspecialchars($store['name']); ?></span>
                            </div>
                            <div class="sale-items-panel">
                                <div class="panel-header">
                                    <span>セール価格</span>
                                    <span>期間</span>
                                </div>
                                <ul class="sale-item-list">
                                    <?php foreach ($store['items'] as $item): ?>
                                        <li class="sale-item">
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="sale-item-image">
                                            <div class="sale-item-details">
                                                <p class="name"><?php echo htmlspecialchars($item['name']); ?></p>
                                                <p class="price">
                                                    <span class="price-old"><?php echo htmlspecialchars($item['price_old']); ?></span>
                                                    <span class="price-arrow">→</span>
                                                    <span class="price-new"><?php echo htmlspecialchars($item['price_new']); ?></span>
                                                </p>
                                            </div>
                                            <div class="sale-item-time">
                                                <span><?php echo htmlspecialchars($item['time']); ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>
    <script src="/assets/js/sale.js"></script> </body>
</html>