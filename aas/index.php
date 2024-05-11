<?php
$title = "AAS";
$templatePath = "pages/template-1/index.html";
$iframeExists = file_exists($templatePath);
?>
<!DOCTYPE html>
<html>

<head>
    <title> <?= $title ?> </title>
    <style>
        .responsive-iframe {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 300px;
        }

        .responsive-iframe iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .test {
            background-color: bisque;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <p class="test">-----높이 확인용 -----</p>
    <?php if ($iframeExists) : ?>
        <div class="responsive-iframe">
            <iframe src="<?= $templatePath ?>" frameborder="0" allowfullscreen></iframe>
        </div>
    <?php else : ?>
        <!-- iframe 없이 컨테이너만 보여주기 -->
        <div class="responsive-iframe">
            <!-- 비워진 컨테이너, 필요하다면 여기에 대체 콘텐츠나 메시지를 추가 -->
        </div>
    <?php endif; ?>
    <p class="test">---- 높이 확인용------</p>
</body>

</html>