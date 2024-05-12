/*****
 * 광고서버와 미리 약속된 template-1 광고배너에 필요한 js 예제.
 */

window.onload = function () {
    startAutoSlide();
}

let lognBnIndex = 0;
let squareBnIndex = 0;

function showLongSlide(index) {
    const slider = document.querySelector('.carousel-inner');
    const numLongSlides = document.querySelectorAll('.carousel-item').length; // 긴 배너 슬라이드 개수 동적 계산
    lognBnIndex = index % numLongSlides; // 동적으로 계산된 슬라이드 개수로 나눈 나머지로 설정
    slider.style.transform = `translateX(-${lognBnIndex * 100}%)`;
}

function startAutoSlide() {
    setInterval(function () {
        showLongSlide(lognBnIndex + 1); // 현재 인덱스에서 1을 더해 다음 슬라이드로 이동
    }, 4000); // 4초 마다 슬라이드가 자동으로 넘어갑니다.
}


