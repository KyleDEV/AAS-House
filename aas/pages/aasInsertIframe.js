document.addEventListener('DOMContentLoaded', function () {
    // 페이지에서 모든 aasSpace 요소 찾기
    var adSpaces = document.querySelectorAll('.aasSpace');

    adSpaces.forEach(function (adContainer) {
        // .aasSpace의 data-template에 맞는 경로로 src 세팅. 기본값은 template-1 폴더경로
        var template = adContainer.dataset.template || 'template-1';
        var iframeSrc = `/aas/pages/${template}/index.html`;

        // iframe HTML 문자열 생성
        var iframeHTML = `<iframe src="${iframeSrc}" frameborder="0" allowfullscreen="true" style="width: 100%; height: 100%;"></iframe>`;

        // 광고 공간에 iframe 삽입
        adContainer.innerHTML = iframeHTML;
    });
});
