<?php
// ヘッダーやフッターで必要な変数
// $page_title と $active_nav は、このファイルをインクルードするページ側で定義されている必要があります。
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../includes/style.css"> 
</head>
<body>
    <header>
        <div class="hamburger-menu" id="hamburgerMenu">
            <span></span><span></span><span></span>
        </div>
        <div class="header-title"><?php echo $page_title; ?></div>
        <div class="header-right-spacer"></div>
    </header>

    <nav class="side-navigation" id="sideNavigation">
        <ul>
            <li><a href="../home/home.php">ホーム</a></li>
            <li><a href="../analys/analys.php">分析</a></li>
            <li><a href="../saleInput/saleInput.php">入力</a></li>
            <li><a href="../setting/setting.php">設定</a></li>
            <li><a href="#">商品管理</a></li>
            <li><a href="#">お知らせ</a></li>
        </ul>
    </nav>
    <div class="overlay" id="overlay"></div>

    <div class="container">

