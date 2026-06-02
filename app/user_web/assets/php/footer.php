<?php
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');

function isActive($path) {
    global $requestUri;
    if ($path === '/index.php' && ($requestUri === '/' || $requestUri === '/index.php')) {
        return 'active';
    }
    if ($path !== '/index.php' && strpos($requestUri, $path) !== false) {
        return 'active';
    }
    return '';
}

// アイコンを読み込む関数
function get_svg_icon($filename) {
    $filepath = $_SERVER['DOCUMENT_ROOT'] . '/assets/image/svg/' . $filename;
    if (file_exists($filepath)) {
        return file_get_contents($filepath);
    }
    return '';
}
?>
<footer>
    <a href="/index.php" class="footer-button <?php echo isActive('/index.php'); ?>">
        <?php echo get_svg_icon('home-icon.svg'); ?>
    </a>
    <a href="/map/map.php" class="footer-button <?php echo isActive('/map/map.php'); ?>">
        <?php echo get_svg_icon('map-icon.svg'); ?>
    </a>
    <a href="/recipe/recipe.php" class="footer-button <?php echo isActive('/recipe/recipe.php'); ?>">
        <?php echo get_svg_icon('recipe-icon.svg'); ?>
    </a>
    <a href="/sale/sale.php" class="footer-button <?php echo isActive('/sale/sale.php'); ?>">
        <?php echo get_svg_icon('sale-icon.svg'); ?>
    </a>
    <a href="/refrigerator/refrigerator.php" class="footer-button <?php echo isActive('/refrigerator/refrigerator.php'); ?>">
        <?php echo get_svg_icon('balance-icon.svg'); ?>
    </a>
    <a href="/favorites/favorites.php" class="footer-button <?php echo isActive('/favorites/favorites.php'); ?>">
        <?php echo get_svg_icon('favorite-icon.svg'); // ファイル名を変更 ?>
    </a>
</footer>

<script src="/assets/js/main.js"></script>

</body>
</html>