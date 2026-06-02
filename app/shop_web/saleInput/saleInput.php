<?php
// 文字エンコーディングを明示的に設定
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

require_once __DIR__ . '/../includes/database.php';

$page_title = "セール情報入力";
$active_nav = "入力";
include '../includes/header.php';

// データベース接続
$db = Database::getInstance();
// $userId = 1; // デフォルトユーザーID

// メッセージ変数
$message = '';
$message_type = '';

// フォーム送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_sale'])) {
    try {
        // 入力値の取得とバリデーション
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $salePrice = isset($_POST['sale_price']) ? floatval($_POST['sale_price']) : 0;
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $storeIds = isset($_POST['store_ids']) ? $_POST['store_ids'] : [];
        
        // バリデーション
        $errors = [];
        if ($productId <= 0) {
            $errors[] = '商品を選択してください';
        }
        if ($salePrice <= 0) {
            $errors[] = 'セール価格を入力してください';
        }
        if (empty($startDate)) {
            $errors[] = '開始日を入力してください';
        }
        if (empty($endDate)) {
            $errors[] = '終了日を入力してください';
        }
        if (!empty($startDate) && !empty($endDate) && $startDate > $endDate) {
            $errors[] = '終了日は開始日より後である必要があります';
        }
        if (empty($storeIds)) {
            $errors[] = '少なくとも1つの店舗を選択してください';
        }
        
        if (empty($errors)) {
            // トランザクション開始
            $pdo = $db->getConnection();
            $pdo->beginTransaction();
            
            try {
                // セール情報を追加
                $saleId = $db->addSale($productId, $salePrice, $startDate, $endDate, $notes);
                
                // セールと店舗の関連付け
                $storeIdsInt = array_map('intval', $storeIds);
                $linkedCount = $db->linkSaleToStores($saleId, $storeIdsInt);
                
                $pdo->commit();
                
                $message = "セール情報を登録しました（店舗数: {$linkedCount}件）";
                $message_type = 'success';
                
                // フォームをクリア（リダイレクトの代わり）
                $_POST = [];
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            $message = implode('<br>', $errors);
            $message_type = 'error';
        }
        
    } catch (Exception $e) {
        $message = 'エラー: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// データ取得
$products = $db->getAllProducts();
$categories = $db->getAllCategories();
$stores = $db->getAllStores();

// 商品をカテゴリごとにグループ化
$productsByCategory = [];
foreach ($products as $product) {
    $categoryName = $product['category_name'] ?? '未分類';
    if (!isset($productsByCategory[$categoryName])) {
        $productsByCategory[$categoryName] = [];
    }
    $productsByCategory[$categoryName][] = $product;
}
?>

<?php if ($message): ?>
    <div class="message <?php echo $message_type; ?>" style="padding: 15px; margin: 15px 0; border-radius: 5px; background-color: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $message_type === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<form action="" method="post" id="saleEntryForm">
    <div class="form-group">
        <label for="product_id">商品名 <span style="color: red;">*</span></label>
        <select id="product_id" name="product_id" required class="form-input">
            <option value="">-- 商品を選択してください --</option>
            <?php foreach ($productsByCategory as $categoryName => $categoryProducts): ?>
                <optgroup label="<?php echo htmlspecialchars($categoryName); ?>">
                    <?php foreach ($categoryProducts as $product): ?>
                        <option value="<?php echo $product['id']; ?>" 
                                data-price="<?php echo $product['regular_price']; ?>"
                                <?php echo (isset($_POST['product_id']) && $_POST['product_id'] == $product['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($product['name']); ?> 
                            (通常価格: <?php echo number_format($product['regular_price']); ?>円)
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="sale_price">セール価格 <span style="color: red;">*</span></label>
        <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" required class="form-input" 
               value="<?php echo htmlspecialchars($_POST['sale_price'] ?? ''); ?>" placeholder="例: 298">
        <small style="color: #666; font-size: 0.9em;">通常価格より安い価格を設定してください</small>
    </div>
    
    <div class="form-group">
        <label for="start_date">開始日 <span style="color: red;">*</span></label>
        <input type="date" id="start_date" name="start_date" required class="form-input" 
               value="<?php echo htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')); ?>">
    </div>
    
    <div class="form-group">
        <label for="end_date">終了日 <span style="color: red;">*</span></label>
        <input type="date" id="end_date" name="end_date" required class="form-input" 
               value="<?php echo htmlspecialchars($_POST['end_date'] ?? date('Y-m-d', strtotime('+7 days'))); ?>">
    </div>
    
    <div class="form-group">
        <label>対象店舗 <span style="color: red;">*</span></label>
        <div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; max-height: 200px; overflow-y: auto;">
            <?php if (empty($stores)): ?>
                <p style="color: #666; font-size: 0.9em;">店舗データがありません。先に店舗を登録してください。</p>
            <?php else: ?>
                <?php foreach ($stores as $store): ?>
                    <label style="display: block; padding: 5px 0; cursor: pointer;">
                        <input type="checkbox" name="store_ids[]" value="<?php echo $store['id']; ?>" 
                               <?php echo (isset($_POST['store_ids']) && in_array($store['id'], $_POST['store_ids'])) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($store['name']); ?>
                        <?php if (!empty($store['address'])): ?>
                            <span style="color: #666; font-size: 0.9em;">(<?php echo htmlspecialchars($store['address']); ?>)</span>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <small style="color: #666; font-size: 0.9em;">複数選択可能</small>
    </div>
    
    <div class="form-group">
        <label for="notes">特記事項</label>
        <textarea id="notes" name="notes" class="form-input" rows="3" 
                  placeholder="例: 期間限定セール、朝採れセールなど"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
    </div>

    <button type="submit" name="submit_sale" class="submit-button">この内容で登録する</button>
</form>

<script>
// 商品選択時に通常価格を表示
document.getElementById('product_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const regularPrice = selectedOption.getAttribute('data-price');
    const salePriceInput = document.getElementById('sale_price');
    
    if (regularPrice) {
        salePriceInput.placeholder = '通常価格: ' + parseInt(regularPrice).toLocaleString() + '円';
    }
});

// 日付のバリデーション
document.getElementById('end_date').addEventListener('change', function() {
    const startDate = document.getElementById('start_date').value;
    const endDate = this.value;
    
    if (startDate && endDate && startDate > endDate) {
        alert('終了日は開始日より後である必要があります');
        this.value = '';
    }
});
</script>

<?php
include '../includes/footer.php';
?>
