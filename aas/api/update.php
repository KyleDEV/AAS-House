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

// //TODO: id에 맞는 폴더/파일/템플릿파일 있는지 확인하고 없으면 예외/응답처리
function updatePageContent($bannerId, $htmlContent, $cssContent)
{
    $tempDir = __DIR__ . "/../temp/";
    $adsTemplatesDir = __DIR__ . "/../../aasApiConfig/templates/";
    $desPagesDir = __DIR__ . "/../pages/";
    EmptyTempDir($tempDir);
    $timestamp = time(); // CSS와 HTML 쿼리스트링으로 붙일 버전 번호

    $htmlTemplateFilePath = $adsTemplatesDir . "template-$bannerId/template-$bannerId.html";

    // HTML 파일 로드
    $doc = new DOMDocument();
    @$doc->loadHTMLFile($htmlTemplateFilePath, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // HTML 콘텐츠와 CSS 콘텐츠의 JS 제거
    $sanitizedHtml = sanitizeContent($htmlContent);
    $sanitizedCss = sanitizeContent($cssContent);

    // <head>와 <body>에 각각 CSS,JS 링크 추가
    AppendCssLinkToHtmlHead($bannerId, $timestamp, $doc);
    AppendJsLinkToHtmlBody($bannerId, $timestamp, $doc, $doc->getElementsByTagName('body')->item(0));

    // <body> 내용 교체
    $newDoc = new DOMDocument();
    @$newDoc->loadHTML(mb_convert_encoding($sanitizedHtml, 'HTML-ENTITIES', 'UTF-8'));
    $newBody = $newDoc->getElementsByTagName('body')->item(0);

    $body = $doc->getElementsByTagName('body')->item(0);
    foreach ($newBody->childNodes as $node)
    {
        $node = $doc->importNode($node, true);
        $body->appendChild($node);
    }

    // CSS와 JS 파일 저장 및 HTML 파일 저장
    SaveCssToTemp($bannerId, $sanitizedCss, $tempDir);
    copyJsFileToTemp($bannerId, $adsTemplatesDir, $tempDir);
    SaveHtmlToTemp($doc, $tempDir);

    // 목적지 폴더 비우기 및 임시 폴더의 모든 내용을 목적지 폴더로 이동
    EmptyDestinationDir($bannerId, $desPagesDir);
    MoveTempToDestination($tempDir, $desPagesDir, $bannerId);
    EmptyTempDir($tempDir);

    return true;
}











function EmptyTempDir($tempDir)
{
    array_map('unlink', glob($tempDir . "*"));
}

function EmptyDestinationDir($bannerId, $desPagesDir)
{
    $files = glob($desPagesDir . "template-$bannerId/*");
    foreach ($files as $file)
    {
        unlink($file);
    }
}

function AppendCssLinkToHtmlHead($bannerId, $timestamp, $doc)
{
    $head = $doc->getElementsByTagName('head')->item(0);
    $styleLink = $doc->createElement('link');
    $styleLink->setAttribute('rel', 'stylesheet');
    $styleLink->setAttribute('type', 'text/css');
    $styleLink->setAttribute('href', "templateStyle-$bannerId.css?v=$timestamp");
    $head->appendChild($styleLink);
}

function SaveCssToTemp($bannerId, $sanitizedCss, $tempDir)
{
    $cssFileTempPath = $tempDir . "templateStyle-$bannerId.css";

    // temp폴더에 CSS 파일 생성
    file_put_contents($cssFileTempPath, $sanitizedCss);
}

function SaveHtmlToTemp($doc, $tempDir)
{
    $doc->saveHTMLFile($tempDir . "index.html");
}

function MoveTempToDestination($tempDir, $desPagesDir, $bannerId)
{
    $sourceFiles = glob($tempDir . '*');
    foreach ($sourceFiles as $file)
    {
        $destPath = $desPagesDir . "template-$bannerId/" . basename($file);
        if (!file_exists(dirname($destPath)))
        {
            mkdir(dirname($destPath), 0777, true);
        }
        rename($file, $destPath);
    }
}


// JavaScript 파일을 Temp 디렉토리로 복사하는 함수
function copyJsFileToTemp($bannerId, $adsTemplatesDir, $tempDir)
{
    $jsFilePath = $adsTemplatesDir . "template-$bannerId/template-$bannerId.js";
    $jsFileTempPath = $tempDir . "template-$bannerId.js";

    if (file_exists($jsFilePath))
    {
        copy($jsFilePath, $jsFileTempPath);
    }
}

// HTML 문서에 JavaScript 링크를 추가하는 함수
function AppendJsLinkToHtmlBody($bannerId, $timestamp, $doc, $body)
{
    $scriptLink = $doc->createElement('script');
    $scriptLink->setAttribute('src', "template-$bannerId.js?v=$timestamp");
    $body->appendChild($scriptLink);
}

function sanitizeContent($content)
{
    // JS 코드 제거
    //TODO: 테스트 필요
    return preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content);
}
