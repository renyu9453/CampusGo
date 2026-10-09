<?php

require 'includes/auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

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

$id = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare(
    'DELETE FROM messages
     WHERE id = ?
     AND user_id = ?'
);

$stmt->execute([
    $id,
    $_SESSION['user_id']
]);

header('Location: board.php');
exit;