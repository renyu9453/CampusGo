<?php

require 'includes/auth.php';
require 'db.php';

$errors = [];
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $csrf = $_POST['csrf'] ?? '';

    if (
        !hash_equals(
            $_SESSION['csrf'],
            $csrf
        )
    ) {
        http_response_code(403);
        die('CSRF 驗證失敗');
    }

    $content = trim($_POST['content'] ?? '');

    if ($content === '' || mb_strlen($content) > 500) {
        $errors[] = '留言內容必填，最多 500 字';
    }

    if (!$errors) {

        $stmt = $pdo->prepare(
            'INSERT INTO messages (user_id, content)
             VALUES (?, ?)'
        );

        $stmt->execute([
            $_SESSION['user_id'],
            $content
        ]);

        header('Location: board.php');
        exit;
    }
}

$stmt = $pdo->prepare(
    'SELECT
        m.id,
        m.user_id,
        m.content,
        m.created_at,
        u.display_name
     FROM messages m
     JOIN users u
       ON m.user_id = u.id
     ORDER BY m.created_at DESC'
);

$stmt->execute();

$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>CampusGo 留言板</title>

<link rel="manifest" href="/campusgo/manifest.json">
<meta name="theme-color" content="#2563eb">

<link
    rel="stylesheet"
    href="css/style.css?v=7"
>

<script src="js/db.js" defer></script>
<script src="js/board.js" defer></script>

</head>

<body>

<?php require 'header.php'; ?>

<main class="page active">

    <h2>
        💬 校園留言板（共 <?= count($messages) ?> 則）
    </h2>

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
    id="message-form"
    method="post"
    action="board.php"
    class="card"
>

<input
    type="hidden"
    name="csrf"
    value="<?= htmlspecialchars($_SESSION['csrf']) ?>"
>

        <label for="content">
            留言內容
        </label>

        <textarea
            id="content"
            name="content"
            rows="4"
            maxlength="500"
            required
        ><?= htmlspecialchars($content) ?></textarea>

        <button type="submit">
            發表留言
        </button>

    </form>


    <section class="messages">

        <?php if (!$messages): ?>

            <p class="empty">
                目前還沒有留言，搶頭香吧！
            </p>

        <?php endif; ?>


        <?php foreach ($messages as $m): ?>

            <article class="card">

                <strong>
                    <?= htmlspecialchars($m['display_name']) ?>
                </strong>

                <p>
                    <?= nl2br(htmlspecialchars($m['content'])) ?>
                </p>
<button
    type="button"
    class="share-message"
    data-text="<?= htmlspecialchars(
        $m['content'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>
    📤 分享
</button>
                <small>
                    <?= htmlspecialchars($m['created_at']) ?>
                </small>

                <?php if ((int)$m['user_id'] === (int)$_SESSION['user_id']): ?>

                    <div>

                        <a href="edit.php?id=<?= $m['id'] ?>">
                            ✏️ 編輯
                        </a>

                        &nbsp;
<form
    method="post"
    action="delete.php"
    style="display:inline"
    onsubmit="return confirm('確定要刪除這則留言嗎？')"
>
    <input
        type="hidden"
        name="id"
        value="<?= $m['id'] ?>"
    >

    <input
        type="hidden"
        name="csrf"
        value="<?= htmlspecialchars($_SESSION['csrf']) ?>"
    >

    <button type="submit">
        🗑️ 刪除
    </button>
</form>

                    </div>

                <?php endif; ?>

            </article>

        <?php endforeach; ?>

    </section>

</main>


<nav class="tab-bar">

    <a
        href="index.html"
        class="tab"
    >
        🏠<span>首頁</span>
    </a>

    <a
        href="board.php"
        class="tab active"
    >
        💬<span>留言板</span>
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