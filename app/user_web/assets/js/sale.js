document.addEventListener('DOMContentLoaded', function() {
    // すべてのスーパーマーケットヘッダーを取得
    const storeHeaders = document.querySelectorAll('.sale-store-header');

    storeHeaders.forEach(header => {
        header.addEventListener('click', function() {
            // クリックされたヘッダーに 'active' クラスをトグル
            this.classList.toggle('active');

            // 対応する商品パネルを取得 (ヘッダーの次の要素)
            const panel = this.nextElementSibling;

            // パネルのmax-heightをセットして開閉アニメーション
            if (panel.style.maxHeight) {
                // パネルが開いていれば閉じる
                panel.style.maxHeight = null;
            } else {
                // パネルが閉じていれば開く
                // scrollHeightは要素の内容物全体の高さ
                panel.style.maxHeight = panel.scrollHeight + "px";
            }
        });
    });
});