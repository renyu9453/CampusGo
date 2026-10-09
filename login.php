<?php

session_start();

require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT * FROM users WHERE username = ?'
    );

    $stmt->execute([$username]);

    $user = $stmt->fetch();

    if (
        $user &&
        password_verify(
            $password,
            $user['password_hash']
        )
    ) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['display_name'] = $user['display_name'];

        header('Location: board.php');
        exit;

    } else {

        $error = '帳號或密碼錯誤';
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

    <title>會員登入</title>

    <link rel="manifest" href="/campusgo/manifest.json">
<meta name="theme-color" content="#2563eb">

    <link
        rel="stylesheet"
        href="css/style.css?v=7"
    >

</head>

<body>

<header class="app-header">
    <h1>CampusGo 校園通</h1>
</header>

<main class="page active">

    <h2>🔐 會員登入</h2>

    <?php if (isset($_GET['registered'])): ?>

        <p class="success">
            註冊成功，請登入！
        </p>

    <?php endif; ?>

    <?php if ($error): ?>

        <p class="error">
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endif; ?>

    <form
        method="post"
        action="login.php"
        class="card"
    >

        <label for="username">
            帳號
        </label>

        <input
            type="text"
            id="username"
            name="username"
            required
        >

        <label for="password">
            密碼
        </label>

        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <button type="submit">
            登入
        </button>

    </form>

    <p>
        還沒有帳號？
        <a href="register.php">前往註冊</a>
    </p>

</main>

</body>
</html>