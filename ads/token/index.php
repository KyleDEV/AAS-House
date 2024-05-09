<?php
//Composer의 autoloader를 포함.모든 의존성 패키지를 자동으로 로드한다.
require_once '../../adsLib/vendor/autoload.php';
use \Firebase\JWT\JWT;

// 설정 파일 경로
$configFile = __DIR__ . '../../adsApiConfig/config.json';

// 키 설정 파일 존재 여부 확인
if (!file_exists($configFile)) {
    die("Configuration file not found.");
}

// 설정 파일 로드
$config = json_decode(file_get_contents($configFile), true);

// JWT 설정
$jwtKey = $config['jwt']['secret_key'];
$jwtUser = $config['jwt']['user'];
$jwtPassword = $config['jwt']['password'];


?>