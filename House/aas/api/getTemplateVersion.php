<?php

/*********
 * aasInsertIframe.js의 Ajax 요청에 응답하는 GET. 
 * /aas/pages/tempalte-x/index.html 파일의 최종수정시간을 반환한다.
 * 이 반환값은 Iframe의 src에 해당하는 html에 버전 파라미터로 쓰일 숫자이다. intex.html?v=xxxxxx
 *********/

// 템플릿 ID를 GET 파라미터에서 받아옴
$templateId = isset($_GET['templateId']) ? $_GET['templateId'] : null;

// 템플릿 파일 경로 설정
$templateFilePath = __DIR__ . "/../aas/banners/template-$templateId/index.html";



// 파일의 존재 여부 및 수정 시간 체크해서 존재하면 마지막 수정시간을, 존재하지 않으면 현재시간 반환.
if (file_exists($templateFilePath))
{
    $lastModified = filemtime($templateFilePath);
}
else
{
    $lastModified = time();
}

// 헤더 설정
header('Content-Type: application/json');
// 결과를 JSON 형식으로 클라이언트에 응답
echo json_encode(['version' => $lastModified]);
