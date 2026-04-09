<?php
session_start();

$admin_password = getenv('ADMIN_PASSWORD');
if (!$admin_password) {
    die('ADMIN_PASSWORD environment variable is not set.');
}

if (isset($_POST['admin_login'])) {
    if ($_POST['admin_login'] === $admin_password) {
        $_SESSION['admin_authed'] = true;
    } else {
        $login_error = 'Wrong password.';
    }
}

if (empty($_SESSION['admin_authed'])) {
    ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="./css/admin.css">
</head>
<body class="admin-login-page">
    <form method="post">
        <label>Admin password</label>
        <input type="password" name="admin_login" autofocus>
        <button type="submit">Log in</button>
        <?php if (!empty($login_error)): ?>
            <span class="error"><?= htmlspecialchars($login_error) ?></span>
        <?php endif; ?>
    </form>
</body>
</html><?php
    exit;
}
