<?php
require_once '../../aasApiConfig/includes/apiCommon.php';

// 요청된 ID를 바탕으로 특정 HTML 파일의 내용을 반환하는 PHP 스크립트
if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    // 'id' 쿼리 파라미터로부터 템플릿 ID를 가져옵니다.
    $templateId = isset($_GET['id']) ? $_GET['id'] : 'default';

    // 정의된 템플릿 디렉토리에서 해당 ID에 맞는 HTML 파일의 경로를 설정합니다.
    $filePath = BANNERS_DIR . "template-$templateId/index.html";


    // 파일이 존재하는지 확인하고, 있다면 내용을 반환합니다.
    if (file_exists($filePath))
    {
        $content = file_get_contents($filePath);
        header('Content-Type: text/html');
        echo $content;
    }
    else
    {
        // 파일이 없을 경우 404 에러를 반환
        http_response_code(404);
        echo "File not found. 광고배너 파일이 없어요!";
    }
    exit;
}
