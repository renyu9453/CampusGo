<?php
$name = "";
$email = "";
$content = "";
$type = "";
$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$content = trim($_POST["content"] ?? "");
$type = trim($_POST["type"] ?? "");

$allowed = ["建議", "問題回報", "其他"];

if (!in_array($type, $allowed)) {
    $errors[] = "意見類型不正確";
}

    if ($name === "") {
        $errors[] = "姓名為必填";
    }

    if ($email === "") {
        $errors[] = "Email 為必填";
    }

if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email 格式不正確";
}

if ($content === "") {
    $errors[] = "意見內容為必填";
}

if ($content !== "" && mb_strlen($content) < 10) {
    $errors[] = "意見內容至少 10 個字";
}

if (mb_strlen($content) > 500) {
    $errors[] = "意見內容最多 500 個字";

}
if (empty($errors)) {
    $success = true;
}

}
?>

<?php include "header.php"; ?>

    <main>
      <h1>意見回饋</h1>

<?php if ($success): ?>

    <div class="success-box" role="status">
    <p>✅ 意見已成功送出！</p>

    <?php echo "收到：" . htmlspecialchars($content); ?>

</div>

<?php else: ?>

<?php if (!empty($errors)): ?>
    <div class="error-box" role="alert">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="feedback.php">

<label for="type">意見類型</label>

<select id="type" name="type" required>
    <option value="">請選擇</option>
    <option value="建議" <?php if ($type === "建議") echo "selected"; ?>>建議</option>
    <option value="問題回報" <?php if ($type === "問題回報") echo "selected"; ?>>問題回報</option>
    <option value="其他" <?php if ($type === "其他") echo "selected"; ?>>其他</option>
</select>
            <label for="name">姓名（必填）</label>
            <input
    type="text"
    id="name"
    name="name"
    required
    value="<?php echo htmlspecialchars($name); ?>"
>

            <label for="email">Email（必填）</label>
           <input
    type="email"
    id="email"
    name="email"
    required
    value="<?php echo htmlspecialchars($email); ?>"
>

            <label for="content">意見內容（必填，10 字以上）</label>
<textarea
    id="content"
    name="content"
    rows="5"
    required
><?php echo htmlspecialchars($content); ?></textarea>

            <button type="submit">送出意見</button>

        </form>

<?php endif; ?>

    </main>

    <?php include "footer.php"; ?>