<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    header('Location: index.php');
    exit();
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Basic validation
if ($username === '' || $password === '') {
    header('Location: index.php?error=1');
    exit();
}

// Try DB authentication first
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    // If DB unavailable, fall back to simple admin check
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 'admin_01';
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        header('Location: admin.php');
        exit();
    }
    header('Location: index.php?error=1');
    exit();
}

// Prepared statement to find user by username or email
$stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = ? OR email = ? LIMIT 1');
if (!$stmt) {
    // Prepare failed (likely schema mismatch). Fall back to hardcoded admin or fail.
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 'admin_01';
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        header('Location: admin.php');
        exit();
    }
    header('Location: index.php?error=1');
    $conn->close();
    exit();
}

$stmt->bind_param('ss', $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $hash = $row['password'];
    if (password_verify($password, $hash)) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        // No `role` column in current schema—default to 'user'.
        $_SESSION['role'] = 'user';
        // Redirect based on role
        if ($_SESSION['username'] === 'admin') {
            $_SESSION['role'] = 'admin';
            header('Location: admin.php');
        } else {
            header('Location: home.php');
        }
        $stmt->close();
        $conn->close();
        exit();
    }
}

$stmt->close();
$conn->close();

// Last resort: allow hardcoded admin (legacy)
if ($username === 'admin' && $password === 'admin123') {
    $_SESSION['user_id'] = 'admin_01';
    $_SESSION['username'] = 'admin';
    $_SESSION['role'] = 'admin';
    header('Location: admin.php');
    exit();
}

// Authentication failed
header('Location: index.php?error=1');
exit();
?>