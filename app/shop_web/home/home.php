<?php
$page_title = "ホーム";
$active_nav = "ホーム";
include '../includes/header.php'; // 共通ヘッダーの読み込み
?>
    <div class="card">
        <div class="card-title">今日の売上概要</div>
        <div class="card-content" style="color: #28a745;">580,000円</div>
        <div class="card-sub-content">目標達成率: 80% (+10%)</div>
    </div>

    <div class="card">
        <div class="card-title">在庫アラート</div>
        <div class="card-sub-content">在庫が少ない商品: 5種類</div>
    </div>

    <div class="card">
        <div class="card-title">期限が近いセール情報</div>
        <div class="card-sub-content">明日までのセール: 3件</div>
    </div>

    <div class="card">
        <div class="card-title">新しいセール情報を追加しませんか？</div>
        <a href="../saleInput/saleInput.php" class="submit-button" style="margin-top: 10px;">セール情報入力へ</a>
    </div>

    <a href="../saleInput/saleInput.php" class="menu-item" style="text-align: center; margin-top: 15px;">新しいセール情報を追加</a>
    <a href="" class="menu-item" style="text-align: center; margin-top: 15px;">セール情報</a>
    <a href="#" class="menu-item" style="text-align: center;">商品管理</a>
    <a href="../analys/analys.php" class="menu-item" style="text-align: center;">売上分析</a>
    <a href="#" class="menu-item" style="text-align: center;">在庫管理</a>
    <a href="#" class="menu-item" style="text-align: center;">顧客管理</a>
    <a href="#" class="menu-item" style="text-align: center;">お知らせ</a>
    <a href="#" class="menu-item" style="text-align: center;">設定</a>

<?php
include '../includes/footer.php'; // 共通フッターの読み込み
?>