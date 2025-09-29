<?php
$page_title = "ホーム";
$active_nav = "ホーム";
include '../includes/header.php'; // 共通ヘッダーの読み込み

// --- ページ固有のコンテンツ ---
?>
    <div class="card">
        <div class="card-title">今日の売上概要</div>
        <div class="card-content">580,000円</div>
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
        <a href="../saleinput/index.php" class="submit-button" style="margin-top: 10px;">セール情報入力へ</a>
    </div>

    <a href="#" class="menu-item" style="text-align: center; margin-top: 15px;">セール情報</a>
    <a href="#" class="menu-item" style="text-align: center;">商品管理</a>
    <a href="../analys/index.php" class="menu-item" style="text-align: center;">売上分析</a>
    <?php
include '../includes/footer.php'; // 共通フッターの読み込み
?>