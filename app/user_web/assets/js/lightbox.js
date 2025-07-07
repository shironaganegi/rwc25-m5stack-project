document.addEventListener('DOMContentLoaded', () => {
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxClose = document.getElementById('lightboxClose');
        const images = document.querySelectorAll('.enlargeable-image');
        let pinchZoomInstance = null;

        // 画像をクリックしたときの処理
        images.forEach(image => {
            image.addEventListener('click', (e) => {
                lightboxImage.src = e.target.src;
                lightbox.classList.add('active');

                if (pinchZoomInstance) {
                    pinchZoomInstance.update();
                } else {
                    pinchZoomInstance = new PinchZoom.default(lightboxImage, {
                        minZoom: 1,
                        maxZoom: 4,
                    });
                }
            });
        });

        // 閉じる処理をまとめた関数
        const closeLightbox = () => {
            lightbox.classList.remove('active');
            if (pinchZoomInstance) {
                pinchZoomInstance.setZoom({ zoom: 1, x: 0, y: 0 });
            }
        };

        // ×ボタンをクリックした時だけ閉じる
        lightboxClose.addEventListener('click', closeLightbox);
    });