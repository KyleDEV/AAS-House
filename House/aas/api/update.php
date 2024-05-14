<?php
require_once '../../aasLib/vendor/autoload.php';
require_once '../../aasApiConfig/includes/apiCommon.php';
require_once '../../aasLib/vendor/simplehtmldom/simplehtmldom/simple_html_dom.php';

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

// POST 요청 확인. POST가 아니라면 405
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => '요청 메서드가 POST가 아닙니다 (Invalid request method. Please use POST).'], JSON_UNESCAPED_UNICODE);
    exit;
}

// config.json 설정 로드 및 확인
$config = loadConfig();
if (!$config)
{
    exit;
}

// 요청 헤더에서 JWT 토큰을 가져와 secret_key 와 비교 인증
$headers = getallheaders();
$jwt = $headers['Authorization'] ?? '';
$jwt = str_replace('Bearer ', '', $jwt); // Bearer 접두어 제거
$jwtKey = $config['jwt']['secret_key'];
try
{
    $decoded = JWT::decode($jwt, new Key($jwtKey, 'HS256'));
}
catch (Exception $e)
{
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized: 유효하지 않은 JWT 토큰 (Invalid JWT token).'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 인증 성공

// HTML과 CSS 데이터 처리
$input = json_decode(file_get_contents('php://input'), true);
$htmlData = $input['html'] ?? '';
$cssData = $input['css'] ?? '';
$bannerId = $input['bannerId'] ?? '';

// HTML 데이터 확인 (디버깅용)
error_log("HTML Data: " . $htmlData);

// <body> 태그 추가
$htmlDataWithBody = "<body>$htmlData</body>";

// 페이지 업데이트 
$updateResult = updatePageContent($bannerId, $htmlDataWithBody, $cssData);

if ($updateResult)
{
    echo json_encode(['success' => true, 'message' => '배너가 업데이트 되었습니다(Content updated successfully).'], JSON_UNESCAPED_UNICODE);
}
else
{
    echo json_encode(['success' => false, 'message' => '실패!(Failed to update content).'], JSON_UNESCAPED_UNICODE);
}

// 페이지 업데이트 함수
function updatePageContent($bannerId, $htmlData, $cssData)
{
    EmptyTempDir();
    $timestamp = time(); // CSS와 HTML 쿼리스트링으로 붙일 버전 번호

    $htmlTemplateFilePath = ADS_TEMPLATES_DIR . "template-$bannerId/template-$bannerId.html";

    // HTML 템플릿 파일 로드
    $templateHtmlDoc = file_get_html($htmlTemplateFilePath);
    if (!$templateHtmlDoc)
    {
        error_log("Failed to load HTML template file: $htmlTemplateFilePath");
        return false;
    }

    // HTML 콘텐츠와 CSS 콘텐츠의 JS 사용키워드 제거
    $sanitizedHtml = sanitizeHtml($htmlData);
    $sanitizedCss = sanitizeCSS($cssData);

    // HTML 파싱 확인 (디버깅용)
    error_log("Sanitized HTML Data: " . $sanitizedHtml);

    // <body> 내용 교체
    $newDocFromData = str_get_html($sanitizedHtml);
    if (!$newDocFromData)
    {
        error_log("Failed to parse sanitized HTML data");
        return false;
    }

    $newBodyFromData = $newDocFromData->find('body', 0);
    if (!$newBodyFromData)
    {
        error_log("Failed to find body in new HTML data");
        return false;
    }

    $body = $templateHtmlDoc->find('body', 0);
    if ($body)
    {
        $body->innertext = $newBodyFromData->innertext;
    }
    else
    {
        error_log("Failed to find body in template HTML");
        return false;
    }

    // <head>와 <body>에 각각 CSS,JS 링크 추가
    AppendCssLinkToHtmlHead($bannerId, $timestamp, $templateHtmlDoc);
    AppendJsLinkToHtmlBody($bannerId, $timestamp, $templateHtmlDoc, $body);

    // CSS와 JS 파일 저장 및 HTML 파일 저장
    SaveCssToTemp($bannerId, $sanitizedCss);
    copyJsFileToTemp($bannerId);
    SaveHtmlToTemp($templateHtmlDoc);

    // 목적지 폴더 비우기 및 임시 폴더의 모든 내용을 목적지 폴더로 이동
    EmptyDestinationDir($bannerId);
    MoveTempToDestination($bannerId);
    EmptyTempDir();

    return true;
}

function EmptyTempDir()
{
    array_map('unlink', glob(TEMP_DIR . "*"));
}

function EmptyDestinationDir($bannerId)
{
    $files = glob(BANNERS_DIR . "template-$bannerId/*");
    foreach ($files as $file)
    {
        unlink($file);
    }
}

function AppendCssLinkToHtmlHead($bannerId, $timestamp, $doc)
{
    $head = $doc->find('head', 0);
    if ($head)
    {
        $styleLink = $doc->createElement('link');
        $styleLink->setAttribute('rel', 'stylesheet');
        $styleLink->setAttribute('type', 'text/css');
        $styleLink->setAttribute('href', "templateStyle-$bannerId.css?v=$timestamp");
        $head->appendChild($styleLink);
    }
}

function SaveCssToTemp($bannerId, $sanitizedCss)
{
    $cssFileTempPath = TEMP_DIR . "templateStyle-$bannerId.css";

    // temp폴더에 CSS 파일 생성
    file_put_contents($cssFileTempPath, $sanitizedCss);
}

function SaveHtmlToTemp($doc)
{
    $doc->save(TEMP_DIR . "index.html");
}

function MoveTempToDestination($bannerId)
{
    $sourceFiles = glob(TEMP_DIR . '*');
    foreach ($sourceFiles as $file)
    {
        $destPath = BANNERS_DIR . "template-$bannerId/" . basename($file);
        if (!file_exists(dirname($destPath)))
        {
            mkdir(dirname($destPath), 0777, true);
        }
        rename($file, $destPath);
    }
}

function copyJsFileToTemp($bannerId)
{
    $jsFilePath = ADS_TEMPLATES_DIR . "template-$bannerId/template-$bannerId.js";
    $jsFileTempPath = TEMP_DIR . "template-$bannerId.js";

    if (file_exists($jsFilePath))
    {
        copy($jsFilePath, $jsFileTempPath);
    }
}

function AppendJsLinkToHtmlBody($bannerId, $timestamp, $doc, $body)
{
    $scriptLink = $doc->createElement('script');
    $scriptLink->setAttribute('src', "template-$bannerId.js?v=$timestamp");
    $body->appendChild($scriptLink);
}

/** JS 태그와 onClick()함수등을 제거한다. */
function sanitizeHtml($htmlData)
{
    // <script> 태그 및 그 내용 제거
    $SanitizedContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $htmlData);
    // 위험할 수 있는 HTML 속성 제거 (onclick, onerror 등)
    $SanitizedContent = preg_replace('/ on\w+="[^"]*"/', '', $SanitizedContent);
    $SanitizedContent = preg_replace('/ on\w+=\'[^\']*\'/', '', $SanitizedContent);
    return $SanitizedContent;
}

/** \<script\>와 같은 HTML 태그나 javascript: 와 같은 프로토콜을 사용한 URL이 포함되지 않도록 필터링  */
function sanitizeCSS($cssData)
{
    // URL 사용 제한 (옵션에 따라)
    $sanitizedContent = preg_replace('/url\([^)]*\)/i', '', $cssData);
    // 위험한 속성 및 키워드 제거
    $sanitizedContent = preg_replace('/expression\(|javascript:/i', '', $sanitizedContent);
    return $sanitizedContent;
}
