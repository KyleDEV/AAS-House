<?php

/**  광고노출페이지. AAS 광고가 노출 웹페이지. 이것은 테스트용 페이지 파일.*/
$title = "AAS 광고노출 페이지";
$templatePath = "pages/template-1/index.html";
$iframeExists = file_exists($templatePath);
?>
<!DOCTYPE html>
<html>

<head>
    <title> <?= $title ?> </title>
    <style>
        .test {
            background-color: bisque;
            margin: 0;
            padding: 0;
        }

        .site-widget-container {
            height: 250px;
            background-color: aquamarine;
            margin: 0;
            padding: 1em;
        }
    </style>
</head>

<body>
    <div class="site-widget-container">
        <div class="aasSpace" style="height: 100%;" data-template="template-1"></div>
    </div>

    <!-- .aasSpace 요소의  data-template에 맞는 aas 광고페이지를 iframe에 감싸서 .aasSpace의 innerHTL로 넣는 자바스크립트-->
    <script src="/aas/pages/aasInsertIframe.js?v=202405128"></script>
</body>

</html>