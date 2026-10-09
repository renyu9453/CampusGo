<?php

require 'includes/auth.php';
require 'db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT *
     FROM messages
     WHERE id = ?
     AND user_id = ?'
);

$stmt->execute([
    $id,
    $_SESSION['user_id']
]);

$msg = $stmt->fetch();

if (!$msg) {
    header('Location: board.php');
    exit;
}

$error = '';

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

        $error = '留言內容必填，最多 500 字';

    } else {

        $stmt = $pdo->prepare(
            'UPDATE messages
             SET content = ?
             WHERE id = ?
             AND user_id = ?'
        );

        $stmt->execute([
            $content,
            $id,
            $_SESSION['user_id']
        ]);

        header('Location: board.php');
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

    <title>編輯留言</title>

    <link rel="manifest" href="/campusgo/manifest.json">
<meta name="theme-color" content="#2563eb">

    <link
        rel="stylesheet"
        href="css/style.css?v=7"
    >

</head>

<body>

<?php require 'header.php'; ?>

<main class="page active">

    <h2>✏️ 編輯留言</h2>

    <p>
        留言者：
        <strong>
            <?= htmlspecialchars($_SESSION['display_name']) ?>
        </strong>
    </p>

    <?php if ($error): ?>

        <p class="error">
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endif; ?>

    <form
        method="post"
        action="edit.php?id=<?= $id ?>"
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
            rows="5"
            maxlength="500"
            required
        ><?= htmlspecialchars($msg['content']) ?></textarea>

        <button type="submit">
            儲存修改
        </button>

        <a href="board.php">
            取消
        </a>

    </form>

</main>

</body>
</html>