<?php

require 'db.php';

session_start();

$errors = [];

$username = '';
$display = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $display = trim($_POST['display_name'] ?? '');
    $pw = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';

    if (!preg_match('/^[A-Za-z0-9]{4,30}$/', $username)) {
        $errors[] = '帳號需為 4–30 位英數字';
    }

    if ($display === '' || mb_strlen($display) > 50) {
        $errors[] = '顯示名稱必填，最多 50 字';
    }

    if (mb_strlen($pw) < 8) {
        $errors[] = '密碼至少 8 字';
    }

    if ($pw !== $pw2) {
        $errors[] = '兩次密碼不一致';
    }

    if (!$errors) {

        $stmt = $pdo->prepare(
            'SELECT id FROM users WHERE username = ?'
        );

        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $errors[] = '這個帳號已經有人使用';
        }
    }

    if (!$errors) {

        $hash = password_hash(
            $pw,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare(
            'INSERT INTO users
            (username, password_hash, display_name)
            VALUES (?, ?, ?)'
        );

        $stmt->execute([
            $username,
            $hash,
            $display
        ]);

        header('Location: login.php?registered=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>會員註冊</title>

    <link rel="manifest" href="/campusgo/manifest.json">
<meta name="theme-color" content="#2563eb">

    <link
        rel="stylesheet"
        href="css/style.css?v=7"
    >

</head>

<body>

<header class="app-header">
<h1>
    <a href="/campusgo/" class="home-link">CampusGo 校園通</a>
</h1>
</header>

<main class="page active">

    <h2>📝 會員註冊</h2>

    <?php if ($errors): ?>

        <div class="error">

            <?php foreach ($errors as $error): ?>

                <p>
                    <?= htmlspecialchars($error) ?>
                </p>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <form
        method="post"
        action="register.php"
        class="card"
    >

        <label for="username">
            帳號（英數 4–30 字）
        </label>

        <input
            type="text"
            id="username"
            name="username"
            value="<?= htmlspecialchars($username) ?>"
            required
        >

        <label for="display_name">
            顯示名稱
        </label>

        <input
            type="text"
            id="display_name"
            name="display_name"
            maxlength="50"
            value="<?= htmlspecialchars($display) ?>"
            required
        >

        <label for="password">
            密碼（至少 8 字）
        </label>

        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <label for="password2">
            再輸入一次密碼
        </label>

        <input
            type="password"
            id="password2"
            name="password2"
            required
        >

        <button type="submit">
            註冊
        </button>

    </form>

    <p>
        已經有帳號？
        <a href="login.php">前往登入</a>
    </p>

</main>

</body>
</html>