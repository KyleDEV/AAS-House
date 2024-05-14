/************
 * 사이트의 광고노출 페이지에 삽입되어야할 js 파일.
 * 페이지에서  모든  광고컨테이너 <div class="aasSpace" 를 찾아 그안에 광고배너 iframe을 삽입해준다.
 *************/

document.addEventListener('DOMContentLoaded', FillAllAasSpacesWithIframes);

function FillAllAasSpacesWithIframes() {
    // 페이지에서 모든 aasSpace 요소 찾기
    var adSpaces = document.querySelectorAll('.aasSpace');

    adSpaces.forEach(function (adContainer) {
        // .aasSpace의 data-template에 맞는 경로로 src 설정. 기본값은 template-1 폴더 경로
        var templateId = adContainer.dataset.template || '1';

        // 서버에 버전 정보를 요청하는 AJAX 호출
        fetch(`/aas/api/getTemplateVersion.php?templateId=${templateId}`)
            .then(response => response.json())
            .then(data => {
                // 버전 정보를 받아 iframe의 src에 적용
                var version = data.version;
                var iframeSrc = `/aas/banners/template-${templateId}/index.html?v=${version}`;
                var iframeHTML = `<iframe src="${iframeSrc}" frameborder="0" allowfullscreen="true" style="width: 100%; height: 100%;"></iframe>`;

                // 광고 공간에 iframe 삽입
                adContainer.innerHTML = iframeHTML;
            })
            .catch(error => console.error('Failed to fetch template version:', error));
    });
}