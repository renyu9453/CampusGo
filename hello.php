<?php
date_default_timezone_set("Asia/Taipei");

$who = $_GET["who"] ?? "同學";
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>第一個 PHP</title>
</head>

<body>

    <h1>第一個 PHP</h1>

    <p>你好，<?php echo htmlspecialchars($who); ?>！</p>

    <p>
        現在時間：
        <?php echo date("Y-m-d H:i:s"); ?>
    </p>

</body>
</html>