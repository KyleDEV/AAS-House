/*****
 * 광고서버와 미리 약속된 template-1 광고배너에 필요한 js 예제.
 */


window.onload = function () {
    startAutoSlide(); // 슬라이드 자동 넘김
    attachEventListenersOnBtns();  // 슬라이드 넘김 버튼 이벤트 부여
}

let lognBnIndex = 0;

function attachEventListenersOnBtns() {
    const prevButton = document.querySelector('.carousel-control.prev');
    const nextButton = document.querySelector('.carousel-control.next');

    prevButton.addEventListener('click', function () {
        NextSlide(lognBnIndex - 1);
    });

    nextButton.addEventListener('click', function () {
        NextSlide(lognBnIndex + 1);
    });
}

function NextSlide(index) {
    const slider = document.querySelector('.carousel-inner');
    const numLongSlides = document.querySelectorAll('.carousel-item').length;
    lognBnIndex = index % numLongSlides;
    slider.style.transform = `translateX(-${lognBnIndex * 100}%)`;
}

function startAutoSlide() {
    setInterval(function () {
        NextSlide(lognBnIndex + 1);
    }, 4000); // 4초 마다 자동 슬라이드
}
