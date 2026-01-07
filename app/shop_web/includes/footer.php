<?php
//このファイルはフッターナビゲーションとJavaScriptの読み込み、HTMLの閉じタグを含みます。
// $active_nav は、このファイルをインクルードするページ側で定義されている必要があります。
?>
    </div> <footer>
        <a href="../home/home.php" class="nav-item <?php echo ($active_nav === 'ホーム' ? 'active' : ''); ?>">
            <img src="https://via.placeholder.com/24/333333/ffffff?text=🏠" alt="ホーム">ホーム
        </a>
        <a href="../analys/analys.php" class="nav-item <?php echo ($active_nav === '分析' ? 'active' : ''); ?>">
            <img src="https://via.placeholder.com/24/333333/ffffff?text=📊" alt="分析">分析
        </a>
        <a href="../saleInput/saleInput.php" class="nav-item <?php echo ($active_nav === '入力' ? 'active' : ''); ?>">
            <img src="https://via.placeholder.com/24/333333/ffffff?text=➕" alt="入力">入力
        </a>
        <a href="../setting/setting.php" class="nav-item <?php echo ($active_nav === '設定' ? 'active' : ''); ?>">
            <img src="https://via.placeholder.com/24/333333/ffffff?text=⚙️" alt="設定">設定
        </a>
    </footer>

    <script src="../includes/script.js"></script>
</body>
</html>

