<?php
require_once __DIR__ . '/../includes/database.php';

$page_title = "セール情報入力";
$active_nav = "入力";
include '../includes/header.php';

$db = Database::getInstance();
$message = '';
$error = '';

// フォーム送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $category_name = trim($_POST['category'] ?? '');
    $regular_price = floatval($_POST['regular_price'] ?? 0);
    $sale_price = floatval($_POST['sale_price'] ?? 0);
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $end_date = $_POST['end_date'] ?? date('Y-m-d');
    $notes = trim($_POST['notes'] ?? '');
    $user_id = 1; // 仮のユーザーID（認証実装後に変更）
    
    // バリデーション
    if (empty($product_name) || empty($category_name) || $sale_price <= 0 || $regular_price <= 0) {
        $error = '必須項目を入力してください';
    } elseif ($sale_price >= $regular_price) {
        $error = 'セール価格は通常価格より低く設定してください';
    } elseif (strtotime($start_date) > strtotime($end_date)) {
        $error = '終了日は開始日以降に設定してください';
    } else {
        try {
            // カテゴリを取得または作成
            $category = $db->selectOne("SELECT id FROM categories WHERE name = ? AND user_id = ?", [$category_name, $user_id]);
            if (!$category) {
                $category_id = $db->insert("INSERT INTO categories (name, user_id) VALUES (?, ?)", [$category_name, $user_id]);
            } else {
                $category_id = $category['id'];
            }
            
            // 商品を取得または作成
            $product = $db->selectOne("SELECT id FROM products WHERE name = ? AND user_id = ?", [$product_name, $user_id]);
            if (!$product) {
                $product_id = $db->insert(
                    "INSERT INTO products (name, category_id, regular_price, stock_quantity, user_id) VALUES (?, ?, ?, ?, ?)",
                    [$product_name, $category_id, $regular_price, 0, $user_id]
                );
            } else {
                $product_id = $product['id'];
                // 既存商品の通常価格を更新
                $db->update(
                    "UPDATE products SET regular_price = ? WHERE id = ?",
                    [$regular_price, $product_id]
                );
            }
            
            // セール情報を登録
            $db->addSale($product_id, $sale_price, $start_date, $end_date, $user_id, $notes);
            
            $message = 'セール情報を登録しました';
            // フォームをリセット
            $_POST = [];
        } catch (Exception $e) {
            $error = '登録に失敗しました: ' . $e->getMessage();
            error_log("セール情報登録エラー: " . $e->getMessage());
        }
    }
}

// カテゴリ一覧を取得
$categories = $db->getAllCategories();
?>

<?php if ($message): ?>
    <div style="background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form action="" method="post" id="saleEntryForm">
    <div class="form-group">
        <label for="product_name">商品名 <span style="color: red;">*</span></label>
        <input type="text" id="product_name" name="product_name" required class="form-input" value="<?php echo htmlspecialchars($_POST['product_name'] ?? ''); ?>">
    </div>

    <div class="form-group">
        <label for="category">カテゴリ <span style="color: red;">*</span></label>
        <select id="category" name="category" required class="form-input">
            <option value="">選択してください</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo (isset($_POST['category']) && $_POST['category'] === $cat['name']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small style="color: #666;">新しいカテゴリを追加する場合は、上記から選択後、商品登録時に自動的に作成されます</small>
    </div>
    
    <div class="form-group">
        <label for="regular_price">通常価格 <span style="color: red;">*</span></label>
        <input type="number" id="regular_price" name="regular_price" step="0.01" min="0" required class="form-input" value="<?php echo htmlspecialchars($_POST['regular_price'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="sale_price">セール価格 <span style="color: red;">*</span></label>
        <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" required class="form-input" value="<?php echo htmlspecialchars($_POST['sale_price'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="start_date">開始日 <span style="color: red;">*</span></label>
        <input type="date" id="start_date" name="start_date" required class="form-input" value="<?php echo htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')); ?>">
    </div>
    
    <div class="form-group">
        <label for="end_date">終了日 <span style="color: red;">*</span></label>
        <input type="date" id="end_date" name="end_date" required class="form-input" value="<?php echo htmlspecialchars($_POST['end_date'] ?? date('Y-m-d')); ?>">
    </div>
    
    <div class="form-group">
        <label for="notes">特記事項（セール時間など）</label>
        <textarea id="notes" name="notes" class="form-input" placeholder="例: 19:00まで"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
    </div>

    <button type="submit" class="submit-button">この内容で登録する</button>
</form>

<?php
include '../includes/footer.php';
?>