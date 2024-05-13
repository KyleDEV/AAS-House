/************
 * 사이트의 광고노출 페이지에 삽입되어야할 js 파일.
 * 페이지에서  모든  광고컨테이너 <div class="aasSpace" 를 찾아 그안에 광고배너 iframe을 삽입해준다.
 *************/

document.addEventListener('DOMContentLoaded', fillAllAasSpacesWithIframes);


function fillAllAasSpacesWithIframes() {
    // 페이지에서 모든 aasSpace 요소 찾기
    var adSpaces = document.querySelectorAll('.aasSpace');

    adSpaces.forEach(function (adContainer) {
        // .aasSpace의 data-template에 맞는 템플릿 ID로 src 설정
        var templateId = adContainer.dataset.template || '1'; // 기본값은 1

        // 새로운 iframe src 경로 생성
        var iframeSrc = `/aas/api/getBanner.php?id=${templateId}`;
        var iframeHTML = `<iframe src="${iframeSrc}" frameborder="0" allowfullscreen="true" style="width: 100%; height: 100%;"></iframe>`;

        // 광고 공간에 iframe 삽입
        adContainer.innerHTML = iframeHTML;
    });
}