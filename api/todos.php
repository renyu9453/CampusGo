<?php

session_start();

require '../db.php';

header('Content-Type: application/json; charset=utf-8');

function json_response(
    bool $ok,
    mixed $payload = null,
    int $status = 200
): never {

    http_response_code($status);

    $body = $ok
        ? [
            'ok' => true,
            'data' => $payload
        ]
        : [
            'ok' => false,
            'error' => $payload
        ];

    echo json_encode(
        $body,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


// 沒登入，API 回 401
if (!isset($_SESSION['user_id'])) {

    json_response(
        false,
        '請先登入',
        401
    );
}

$userId = (int) $_SESSION['user_id'];

$method = $_SERVER['REQUEST_METHOD'];

if (
    in_array(
        $method,
        ['POST', 'PUT', 'DELETE'],
        true
    )
) {
    $csrf =
        $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';

    if (
        empty($_SESSION['csrf']) ||
        !hash_equals(
            $_SESSION['csrf'],
            $csrf
        )
    ) {
        json_response(
            false,
            'CSRF 驗證失敗',
            403
        );
    }
}


switch ($method) {

    // =========================
    // GET：讀取自己的待辦
    // =========================
    case 'GET':

        $stmt = $pdo->prepare(
           'SELECT id, title, done, due_at, created_at
             FROM todos
             WHERE user_id = ?
             ORDER BY done ASC, created_at DESC'
        );

        $stmt->execute([$userId]);

        json_response(
            true,
            $stmt->fetchAll()
        );

        break;


    // =========================
    // POST：新增待辦
    // =========================
    case 'POST':

        $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        $title = trim(
            $input['title'] ?? ''
        );

        $dueAt = trim($input['due_at'] ?? '');

if ($dueAt === '') {
    $dueAt = null;
} else {
    $dueAt = str_replace('T', ' ', $dueAt);

    if (strlen($dueAt) === 16) {
        $dueAt .= ':00';
    }
}

        if (
            $title === '' ||
            mb_strlen($title) > 100
        ) {

            json_response(
                false,
                '內容必填，最多 100 字',
                400
            );
        }

$stmt = $pdo->prepare(
    'INSERT INTO todos
     (user_id, title, due_at)
     VALUES (?, ?, ?)'
);

$stmt->execute([
    $userId,
    $title,
    $dueAt
]);

        json_response(
            true,
           [
    'id' => (int) $pdo->lastInsertId(),
    'title' => $title,
    'done' => 0,
    'due_at' => $dueAt
],
            201
        );

        break;


    // =========================
    // PUT：切換完成狀態
    // =========================
    case 'PUT':

        $id = (int) ($_GET['id'] ?? 0);

        $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        $done = !empty($input['done'])
            ? 1
            : 0;

        $stmt = $pdo->prepare(
            'UPDATE todos
             SET done = ?
             WHERE id = ?
             AND user_id = ?'
        );

        $stmt->execute([
            $done,
            $id,
            $userId
        ]);

        if ($stmt->rowCount() === 0) {

            json_response(
                false,
                '找不到這筆待辦',
                404
            );
        }

        json_response(
            true,
            [
                'id' => $id,
                'done' => $done
            ]
        );

        break;


    // =========================
    // DELETE：刪除待辦
    // =========================
    case 'DELETE':

        $id = (int) ($_GET['id'] ?? 0);

        $stmt = $pdo->prepare(
            'DELETE FROM todos
             WHERE id = ?
             AND user_id = ?'
        );

        $stmt->execute([
            $id,
            $userId
        ]);

        if ($stmt->rowCount() === 0) {

            json_response(
                false,
                '找不到這筆待辦',
                404
            );
        }

        json_response(
            true,
            [
                'id' => $id
            ]
        );

        break;


    // =========================
    // 其他方法
    // =========================
    default:

        json_response(
            false,
            '不支援的方法',
            405
        );
}