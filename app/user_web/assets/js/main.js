document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.getElementById('menuButton');
    const sideNav = document.getElementById('sideNav');
    const overlay = document.getElementById('overlay');

    // メニューボタンとオーバーレイをクリックした時の処理
    const toggleMenu = () => {
        menuButton.classList.toggle('active');
        sideNav.classList.toggle('active');
        overlay.classList.toggle('active');
    };

    menuButton.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', toggleMenu);
});