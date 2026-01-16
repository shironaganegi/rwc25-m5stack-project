<?php
require_once __DIR__ . '/../includes/database.php';

$page_title = "ホーム";
$active_nav = "ホーム";
include '../includes/header.php'; // 共通ヘッダーの読み込み

// データベース接続
$db = Database::getInstance();

// 今日の売上を取得
$todaysSales = $db->getTodaysSales();
$totalSales = $todaysSales['total_sales'] ?? 0;

// 前日の売上を取得（前日比計算用）
$yesterdaysSales = $db->getYesterdaysSales();
$yesterdayTotal = $yesterdaysSales['total_sales'] ?? 0;

// 前日比を計算
$salesChange = 0;
$salesChangePercent = 0;
if ($yesterdayTotal > 0) {
    $salesChange = $totalSales - $yesterdayTotal;
    $salesChangePercent = round(($salesChange / $yesterdayTotal) * 100, 1);
} elseif ($totalSales > 0) {
    $salesChange = $totalSales;
    $salesChangePercent = 100;
}

// 売上目標と達成率を取得
$salesTarget = $db->getSalesTarget();
$targetAmount = $salesTarget['target_amount'] ?? 0;
$actualAmount = $salesTarget['actual_amount'] ?? 0;
$achievementRate = $salesTarget['achievement_rate'] ?? 0;

// 前日の達成率を取得（前日比計算用）
$yesterdayTarget = $db->selectOne("
    SELECT st.target_amount,
           COALESCE(SUM(sd.total_amount), 0) as actual_amount,
           CASE 
               WHEN st.target_amount > 0 THEN 
                   ROUND((COALESCE(SUM(sd.total_amount), 0) / st.target_amount) * 100, 1)
               ELSE 0 
           END as achievement_rate
    FROM sales_targets st
    LEFT JOIN sales_data sd ON st.target_date = sd.sale_date
    WHERE st.target_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    GROUP BY st.target_amount
");
$yesterdayAchievementRate = $yesterdayTarget['achievement_rate'] ?? 0;
$achievementRateChange = round($achievementRate - $yesterdayAchievementRate, 1);

// 在庫アラートを取得
$lowStockCount = $db->getLowStockCount(10);
$lowStockCountValue = $lowStockCount['count'] ?? 0;

// 期限が近いセール情報を取得
$expiringSales = $db->getExpiringSales();
$expiringSalesCount = count($expiringSales);
?>

    <div class="card">
        <div class="card-title">今日の売上概要</div>
        <div class="card-content" style="color: #28a745;">
            <?php echo number_format($totalSales); ?>円
        </div>
        <div class="card-sub-content">
            目標達成率: <?php echo number_format($achievementRate, 1); ?>%
            <?php if ($achievementRateChange != 0): ?>
                <span style="color: <?php echo $achievementRateChange > 0 ? '#28a745' : '#dc3545'; ?>;">
                    (<?php echo $achievementRateChange > 0 ? '+' : ''; ?><?php echo number_format($achievementRateChange, 1); ?>%)
                </span>
            <?php endif; ?>
        </div>
        <?php if ($salesChange != 0): ?>
            <div class="card-sub-content" style="margin-top: 5px;">
                前日比: 
                <span style="color: <?php echo $salesChange > 0 ? '#28a745' : '#dc3545'; ?>;">
                    <?php echo $salesChange > 0 ? '+' : ''; ?><?php echo number_format($salesChange); ?>円
                    (<?php echo $salesChangePercent > 0 ? '+' : ''; ?><?php echo number_format($salesChangePercent, 1); ?>%)
                </span>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-title">在庫アラート</div>
        <div class="card-sub-content">
            在庫が少ない商品: <?php echo $lowStockCountValue; ?>種類
        </div>
        <?php if ($lowStockCountValue > 0): ?>
            <div class="card-sub-content" style="margin-top: 5px; color: #dc3545;">
                <a href="#" style="color: #dc3545; text-decoration: underline;">詳細を確認</a>
            </div>
        <?php endif; ?>
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

    <a href="../saleInput/saleInput.php" class="menu-item" style="text-align: center; margin-top: 15px;">新しいセール情報を追加</a>
    <a href="" class="menu-item" style="text-align: center; margin-top: 15px;">セール情報</a>
    <a href="#" class="menu-item" style="text-align: center;">商品管理</a>
    <a href="../analys/analys.php" class="menu-item" style="text-align: center;">売上分析</a>
    <a href="#" class="menu-item" style="text-align: center;">在庫管理</a>
    <a href="#" class="menu-item" style="text-align: center;">顧客管理</a>
    <a href="#" class="menu-item" style="text-align: center;">お知らせ</a>
    <a href="../setting/setting.php" class="menu-item" style="text-align: center;">設定</a>

<?php
include '../includes/footer.php'; // 共通フッターの読み込み
?>
