<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database.php';

// DBからセール情報を取得
try {
    $db = Database::getInstance();
    $sale_data = $db->getSalesByStore();
} catch (Exception $e) {
    // エラー時は空配列を設定
    $sale_data = [];
    error_log("セール情報の取得に失敗しました: " . $e->getMessage());
}
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
            <div class="search-container">
                <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input type="text" class="search-input" placeholder="検索">
            </div>
            <a href="#" class="add-store-button">登録スーパー追加</a>
        </section>

        <hr class="yellow-separator">

        <section class="sale-store-list-section">
            <div class="sale-store-list">
                <?php if (empty($sale_data)): ?>
                    <p style="text-align: center; padding: 20px; color: #666;">現在セール情報はありません</p>
                <?php else: ?>
                    <?php foreach ($sale_data as $store): ?>
                        <div class="sale-store-container" data-store-name="<?php echo htmlspecialchars($store['name']); ?>">
                            <div class="sale-store-header">
                                <img src="<?php echo htmlspecialchars($store['logo']); ?>" 
                                     alt="<?php echo htmlspecialchars($store['name']); ?>" 
                                     class="sale-store-logo"
                                     onerror="this.src='/assets/image/default_store.png'">
                                <span class="sale-store-name"><?php echo htmlspecialchars($store['name']); ?></span>
                            </div>
                            <div class="sale-items-panel">
                                <div class="panel-header">
                                    <span>セール価格</span>
                                    <span>時間</span>
                                </div>
                                <ul class="sale-item-list">
                                    <?php foreach ($store['items'] as $item): ?>
                                        <li class="sale-item" data-product-name="<?php echo htmlspecialchars($item['name']); ?>">
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="sale-item-image"
                                                 onerror="this.src='/assets/image/default_product.png'">
                                            <div class="sale-item-details">
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
                <?php endif; ?>
            </div>
        </section>

    </main>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>
    <script src="/assets/js/sale.js"></script> </body>
</html>