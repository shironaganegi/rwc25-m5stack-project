<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スーパーマーケット アプリ トップ</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/index.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <section class="slider-section">
            <div class="slider-container" id="sliderContainer">
                <div class="slide">
                    <img src="/assets/image/tirasi.png" alt="チラシ" class="enlargeable-image">
                    <p>スーパー髙橋 今週のお買い得品</p>
                </div>
                <div class="slide">
                    <img src="/assets/image/tirasi.png" alt="チラシ" class="enlargeable-image">
                    <p>週末限定！生鮮大特価市</p>
                </div>
                <div class="slide">
                    <img src="/assets/image/tirasi.png" alt="チラシ" class="enlargeable-image">
                    <p>お弁当・お惣菜まつり</p>
                </div>
            </div>
            <div class="slider-dots" id="sliderDots"></div>
        </section>

        <hr class="separator">

        <section class="featured-section">
            <h2 class="featured-title">今日の注目情報</h2>
            <img src="/assets/image/tirasi.png" alt="チラシ" class="featured-image enlargeable-image">
        </section>
        
      <div class="lightbox" id="lightbox">
            <button class="lightbox-close" id="lightboxClose">&times;</button>
            <div class="lightbox-content">
                <img src="" alt="拡大画像" id="lightboxImage">
            </div>
        </div>

    </main>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

    <script src="/assets/js/slider.js"></script>
    <script src="/assets/js/lightbox.js"></script>
</body>
</html>