// script.js

document.addEventListener("DOMContentLoaded", function() {
    const hamburgerMenu = document.getElementById("hamburgerMenu");
    const sideNavigation = document.getElementById("sideNavigation");
    const overlay = document.getElementById("overlay");

    function toggleMenu() {
        // ハンバーガーメニューとサイドメニューの開閉をトグル
        hamburgerMenu.classList.toggle("open");
        sideNavigation.classList.toggle("open");
        overlay.classList.toggle("active");
        
        // メニューが開いているときはボディのスクロールを停止
        if (sideNavigation.classList.contains("open")) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }

    // 各要素にイベントリスナーを設定
    if (hamburgerMenu) {
        hamburgerMenu.addEventListener("click", toggleMenu);
    }
    if (overlay) {
        overlay.addEventListener("click", toggleMenu);
    }
});