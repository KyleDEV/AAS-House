<?php
require_once '../../aasLib/vendor/autoload.php';

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

// POST 요청 확인. POST가 아니라면 405
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Please use POST.']);
    exit;
}

// 설정 파일 경로
$configFile = __DIR__ . '/../../aasApiConfig/config.json';

// 설정 파일 로드
if (!file_exists($configFile))
{
    die("Configuration file not found.");
}
$config = json_decode(file_get_contents($configFile), true);

// JWT 설정
$jwtKey = $config['jwt']['secret_key'];
$headers = getallheaders();
$jwt = $headers['Authorization'] ?? '';
$jwt = str_replace('Bearer ', '', $jwt); // Bearer 접두어 제거

// JWT 검증
try
{
    $decoded = JWT::decode($jwt, new Key($jwtKey, 'HS256'));
}
catch (Exception $e)
{
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Invalid JWT token.']);
    exit;
}

// 인증 성공



// HTML과 CSS 데이터 처리
$input = json_decode(file_get_contents('php://input'), true);
$htmlContent = $input['html'] ?? '';
$cssContent = $input['css'] ?? '';
$bannerId = $input['bannerId'] ?? '';
//JSON 데이터 예시
// {
//   "html": "<div style='background-color: #f0f0f0; text-align: center; padding: 20px;'>Welcome to our Website!</div>",
//   "css": "div { border: 1px solid #000; padding: 10px; }",
//   "bannerId": "banner123"
// }


// 페이지 업데이트 
$updateResult = updatePageContent($bannerId, $htmlContent, $cssContent);

if ($updateResult)
{
    echo json_encode(['success' => true, 'message' => 'Content updated successfully.']);
}
else
{
    echo json_encode(['success' => false, 'message' => 'Failed to update content.']);
}

function updatePageContent($bannerId, $htmlContent, $cssContent)
{
    // 초기화: temp 폴더 비우기
    array_map('unlink', glob(__DIR__ . "/../temp/*"));

    // 경로 설정
    $templatePath = __DIR__ . "/../../aasApiConfig/templates/template-$bannerId.html";
    $cssFilePath = __DIR__ . "/../temp/style-$bannerId.css";

    // HTML 콘텐츠와 CSS 콘텐츠의 JS 제거
    $sanitizedHtml = sanitizeContent($htmlContent);
    $sanitizedCss = sanitizeContent($cssContent);

    // CSS 파일 생성
    file_put_contents($cssFilePath, $sanitizedCss);

    // HTML 파일 로드
    $doc = new DOMDocument();
    @$doc->loadHTMLFile($templatePath, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // <head>에 CSS 링크 추가
    $head = $doc->getElementsByTagName('head')->item(0);
    $styleLink = $doc->createElement('link');
    $styleLink->setAttribute('rel', 'stylesheet');
    $styleLink->setAttribute('type', 'text/css');
    $styleLink->setAttribute('href', "style-$bannerId.css");
    $head->appendChild($styleLink);

    // <body> 내용 교체
    $body = $doc->getElementsByTagName('body')->item(0);
    while ($body->hasChildNodes())
    {
        $body->removeChild($body->firstChild);
    }
    $fragment = $doc->createDocumentFragment();
    $fragment->appendXML($sanitizedHtml); // HTML 문자열을 프래그먼트에 추가
    $body->appendChild($fragment); // 프래그먼트를 <body>에 추가

    // 임시 HTML 파일 저장
    $doc->saveHTMLFile(__DIR__ . "/../temp/index.html");

    // 기존 폴더 비우기 (JS 파일 제외)
    $files = glob(__DIR__ . "/../template-$bannerId/*");
    foreach ($files as $file)
    {
        if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'js')
        {
            unlink($file);
        }
    }

    // 파일을 최종 위치로 이동
    rename(__DIR__ . "/../temp/index.html", __DIR__ . "/../template-$bannerId/index.html");
    rename($cssFilePath, __DIR__ . "/../template-$bannerId/style-$bannerId.css");

    // temp 폴더 비우기
    array_map('unlink', glob(__DIR__ . "/../temp/*"));

    return true;
}


function sanitizeContent($content)
{
    // JS 코드 제거 (간단한 예시)
    return preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content);
}
