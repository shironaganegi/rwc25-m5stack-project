document.addEventListener('DOMContentLoaded', function() {
    const upperFridgeButton = document.getElementById('upper-fridge-button');
    const lowerFridgeButton = document.getElementById('lower-fridge-button');
    const upperShelfItemsSection = document.getElementById('upper-shelf-items');
    const lowerShelfItemsSection = document.getElementById('lower-shelf-items');
    const fridgeSections = document.querySelectorAll('.fridge-section');
    const searchInput = document.getElementById('search-input'); // 検索入力要素を取得
    const allFridgeItems = document.querySelectorAll('.fridge-item'); // すべてのアイテムを取得

    // --- 表示更新関数 (主に検索用) ---
    function updateDisplayForSearch() {
        const searchTerm = searchInput.value.toLowerCase(); // 検索語を取得（小文字に）
        let upperVisibleCount = 0;
        let lowerVisibleCount = 0;

        // 検索語がない場合は何もしない (棚クリック時の表示を維持)
        if (searchTerm === '') {
            // もし、どちらの棚もアクティブでない（初期状態など）なら両方非表示にする
             if (!upperFridgeButton.classList.contains('active') && !lowerFridgeButton.classList.contains('active')) {
                 upperShelfItemsSection.style.display = 'none';
                 lowerShelfItemsSection.style.display = 'none';
             }
            // アイテムの表示状態は変更しない
             allFridgeItems.forEach(item => {
                 item.style.display = 'flex'; // 念のため表示状態に戻す
             });
            return;
        }

        // --- 検索時のみ以下の処理を実行 ---

        // いったん両方の棚セクションを表示可能にする（検索結果があれば表示するため）
        upperShelfItemsSection.style.display = 'block';
        lowerShelfItemsSection.style.display = 'block';
         // 画像のアクティブ状態を解除
        fridgeSections.forEach(section => section.classList.remove('active'));


        // すべてのアイテムをループして表示/非表示を切り替え
        allFridgeItems.forEach(item => {
            const itemName = item.dataset.name.toLowerCase();
            const itemBrand = item.dataset.brand.toLowerCase();
            const itemShelf = item.dataset.shelf;
            // 検索語が空でない場合のみ、名前かブランドに部分一致するかチェック
            const isMatch = itemName.includes(searchTerm) || itemBrand.includes(searchTerm);

            if (isMatch) {
                item.style.display = 'flex'; // マッチしたら表示
                if (itemShelf === 'upper') {
                    upperVisibleCount++;
                } else if (itemShelf === 'lower') {
                    lowerVisibleCount++;
                }
            } else {
                item.style.display = 'none'; // マッチしなかったら非表示
            }
        });

        // 検索結果がない棚セクションを非表示にする
        if (upperVisibleCount === 0) {
            upperShelfItemsSection.style.display = 'none';
        }
        if (lowerVisibleCount === 0) {
            lowerShelfItemsSection.style.display = 'none';
        }
    }

    // --- イベントリスナー ---

    // 上段画像クリック時の処理
    upperFridgeButton.addEventListener('click', function() {
        searchInput.value = ''; // 検索欄をクリア
        upperShelfItemsSection.style.display = 'block'; // 上段リストを表示
        lowerShelfItemsSection.style.display = 'none';  // 下段リストを非表示
        setActiveSection(this); // クリックされたセクションをアクティブにする
        // 検索時非表示になったアイテムを表示に戻す
        allFridgeItems.forEach(item => {
            if (item.dataset.shelf === 'upper') {
                item.style.display = 'flex';
            }
        });
    });

    // 下段画像クリック時の処理
    lowerFridgeButton.addEventListener('click', function() {
        searchInput.value = ''; // 検索欄をクリア
        upperShelfItemsSection.style.display = 'none';  // 上段リストを非表示
        lowerShelfItemsSection.style.display = 'block'; // 下段リストを表示
        setActiveSection(this); // クリックされたセクションをアクティブにする
         // 検索時非表示になったアイテムを表示に戻す
        allFridgeItems.forEach(item => {
            if (item.dataset.shelf === 'lower') {
                item.style.display = 'flex';
            }
        });
    });

    // 検索入力時の処理
    searchInput.addEventListener('input', updateDisplayForSearch); // 検索用の関数を呼び出す

    // --- ヘルパー関数 ---

    // アクティブなセクションの見た目を更新する関数
    function setActiveSection(activeButton) {
        fridgeSections.forEach(section => {
            section.classList.remove('active');
        });
        if (activeButton) { // activeButtonがnullでない場合のみクラスを追加
           activeButton.classList.add('active');
        }
    }

    // --- 初期化 ---
    // 最初はどちらの棚も表示しない
    upperShelfItemsSection.style.display = 'none';
    lowerShelfItemsSection.style.display = 'none';
});