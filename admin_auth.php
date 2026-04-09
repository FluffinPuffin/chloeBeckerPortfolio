<?php
session_start();

$admin_password = getenv('ADMIN_PASSWORD');
if (!$admin_password) {
    die('ADMIN_PASSWORD environment variable is not set.');
}

if (isset($_POST['admin_login'])) {
    if ($_POST['admin_login'] === $admin_password) {
        $_SESSION['admin_authed'] = true;
    }
}

$is_authed   = !empty($_SESSION['admin_authed']);
$login_error = isset($_POST['admin_login']) && !$is_authed ? 'Wrong password.' : '';
