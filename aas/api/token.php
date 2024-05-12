<?php
// Composer의 autoloader를 포함. 모든 의존성 패키지를 자동으로 로드한다.
require_once '../../aasLib/vendor/autoload.php';

use \Firebase\JWT\JWT;

// POST 요청 검사
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    // POST 요청이 아닌 경우 에러 메시지 반환
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Please use POST.'
    ]);
    exit; 
}

// 설정 파일 경로
$configFile = __DIR__ . '/../../aasApiConfig/config.json';

// 키 설정 파일 존재 여부 확인
if (!file_exists($configFile))
{
    die("Configuration file not found.");
}

// 설정 파일 로드
$config = json_decode(file_get_contents($configFile), true);

// JWT 설정
$jwtKey = $config['jwt']['secret_key'];
$jwtUser = $config['jwt']['user'];
$jwtPassword = $config['jwt']['password'];

// POST 데이터에서 사용자 이름과 비밀번호 추출
$username = $_POST['user'] ?? null;
$password = $_POST['password'] ?? null;

// 사용자 이름과 비밀번호 검증
if ($username === $jwtUser && $password === $jwtPassword)
{
    // 유효 시간 설정
    $issuedAt = time();
    $expirationTime = $issuedAt + (3600 * 24 * 7);  // 토큰 유효 시간: 일주일

    // 페이로드 설정
    $payload = [
        'iat' => $issuedAt,        // 토큰 발행 시간
        'exp' => $expirationTime,  // 토큰 만료 시간
        'username' => $username    // 사용자 식별 정보
    ];

    // JWT 토큰 생성
    $jwt = JWT::encode($payload, $jwtKey, 'HS256');

    // 생성된 토큰을 JSON 형태로 클라이언트에 응답
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'token' => $jwt
    ]);
}
else
{
    // 인증 실패 시
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password'
    ]);
}
