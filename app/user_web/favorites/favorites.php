<?php
require_once __DIR__ . '/../includes/database.php';

$db = getDB();
$userId = 1; // 仮のユーザーID

// タブ選択 (store または food)
$currentTab = $_GET['tab'] ?? 'food'; // デフォルトは食材
$searchQuery = $_GET['search'] ?? '';

// お気に入り登録/解除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'toggle_store' && isset($_POST['store_id'])) {
        $db->toggleFavoriteStore($userId, $_POST['store_id']);
    } elseif ($_POST['action'] === 'toggle_food' && isset($_POST['food_name'])) {
        $db->toggleFavoriteProduct($userId, $_POST['food_name']);
    }
    
    // 処理後にリダイレクト（再送信防止）
    $redirectUrl = "?tab={$currentTab}&search=" . urlencode($searchQuery);
    header("Location: {$redirectUrl}");
    exit;
}

// --- データ取得 ---

// 1. お気に入り一覧の取得
$favStores = $db->getUserFavoriteStores($userId);
$favProducts = $db->getUserFavoriteProducts($userId);

// 2. 検索実行（検索ワードがある場合）
$searchResults = [];
if ($searchQuery !== '') {
    if ($currentTab === 'store') {
        $searchResults = $db->searchStoresForFavorites($userId, $searchQuery);
    } else {
        $searchResults = $db->searchProductsForFavorites($userId, $searchQuery);
    }
}

// 画像ヘルパー
function getStoreLogo($name) {
    if (strpos($name, 'イオン') !== false) return '/assets/image/logo_icon.png';
    return '/assets/image/logo_icon.png';
}
function getProductImage($name) {
    if (strpos($name, '卵') !== false) return '/assets/image/eggs.png';
    if (strpos($name, '牛乳') !== false) return '/assets/image/milk.png';
    if (strpos($name, 'トマト') !== false) return '/assets/image/tomato.png';
    if (strpos($name, 'おにぎり') !== false) return '/assets/image/onigiri.png';
    if (strpos($name, '弁当') !== false) return '/assets/image/bento.png';
    if (strpos($name, '刺し身') !== false) return '/assets/image/sashimi.png';
    return '/assets/image/tirasi.png';
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
        <section class="search-section">
            <div class="search-tabs">
                <a href="?tab=food&search=<?php echo htmlspecialchars($searchQuery); ?>" class="tab-btn <?php echo $currentTab === 'food' ? 'active' : ''; ?>">食材</a>
                <a href="?tab=store&search=<?php echo htmlspecialchars($searchQuery); ?>" class="tab-btn <?php echo $currentTab === 'store' ? 'active' : ''; ?>">お店</a>
            </div>

            <form action="" method="GET" class="favorites-search">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($currentTab); ?>">
                <button type="submit" style="background:none;border:none;padding:0;">
                    <span class="search-arrow">🔍</span>
                </button>
                <input type="text" name="search" class="search-input" 
                       placeholder="<?php echo $currentTab === 'store' ? '店舗名を入力（例：イオン）' : '食材名を入力（例：牛乳）'; ?>" 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
            </form>
        </section>

        <?php if ($searchQuery !== ''): ?>
            <section class="search-results-section">
                <h3 class="favorites-title">検索結果: "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
                
                <?php if (empty($searchResults)): ?>
                    <p class="no-results">見つかりませんでした。</p>
                <?php else: ?>
                    <?php if ($currentTab === 'store'): ?>
                        <div class="store-list">
                            <?php foreach ($searchResults as $store): ?>
                                <div class="store-item">
                                    <img src="<?php echo getStoreLogo($store['name']); ?>" class="store-logo">
                                    <div class="store-details">
                                        <p class="store-name"><?php echo htmlspecialchars($store['name']); ?></p>
                                    </div>
                                    <form method="POST" id="fav_store_<?php echo $store['id']; ?>">
                                        <input type="hidden" name="action" value="toggle_store">
                                        <input type="hidden" name="store_id" value="<?php echo $store['id']; ?>">
                                        <button type="button" class="heart-btn <?php echo !empty($store['is_favorite']) ? 'active' : ''; ?>" 
                                                onclick="toggleFavorite('fav_store_<?php echo $store['id']; ?>')">
                                            <?php echo !empty($store['is_favorite']) ? '♥' : '♡'; ?>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php else: ?>
                        <div class="store-list">
                            <?php foreach ($searchResults as $prod): ?>
                                <div class="store-item">
                                    <img src="<?php echo getProductImage($prod['name']); ?>" class="store-logo">
                                    <div class="store-details">
                                        <p class="store-name"><?php echo htmlspecialchars($prod['name']); ?></p>
                                    </div>
                                    <form method="POST" id="fav_food_res_<?php echo $prod['id']; ?>">
                                        <input type="hidden" name="action" value="toggle_food">
                                        <input type="hidden" name="food_name" value="<?php echo htmlspecialchars($prod['name']); ?>">
                                        <button type="button" class="heart-btn <?php echo !empty($prod['is_favorite']) ? 'active' : ''; ?>" 
                                                onclick="toggleFavorite('fav_food_res_<?php echo $prod['id']; ?>')">
                                            <?php echo !empty($prod['is_favorite']) ? '♥' : '♡'; ?>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
            <hr class="yellow-separator">
        <?php endif; ?>

        <section class="favorites-section">
            <h2 class="favorites-title">お気に入り店舗</h2>
            <?php if (empty($favStores)): ?>
                <p style="color:#888; text-align:center;">登録された店舗はありません。</p>
            <?php else: ?>
                <div class="store-list">
                    <?php foreach ($favStores as $store): ?>
                        <div class="store-item">
                            <img src="<?php echo getStoreLogo($store['name']); ?>" class="store-logo">
                            <div class="store-details">
                                <p class="store-name"><?php echo htmlspecialchars($store['name']); ?></p>
                            </div>
                            <form method="POST" id="my_fav_store_<?php echo $store['id']; ?>">
                                <input type="hidden" name="action" value="toggle_store">
                                <input type="hidden" name="store_id" value="<?php echo $store['id']; ?>">
                                <button type="button" class="heart-btn active" 
                                        onclick="toggleFavorite('my_fav_store_<?php echo $store['id']; ?>')">♥</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <hr class="yellow-separator">

        <section class="favorites-section">
            <h2 class="favorites-title">お気に入り食べ物</h2>
            <?php if (empty($favProducts)): ?>
                <p style="color:#888; text-align:center;">登録された食材はありません。</p>
            <?php else: ?>
                <div class="store-list">
                    <?php foreach ($favProducts as $prod): ?>
                        <div class="store-item">
                            <img src="<?php echo getProductImage($prod['name']); ?>" class="store-logo">
                            <div class="store-details">
                                <p class="store-name"><?php echo htmlspecialchars($prod['name']); ?></p>
                            </div>
                            <form method="POST" id="my_fav_food_<?php echo $prod['id']; ?>">
                                <input type="hidden" name="action" value="toggle_food">
                                <input type="hidden" name="food_name" value="<?php echo htmlspecialchars($prod['name']); ?>">
                                <button type="button" class="heart-btn active" 
                                        onclick="toggleFavorite('my_fav_food_<?php echo $prod['id']; ?>')">♥</button>
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