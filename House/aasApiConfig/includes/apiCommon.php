<?php
define('ROOT_DIR', realpath(__DIR__ . '/../../') . '/');
/** config.json이 저장되어있는 위치 (the dir path of config.json file) */
define('CONFIG_DIR', ROOT_DIR . 'aasApiConfig/');
define('ADS_TEMPLATES_DIR', ROOT_DIR . 'aasApiConfig/templates/');
define('BANNERS_DIR', ROOT_DIR . 'aas/banners/');
define('TEMP_DIR', ROOT_DIR . 'aasApiConfig/temp/');

/**
 * config.json 파일을 로드하고 응답한다. 파일이 없다면 500에러 응답 후 종료 할 뿐 반환하지 않는다.
 * @return mixed config.json이  존재한다면 디코딩된 json 반환.
 */
function loadConfig()
{
    $configFile = CONFIG_DIR . 'config.json';
    // 파링일 없다면 HTTP 상태 코드 응답 (HTTP status)
    if (!file_exists($configFile))
    {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '서버에 config.json이 없습니다 (config.json file not found). '
        ], JSON_UNESCAPED_UNICODE); // 한글깨짐 방지. 비-ASCII 문자를 이스케이프하지 않음
        exit;
    }

    return json_decode(file_get_contents($configFile), true);
}
