<?php
// Use a separate session name for admin to avoid conflicts with customer session
session_name('ADMIN_SESSION');
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php?expired=1');
    exit();
}

// Optional: Add session timeout (30 minutes)
$timeout = 1800; // 30 minutes
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $timeout)) {
    session_destroy();
    header('Location: admin_login.php?expired=1');
    exit();
}

// Update login time for timeout tracking
$_SESSION['login_time'] = time();
?>
