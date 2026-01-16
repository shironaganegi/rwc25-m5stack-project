<?php
require_once __DIR__ . '/../includes/database.php';

$page_title = "分析結果";
$active_nav = "分析";
include '../includes/header.php';

// データベース接続
$db = Database::getInstance();
$userId = 1; // デフォルトユーザーID

// 期間選択の処理
$period = $_GET['period'] ?? 'this_month';
$periodNames = [
    'this_month' => '今月',
    'last_month' => '先月',
    'this_week' => '今週',
    'last_week' => '先週',
    'this_year' => '今年',
    'last_year' => '去年'
];

// 期間の開始日と終了日を計算
$today = date('Y-m-d');
$currentStart = '';
$currentEnd = '';
$previousStart = '';
$previousEnd = '';

switch ($period) {
    case 'this_month':
        $currentStart = date('Y-m-01');
        $currentEnd = date('Y-m-t');
        $previousStart = date('Y-m-01', strtotime('-1 month'));
        $previousEnd = date('Y-m-t', strtotime('-1 month'));
        break;
    case 'last_month':
        $currentStart = date('Y-m-01', strtotime('-1 month'));
        $currentEnd = date('Y-m-t', strtotime('-1 month'));
        $previousStart = date('Y-m-01', strtotime('-2 month'));
        $previousEnd = date('Y-m-t', strtotime('-2 month'));
        break;
    case 'this_week':
        $currentStart = date('Y-m-d', strtotime('monday this week'));
        $currentEnd = date('Y-m-d', strtotime('sunday this week'));
        $previousStart = date('Y-m-d', strtotime('monday last week'));
        $previousEnd = date('Y-m-d', strtotime('sunday last week'));
        break;
    case 'last_week':
        $currentStart = date('Y-m-d', strtotime('monday last week'));
        $currentEnd = date('Y-m-d', strtotime('sunday last week'));
        $previousStart = date('Y-m-d', strtotime('monday -2 weeks'));
        $previousEnd = date('Y-m-d', strtotime('sunday -2 weeks'));
        break;
    case 'this_year':
        $currentStart = date('Y-01-01');
        $currentEnd = date('Y-12-31');
        $previousStart = date('Y-01-01', strtotime('-1 year'));
        $previousEnd = date('Y-12-31', strtotime('-1 year'));
        break;
    case 'last_year':
        $currentStart = date('Y-01-01', strtotime('-1 year'));
        $currentEnd = date('Y-12-31', strtotime('-1 year'));
        $previousStart = date('Y-01-01', strtotime('-2 year'));
        $previousEnd = date('Y-12-31', strtotime('-2 year'));
        break;
}

// 売上データを取得
$salesComparison = $db->getSalesComparison($currentStart, $currentEnd, $previousStart, $previousEnd, $userId);
$totalSales = $salesComparison['current'];
$previousSales = $salesComparison['previous'];
$salesChange = $salesComparison['change'];
$salesChangePercent = $salesComparison['change_percent'];

// 日別売上データを取得（グラフ用）
$dailySales = $db->getDailySales($currentStart, $currentEnd, $userId);

// グラフ用データの準備
$chartLabels = [];
$chartData = [];
foreach ($dailySales as $day) {
    $chartLabels[] = date('m/d', strtotime($day['sale_date']));
    $chartData[] = floatval($day['daily_total'] ?? 0);
}
?>

<div style="margin-bottom: 15px;">
    <form method="GET" action="" style="display: inline-block;">
        <label for="period_select" style="font-size: 14px; margin-right: 10px;">期間選択</label>
        <select id="period_select" name="period" class="form-input" style="width: auto; display: inline-block; padding: 5px;" onchange="this.form.submit()">
            <?php foreach ($periodNames as $key => $name): ?>
                <option value="<?php echo $key; ?>" <?php echo $period === $key ? 'selected' : ''; ?>>
                    <?php echo $name; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <span style="margin-left: 15px; color: #666; font-size: 0.9em;">
        <?php echo date('Y年m月d日', strtotime($currentStart)); ?> 〜 <?php echo date('Y年m月d日', strtotime($currentEnd)); ?>
    </span>
</div>

<div class="card">
    <div class="card-title" style="text-align: center;">総売上</div>
    <div class="card-content" style="text-align: center; font-size: 1.5em; font-weight: bold;">
        <?php echo number_format($totalSales); ?>円
    </div>
    <?php if ($salesChange != 0): ?>
        <div class="card-sub-content" style="text-align: center;">
            前期間比: 
            <span style="color: <?php echo $salesChange > 0 ? '#28a745' : '#dc3545'; ?>; font-weight: bold;">
                <?php echo $salesChange > 0 ? '+' : ''; ?><?php echo number_format($salesChange); ?>円
                (<?php echo $salesChangePercent > 0 ? '+' : ''; ?><?php echo number_format($salesChangePercent, 1); ?>%)
            </span>
        </div>
    <?php else: ?>
        <div class="card-sub-content" style="text-align: center; color: #666;">
            前期間比: データなし
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-title" style="text-align: center; margin-bottom: 15px;">日別売上推移</div>
    <canvas id="salesChart" style="max-height: 300px;"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    const chartData = {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: '日別売上',
            data: <?php echo json_encode($chartData); ?>,
            borderColor: 'rgb(40, 167, 69)',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    };
    
    const config = {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '売上: ' + context.parsed.y.toLocaleString() + '円';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + '円';
                        }
                    }
                }
            }
        }
    };
    
    new Chart(ctx, config);
});
</script>

<?php
include '../includes/footer.php';
?>
