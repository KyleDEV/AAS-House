<?php
$title = "AAS";
?>
<!DOCTYPE html>
<html>

<head>
    <title> <?= $title ?> </title>
    <style>
        .responsive-iframe {
            position: relative;
            overflow: hidden;
            padding-top: 56.25%;
            /* 16:9 비율 */
            width: 100%;
        }

        .responsive-iframe iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body>
    <div class="responsive-iframe">
        <iframe src="template-1/index.html" frameborder="0" allowfullscreen>

        </iframe>
    </div>
</body>

</html>