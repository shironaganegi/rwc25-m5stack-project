<?php
//このファイルはフッターナビゲーションとJavaScriptの読み込み、HTMLの閉じタグを含みます。
// $active_nav は、このファイルをインクルードするページ側で定義されている必要があります。
?>
<html>
<body>
    </div> <footer>
        <a href="../home/home.php" class="nav-item <?php echo ($active_nav === 'ホーム' ? 'active' : ''); ?>">
            <img src="" alt="ホーム">ホーム
        </a>
        <a href="../analys/analys.php" class="nav-item <?php echo ($active_nav === '分析' ? 'active' : ''); ?>">
            <img src="" alt="分析">分析
        </a>
        <a href="../saleinput/saleinput.php" class="nav-item <?php echo ($active_nav === '入力' ? 'active' : ''); ?>">
            <img src="" alt="入力">入力
        </a>
        <a href="../setting/setting.php" class="nav-item <?php echo ($active_nav === '設定' ? 'active' : ''); ?>">
            <img src="" alt="設定">設定
        </a>
    </footer>

    <script src="../includes/script.js"></script>
</body>
</html>