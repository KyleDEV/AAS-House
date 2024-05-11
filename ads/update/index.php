<?php
require_once '../../adsLib/vendor/autoload.php';

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
$configFile = __DIR__ . '/../../adsApiConfig/config.json';

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

// 인증 성공 응답 테스트:
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'JWT token is valid.', 'data' => $decoded]);

?>