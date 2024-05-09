# AAS House
다모앙 서버쪽 광고수신코드. API 엔드포인트.

## 써드파티 라이브러리 설치사용 안내

adsLib 폴더안에는 firebase/JWT 패키지가 필요합니다.

### php composer로 설치:
터미널에서 adsLib 폴더안으로 이동해 아래의 composer 명령어를 입력하세요. 개발에 사용된 (composer.lock 파일에 명시된 패키지와 그 버전) 그대로 설치할 수 있습니다.

`composer install`

### 직접 설치:
동일한 버전을 사용하기위해서 개발자에게 직접 zip 파일을 받으세요.

## API 시크릿 설정
adsApiConfig/config.sample.json 파일에 양식이 있습니다. 이 양식대로 같은 폴더에 config.json 파일을 별도로 만들어 사용해야합니다.

config.sample.json 양식은 git 추적이 되지만 config.json은 .gitignore에 명시하여 추적되지 않게 설정, secret key가 노출되지 않도록 해야합니다.

## 중요! 접근제한 설정
adsApiConfig, adsLib는 웹사이트 방문자가 접근할 수 없어야 합니다. 웹서버 설정을 확인하세요.