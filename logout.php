<?php
// Check which session is active to redirect to correct login page

// Check admin session first
session_name('ADMIN_SESSION');
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
session_destroy();

// Check default customer session
session_name('PHPSESSID');
session_start();
if (isset($_SESSION['user_id'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
session_destroy();

// Default: redirect to customer login
header("Location: login.php");
exit();
?>