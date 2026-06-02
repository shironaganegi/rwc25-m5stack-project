<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database.php';

// ユーザーID固定（将来的にはセッションから取得）
$user_id = 1;

// データベース接続
$db = Database::getInstance();

// お気に入りの追加・削除処理
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'add_store' && isset($_POST['store_id'])) {
                $storeId = intval($_POST['store_id']);
                $result = $db->addFavoriteStore($user_id, $storeId);
                if ($result !== false) {
                    $message = 'お気に入り店舗に追加しました';
                    $message_type = 'success';
                } else {
                    $message = '既にお気に入りに登録されています';
                    $message_type = 'info';
                }
            } elseif ($_POST['action'] === 'remove_store' && isset($_POST['store_id'])) {
                $storeId = intval($_POST['store_id']);
                $db->removeFavoriteStore($user_id, $storeId);
                $message = 'お気に入り店舗から削除しました';
                $message_type = 'success';
            } elseif ($_POST['action'] === 'add_product' && isset($_POST['product_id'])) {
                $productId = intval($_POST['product_id']);
                $result = $db->addFavoriteProduct($user_id, $productId);
                if ($result !== false) {
                    $message = 'お気に入り商品に追加しました';
                    $message_type = 'success';
                } else {
                    $message = '既にお気に入りに登録されています';
                    $message_type = 'info';
                }
            } elseif ($_POST['action'] === 'remove_product' && isset($_POST['product_id'])) {
                $productId = intval($_POST['product_id']);
                $db->removeFavoriteProduct($user_id, $productId);
                $message = 'お気に入り商品から削除しました';
                $message_type = 'success';
            }
        } catch (Exception $e) {
            $message = 'エラー: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// 検索キーワードの取得
$search_keyword = $_GET['search'] ?? '';

// お気に入り店舗を取得
$favorite_stores = $db->getFavoriteStores($user_id);

// お気に入り商品を取得
$favorite_products = $db->getFavoriteProducts($user_id);

// 検索フィルタリング
if (!empty($search_keyword)) {
    $favorite_stores = array_filter($favorite_stores, function($store) use ($search_keyword) {
        return stripos($store['name'], $search_keyword) !== false || 
               stripos($store['address'] ?? '', $search_keyword) !== false;
    });
    
    $favorite_products = array_filter($favorite_products, function($product) use ($search_keyword) {
        return stripos($product['name'], $search_keyword) !== false || 
               stripos($product['category_name'] ?? '', $search_keyword) !== false;
    });
}

// 画像パス生成ヘルパー関数
function getProductImage($productName, $categoryName = '') {
    if (strpos($productName, '卵') !== false || strpos($productName, 'たまご') !== false) return '/assets/image/eggs.png';
    if (strpos($productName, '牛乳') !== false || strpos($productName, 'ミルク') !== false) return '/assets/image/milk.png';
    if (strpos($productName, 'トマト') !== false) return '/assets/image/tomato.png';
    if (strpos($productName, 'おにぎり') !== false) return '/assets/image/onigiri.png';
    if (strpos($productName, '弁当') !== false) return '/assets/image/bento.png';
    if (strpos($productName, '刺し身') !== false || strpos($productName, 'さしみ') !== false) return '/assets/image/sashimi.png';
    return '/assets/image/tirasi.png';
}

// 店舗ロゴヘルパー
function getStoreLogo($storeName) {
    if (strpos($storeName, 'イオン') !== false) return '/assets/image/logo_icon.png';
    if (strpos($storeName, 'ライフ') !== false) return '/assets/image/logo_icon.png';
    return '/assets/image/logo_icon.png';
}

// 距離計算（簡易版 - 現在位置は固定）
function calculateDistance($lat, $lon) {
    // 近畿大学の座標（例）
    $base_lat = 34.6764;
    $base_lon = 135.4986;
    
    if ($lat === null || $lon === null) {
        return '距離不明';
    }
    
    // 簡易距離計算（ハーバーサイン公式の簡略版）
    $distance = sqrt(pow(($lat - $base_lat) * 111, 2) + pow(($lon - $base_lon) * 91, 2));
    return number_format($distance, 1) . 'km';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お気に入り登録</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/favorites/favorites.css">
    <script>
        function toggleFavorite(formId) {
            document.getElementById(formId).submit();
        }
    </script>
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>" style="padding: 10px; margin: 10px 20px; border-radius: 5px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : ($message_type === 'error' ? '#f8d7da' : '#d1ecf1'); ?>; color: <?php echo $message_type === 'success' ? '#155724' : ($message_type === 'error' ? '#721c24' : '#0c5460'); ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <section class="search-section">
            <form action="" method="GET" class="search-container favorites-search">
                <span class="search-arrow">></span>
                <input type="text" name="search" class="search-input" placeholder="検索" value="<?php echo htmlspecialchars($search_keyword); ?>">
            </form>
        </section>

        <section class="favorites-section">
            <h2 class="favorites-title">店舗情報</h2>
            <?php if (empty($favorite_stores)): ?>
                <p style="color: #666; padding: 20px;">お気に入り店舗がありません</p>
            <?php else: ?>
                <div class="store-list">
                    <?php foreach ($favorite_stores as $store): ?>
                        <?php
                        // セール情報を取得
                        $sale_info = $db->getStoreSaleTimes($store['id']);
                        $sale_time_text = '';
                        if (!empty($sale_info) && isset($sale_info[0]['has_sale']) && $sale_info[0]['has_sale']) {
                            $sale_time_text = 'セール開催中';
                        } else {
                            $sale_time_text = 'セール情報なし';
                        }
                        ?>
                        <div class="store-item">
                            <img src="<?php echo getStoreLogo($store['name']); ?>" alt="<?php echo htmlspecialchars($store['name']); ?>" class="store-logo">
                            <div class="store-details">
                                <p class="sale-time"><?php echo htmlspecialchars($sale_time_text); ?></p>
                                <p class="store-name"><?php echo htmlspecialchars($store['name']); ?></p>
                                <p class="store-distance"><?php echo calculateDistance($store['latitude'], $store['longitude']); ?></p>
                            </div>
                            <div class="congestion-info">
                                <p class="congestion-label">混雑度</p>
                                <p class="congestion-value">--</p>
                            </div>
                            <form method="POST" action="" style="margin-top: 10px;">
                                <input type="hidden" name="action" value="remove_store">
                                <input type="hidden" name="store_id" value="<?php echo $store['id']; ?>">
                                <button type="submit" class="remove-button" style="background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 0.9em;">削除</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <hr class="yellow-separator">

        <section class="favorites-section">
            <h2 class="favorites-title">お気に入り食べ物</h2>
            <?php if (empty($favorite_products)): ?>
                <p style="color: #666; padding: 20px;">お気に入り商品がありません</p>
            <?php else: ?>
                <div class="food-grid">
                    <?php foreach ($favorite_products as $product): ?>
                        <div class="food-item">
                            <img src="<?php echo getProductImage($product['name'], $product['category_name'] ?? ''); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <p><?php echo htmlspecialchars($product['name']); ?></p>
                            <form method="POST" action="" style="margin-top: 5px;">
                                <input type="hidden" name="action" value="remove_product">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="remove-button" style="background-color: #dc3545; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; font-size: 0.8em;">削除</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

</body>
</html>
