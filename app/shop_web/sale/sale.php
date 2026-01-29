<?php
require_once __DIR__ . '/../includes/database.php';

$page_title = "セール情報";
$active_nav = "セール情報";
include '../includes/header.php'; // 共通ヘッダーの読み込み

?>

    <div class="card">
        <div class="card-title">ライフ八戸の里店のセール情報</div>
</div>

    <div class="card">
        <div class="card-title">イオン登美ヶ丘店のセール情報</div>
</div>
    
    <div class="card">
        <div class="card-title">マツモトキヨシ八戸ノ里駅前店のセール情報</div>
</div>


    <div class="card">
        <div class="card-title">期限が近いセール情報</div>
        <div class="card-sub-content">
            明日までのセール: <?php echo $expiringSalesCount; ?>件
        </div>
        <?php if ($expiringSalesCount > 0): ?>
            <div class="card-sub-content" style="margin-top: 5px;">
                <?php foreach (array_slice($expiringSales, 0, 3) as $sale): ?>
                    <div style="margin-top: 5px; font-size: 0.9em;">
                        • <?php echo htmlspecialchars($sale['product_name'] ?? '不明'); ?>
                        (<?php echo date('m/d', strtotime($sale['end_date'])); ?>まで)
                    </div>
                <?php endforeach; ?>
                <?php if ($expiringSalesCount > 3): ?>
                    <div style="margin-top: 5px; color: #666; font-size: 0.85em;">
                        他 <?php echo $expiringSalesCount - 3; ?>件
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-title">新しいセール情報を追加しませんか？</div>
        <a href="../saleInput/saleInput.php" class="submit-button" style="margin-top: 10px;">セール情報入力へ</a>
    </div>

    <a href="../saleInput/saleInput.php" class="menu-item" style="text-align: center; margin-top: 15px;">新しいセール情報を追加</a>

<?php
include '../includes/footer.php'; // 共通フッターの読み込み
?>
