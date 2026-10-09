<?php

require 'includes/auth.php';

?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

<title>CampusGo 待辦</title>

<link rel="manifest" href="/campusgo/manifest.json">
<meta name="theme-color" content="#2563eb">

<meta
    name="csrf-token"
    content="<?= htmlspecialchars($_SESSION['csrf']) ?>"
>

<link
    rel="stylesheet"
    href="css/style.css?v=8"
>

    <script
        src="js/todo.js"
        defer
    ></script>

</head>

<body>

<?php require 'header.php'; ?>

<main class="page active">

    <h2>✅ 我的待辦</h2>

<form id="todo-form" class="card">

    <label for="title">
        新增待辦
    </label>

    <input
        type="text"
        id="title"
        maxlength="100"
        required
    >

    <label for="due-at">
        到期時間
    </label>

    <input
        type="datetime-local"
        id="due-at"
        name="due_at"
    >

    <button type="submit">
        新增
    </button>

</form>

<section class="card">
    <h2>🔔 到期提醒</h2>

    <p>
        開啟後，待辦事項到期時會通知你。
    </p>

    <button
        type="button"
        id="btn-notify"
    >
        開啟提醒
    </button>
</section>

    </form>

    <p
        id="status"
        role="status"
    ></p>

    <ul id="todo-list"></ul>

</main>


<nav class="tab-bar">

    <a
        href="index.html"
        class="tab"
    >
        🏠<span>首頁</span>
    </a>

    <a
        href="todo.php"
        class="tab active"
    >
        ✅<span>待辦</span>
    </a>

    <a
        href="weather.html"
        class="tab"
    >
        🌤️<span>天氣</span>
    </a>

</nav>

</body>
</html>