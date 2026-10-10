<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<header class="app-header">

<h1>
    <a href="/campusgo/" class="home-link">CampusGo 校園通</a>
</h1>

    <nav>

        <?php if (isset($_SESSION['user_id'])): ?>

            <span>
                👤 你好，
                <?= htmlspecialchars($_SESSION['display_name']) ?>
            </span>

            <a href="logout.php">
                登出
            </a>

        <?php else: ?>

            <a href="login.php">
                登入
            </a>

            <a href="register.php">
                註冊
            </a>

        <?php endif; ?>

    </nav>

</header>