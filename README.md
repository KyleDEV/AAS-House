# AAS House - The house of AdAng System
광고를 노출하는 웹사이트 쪽(House)의 PHP 구현코드입니다. AAS는 자바스크립트 위험요소가 제거된 광고서버 운용을 위함입니다.

## 필요 디렉토리와 파일
`House`폴더에는 아래의 세 디렉토리가 있습니다. 라이브서버에는 다음 디렉토리와 그 하위 파일들을 root에 포함해야합니다.

* aas - 공개되어야할 디렉토리
* aasApiConfig - private key가 있는 설정파일 포함 (비공개)
* aasLib -  PHP 라이브러리 관리 폴더 (비공개)

디렉토리를 변경하거나 다른 경로를 사용하려면 다음위치의 코드를 수정해 사용하십시오. 
* `aasApiConfig/includes/apiCommon.php`에서 DIR에 대한 define() 코드
* `aasExample/aasInsertIframe.js` 의 `$iframeSrc` 경로
* `aas/api` 디렉토리 안 php파일들의 `require_once` 경로

## 써드파티 라이브러리 설치사용 안내
써드파티 PHP 라이브러리를 사용중입니다. 
* firebase/JWT 

adsLib/ 폴더안에는 composer, firebase, autoload.php 등이 필요합니다. 다음 방법으로 설치가능합니다.

### A) php composer로 설치 및 관리:
터미널에서 cd 명령어로 adsLib 폴더안으로 이동해 아래의 composer 명령어를 입력하세요. 

`composer install`

개발에 사용된 버전 (composer.lock 파일에 명시된 패키지와 그 버전) 그대로 설치할 수 있습니다.

### B) 직접 설치:
동일한 버전을 사용하기위해서 개발자에게 직접 zip 파일을 받아 composer, autoload.php까지 함게 받으십시오. autoload 없이 JWT 공식 라이브러리를 직접 다운받아 설치하여 사용하려면 aas/api/token.php와 update.php 의 require_once 코드를 수정해야합니다:
~~~
require_once '../../path/to/firebase/php-jwt/src/JWT.php';
require_once '../../path/to/firebase/php-jwt/src/Key.php';
~~~

## 중요! 접근제한 설정
adsApiConfig, adsLib 디렉토리의 파일은 웹사이트 방문자가 접근할 수 없어야 합니다. 웹서버 설정을 확인하세요.(Apache 일 경우 이미 각 디렉토리에 .htaccess 파일과 'Deny from all' 설정이 되어있습니다.)

## API 시크릿 설정
adsApiConfig/config.sample.json 파일에 양식이 있습니다. 이 양식대로 같은 폴더에 config.json 파일을 별도로 생성해야합니다. 이 파일 내용에 JWT토큰발급을 위한 key와 요청 user/비밀번호를 작성해 사용해야합니다.

* config.sample.json 양식은 git 추적이 됩니다. 이 파일에 비밀정보를 넣지 마십시오. 
* config.json은 .gitignore에 명시하여 추적되지 않게 되어있습니다. user/password와 JWT secret key가 외부에 노출되지 않도록 해야합니다.

## API Endpoints
다음 두 엔드포인트를 사용할 수 있습니다.
* /aas/api/token.php (토큰 발급 엔드포인트)
* /aas/api/update.php (광고배너 업데이트 요청 엔드포인트)

요청측(광고서버)에서는 사전에 발급받은 user,password를 통해 token.php로 JWT 토큰을 받아야합니다. 이 토큰은 유효기간이 있습니다.

수신측이 노출중인 광고위젯의 광고배너를 변경하려면 사전에 정해진 광고(HTML/CSS) 템플릿을 JSON과 함께 요청측이 /aas/api/update.php 엔드포인트에 요청합니다.

### 요청측(광고서버) API 사용 프로세스
* 1) JWT 퍼블릭 토큰 요청:
    * 요청자는  /aas/api/token.php 주소로 user,password 와 함께 POST 요청하면 JWT 토큰을 받을 수 있습니다.  이 토큰은 유효기간이 있습니다.
* 2) /aas/api/update.php 주소로 JWT 토큰과 함께  사전에 정해진 광고(HTML/CSS) 템플릿을 JSON 데이터로 전송하면 House의 노출페이지의 광고배너 변경이 즉시 반영됩니다.

      #### JSON 포맷 예시:
      ~~~
      {
        "bannerId": "banner123",
        "html": "<div>Welcome to our Website!</div>",
        "css": "div { border: 1px solid #000; padding: 10px; }"
      }
      ~~~





## templateDesign 폴더
templateDesign 폴더는 서버에 사용되지 않는 폴더입니다. 광고배너를 디자인하고 테스트하는데 사용하기위한 단순 참고 파일입니다. 

bannerTemplate-1 의 예시파일이 첨부되어있습니다. html/css/js 파일을 포함하여 배너 템플릿을 디자인하고 출력해보는데 사용하십시오.

jsonConverter.html 파일을 실행해서 API 엔드포인트에 요청하기위한 JSON 데이터를 만들 수 있습니다. jsonConverter.html을 웹브라우저로 열고
1)  `bannerID`에는 광고배너 템플릿 ID를, 
2) HTML 입력부분에는 `<body>` 에 들어갈 DOM Element를, 
3) CSS 입력칸에는 Style Sheet 전체 내용을 넣고 
4) 마지막으로 `Convert` 버튼을 클릭하면 json raw string이 생성됩니다.



## House 광고 노출 설정

## 예제 파일
aasExample 폴더는 광고를 노출하는 페이지에대한 얘시이며 안에는 다음 두가지 파일이 존재합니다.

* index.php -  광고노출 페이지 예제 파일
* aasInsertIframe.js - 광고노출 페이지에 추가되어야 할 스크립트입니다.

예시페이지의 동작을 보고 싶다면 aasExample 폴더를 사이트의 root에 복사해 넣으면
도메인/aasExample/ 주소로 확인할 수 있습니다.

## 예제 파일의 광고노출 방식

사이트의 위젯 컨테이너(사이드바 등)에 다음처럼 `.assSpace` 클래스를 가진 광고위젯을 추가합니다.

~~~
 <div class="site-widget-container">
        <div class="aasSpace" style="height: 100%;" data-template="template-1"></div>
 </div>
~~~
사이트의 main css에서 `.site-widget-container`에 대한 높이나 지정하여 사용하거나 `.aasSpace`의 고정높이를 명시하여 사용하십시오.

aasInsertIframe.js 스크립트는 페이지에 존재하는 모든 `.aasSpace` 태그를 찾아 `data-template="1"`에 쓰여진 숫자에 맞추어 template-ID에 맞는 `광고배너`를 iframe으로 추가합니다.




iframe에 추가될 `광고배너`는 하나의 웹페이지로써, 공개되어 있게되며 aas/pages에 각 배너 템플릿별로 저장됩니다. 이 페이지와 여기에 필요한 css/js 파일은 /update.php api에 의해 자동으로 작성됩니다.



