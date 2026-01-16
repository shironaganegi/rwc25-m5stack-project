<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database.php';

// ユーザーID固定
$user_id = 1;
$message = "";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // --- アイテム追加処理 ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
        $name = $_POST['name'] ?? '';
        $quantity = $_POST['quantity'] ?? 1;
        // 購入日（入力がなければ今日）
        $purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : date('Y-m-d');
        // 期限（任意）
        $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

        if ($name) {
            // created_at を購入日として使用します
            $stmt = $pdo->prepare("
                INSERT INTO user_refrigerator (user_id, product_name, quantity, expiry_date, created_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            // 時刻情報を付けるために時間を結合
            $created_at_value = $purchase_date . ' ' . date('H:i:s');
            
            $stmt->execute([$user_id, $name, $quantity, $expiry_date, $created_at_value]);
            $message = "「{$name}」を追加しました。";
            
            // 再送信防止のリダイレクト
            header("Location: /index.php");
            exit;
        }
    }

    // --- データ取得処理 ---
    $sql = "
        SELECT ur.* FROM user_refrigerator ur
        WHERE ur.user_id = ?
        ORDER BY ur.created_at DESC
    ";
    $items = $db->select($sql, [$user_id]);


} catch (Exception $e) {
    $message = "エラー: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>冷蔵庫内情報</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/index.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <section class="refrigerator-image-section">
            <div class="refrigerator-image-container">
                <div class="fridge-static-image">
                    <img src="/assets/image/冷蔵庫上段.png" alt="冷蔵庫">
                    <img src="/assets/image/冷蔵庫下段.png" alt="冷蔵庫" style="margin-top: -4px;">
                </div>
            </div>
        </section>

        <hr class="yellow-separator">

        <section class="search-section">
            <div class="search-container">
                <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input type="text" id="search-input" class="search-input" placeholder="冷蔵庫の中身を検索...">
            </div>

            <div class="add-item-container">
                <h3>食品を追加</h3>
                <form method="post" action="" class="add-item-form">
                    <div class="form-row">
                        <input type="text" name="name" placeholder="食品名 (例: 卵)" required class="form-input">
                        <input type="number" name="quantity" placeholder="個数" value="1" min="1" class="form-input form-input-small">
                    </div>
                    <div class="form-row">
                        <div class="date-input-group">
                            <label>購入日</label>
                            <input type="date" name="purchase_date" class="form-input" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="date-input-group">
                            <label>期限</label>
                            <input type="date" name="expiry_date" class="form-input">
                        </div>
                    </div>
                    <button type="submit" name="add_item" class="add-button">追加</button>
                </form>
                <?php if ($message): ?>
                    <p class="message"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>
            </div>
        </section>

        <section class="item-list-section">
            <div class="list-header">
                <span class="header-shelf">冷蔵庫の中身一覧</span>
            </div>
            <div class="items-container" id="items-list">
                <?php if (empty($items)): ?>
                    <div style="padding: 20px; text-align: center; color: #888;">
                        登録されている食品はありません。
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <div class="fridge-item" data-name="<?php echo htmlspecialchars($item['product_name']); ?>">
                            <img src="/assets/image/logo_icon.png" alt="Icon" class="item-image">
                            <div class="item-details">
                                <p class="item-brand">--</p>
                                <p class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                
                                <div class="item-dates">
                                    <p class="item-info">購入: <?php echo date('n/j', strtotime($item['created_at'])); ?></p>
                                    <p class="item-info" style="<?php echo ($item['expiry_date'] && strtotime($item['expiry_date']) < time()) ? 'color:red;' : ''; ?>">
                                        期限: <?php echo $item['expiry_date'] ? date('n/j', strtotime($item['expiry_date'])) : '--'; ?>
                                    </p>
                                </div>
                                <p class="item-info">個数: <?php echo htmlspecialchars($item['quantity']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const items = document.querySelectorAll('.fridge-item');

            searchInput.addEventListener('input', function(e) {
                const keyword = e.target.value.toLowerCase();
                items.forEach(item => {
                    const name = item.getAttribute('data-name').toLowerCase();
                    if (name.includes(keyword)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>

</body>
</html>