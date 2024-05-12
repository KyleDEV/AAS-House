<?php
require_once '../../aasLib/vendor/autoload.php';
//TODO: 응답코드. 분류별 에러코드 응답하도록 변경하기? http 응답코드로 충분하나?
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

//TODO: id에 맞는 폴더/파일/템플릿파일 있는지 확인하고 없으면 예외/응답처리
function updatePageContent($bannerId, $htmlContent, $cssContent)
{
    $timestamp = time(); // CSS와 HTML 쿼리스트링으로 붙일 버전 번호

    // 초기화: temp 폴더 비우기
    array_map('unlink', glob(__DIR__ . "/../temp/*"));

    // 템플릿 및 자원 파일 경로 설정
    $templatePath = __DIR__ . "/../../aasApiConfig/templates/template-$bannerId/template-$bannerId.html";
    $cssFilePath = __DIR__ . "/../temp/style-$bannerId.css";
    $jsFilePath = __DIR__ . "/../../aasApiConfig/templates/template-$bannerId/template-$bannerId.js";
    $tempJsFilePath = __DIR__ . "/../temp/template-$bannerId.js"; // temp 경로에 저장될 JS 파일 경로

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
    $styleLink->setAttribute('href', "style-$bannerId.css?v=$timestamp");
    $head->appendChild($styleLink);

    // <body> 내용 교체
    $body = $doc->getElementsByTagName('body')->item(0);
    while ($body->hasChildNodes())
    {
        $body->removeChild($body->firstChild);
    }
    $fragment = $doc->createDocumentFragment();
    $fragment->appendXML($sanitizedHtml);
    $body->appendChild($fragment);

    // JavaScript 파일이 있는 경우, temp에 복사하고 <body> 끝에 추가
    if (file_exists($jsFilePath))
    {
        copy($jsFilePath, $tempJsFilePath); // 원본 JS 파일을 temp 경로로 복사
        $scriptLink = $doc->createElement('script');
        $scriptLink->setAttribute('src', "template-$bannerId.js?v=$timestamp");
        $body->appendChild($scriptLink);
    }

    // 임시 HTML 파일 저장
    $doc->saveHTMLFile(__DIR__ . "/../temp/index.html");

    // 기존 폴더 비우기
    $files = glob(__DIR__ . "/../pages/template-$bannerId/*");
    foreach ($files as $file)
    {
        unlink($file);
    }

    // 파일을 최종 위치로 이동
    rename(__DIR__ . "/../temp/index.html", __DIR__ . "/../pages/template-$bannerId/index.html");
    rename($cssFilePath, __DIR__ . "/../pages/template-$bannerId/style-$bannerId.css");
    if (file_exists($tempJsFilePath))
    {
        rename($tempJsFilePath, __DIR__ . "/../pages/template-$bannerId/template-$bannerId.js");
    }

    // temp 폴더 비우기
    array_map('unlink', glob(__DIR__ . "/../temp/*"));

    return true;
}



function sanitizeContent($content)
{
    // JS 코드 제거
    //TODO: 테스트 필요
    return preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content);
}
