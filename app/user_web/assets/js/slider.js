document.addEventListener('DOMContentLoaded', () => {
    const sliderSection = document.querySelector('.slider-section');
    if (!sliderSection) return; // スライダーがなければ何もしない

    const sliderContainer = document.getElementById('sliderContainer');
    const slides = document.querySelectorAll('.slide');
    const dotsContainer = document.getElementById('sliderDots');
    const slideCount = slides.length;
    let currentIndex = 0;
    let startX = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;
    let isDragging = false;
    let autoSlideInterval;
    let slideWidth = sliderSection.clientWidth; // ★スライダー要素の幅を取得

    // ナビゲーションドットを生成
    for (let i = 0; i < slideCount; i++) {
        const dot = document.createElement('div');
        dot.classList.add('dot');
        dot.addEventListener('click', () => {
            goToSlide(i);
        });
        dotsContainer.appendChild(dot);
    }
    const dots = document.querySelectorAll('.dot');

    // 指定したスライドに移動する関数
    function goToSlide(index) {
        if (isDragging) return;

        // ループ処理
        if (index < 0) {
            index = slideCount - 1;
        } else if (index >= slideCount) {
            index = 0;
        }
        
        sliderContainer.style.transition = 'transform 0.5s ease-out';
        currentTranslate = -index * slideWidth; // ★ウィンドウ幅ではなく要素の幅で計算
        sliderContainer.style.transform = `translateX(${currentTranslate}px)`;
        currentIndex = index;
        updateDots();
        resetAutoSlide();
    }

    // ドットのアクティブ状態を更新
    function updateDots() {
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }

    // 自動スライドを開始/リセットする関数
    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            goToSlide(currentIndex + 1);
        }, 5000);
    }

    function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        startAutoSlide();
    }

    // ドラッグ開始
    function dragStart(event) {
        isDragging = true;
        startX = getPositionX(event);
        clearInterval(autoSlideInterval);
        sliderContainer.style.transition = 'none';
        prevTranslate = currentTranslate;
    }

    // ドラッグ中
    function drag(event) {
        if (!isDragging) return;
        const currentPosition = getPositionX(event);
        const moveX = currentPosition - startX;
        currentTranslate = prevTranslate + moveX;
        sliderContainer.style.transform = `translateX(${currentTranslate}px)`;
    }

    // ドラッグ終了
    function dragEnd() {
        if (!isDragging) return;
        isDragging = false;
        const movedBy = currentTranslate - prevTranslate;

        if (movedBy < -50 && currentIndex < slideCount - 1) {
            currentIndex++;
        } else if (movedBy > 50 && currentIndex > 0) {
            currentIndex--;
        }
        
        goToSlide(currentIndex);
    }

    // ★ウィンドウサイズ変更時の処理を追加
    function handleResize() {
        slideWidth = sliderSection.clientWidth;
        sliderContainer.style.transition = 'none'; // リサイズ中はアニメーションをオフ
        currentTranslate = -currentIndex * slideWidth;
        sliderContainer.style.transform = `translateX(${currentTranslate}px)`;
    }

    function getPositionX(event) {
        return event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
    }

    // イベントリスナーを設定
    sliderContainer.addEventListener('mousedown', dragStart);
    sliderContainer.addEventListener('touchstart', dragStart, { passive: true });
    
    document.addEventListener('mousemove', drag);
    document.addEventListener('touchmove', drag, { passive: true });

    document.addEventListener('mouseup', dragEnd);
    document.addEventListener('touchend', dragEnd);
    
    window.addEventListener('resize', handleResize);

    // 初期化
    updateDots();
    startAutoSlide();
});