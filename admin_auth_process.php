<?php
// Use a separate session name for admin to avoid conflicts with customer session
session_name('ADMIN_SESSION');
session_start();

if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    header('Location: admin_login.php');
    exit();
}

$admin_username = isset($_POST['admin_username']) ? trim($_POST['admin_username']) : '';
$admin_password = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';

// Basic validation
if ($admin_username === '' || $admin_password === '') {
    header('Location: admin_login.php?error=1');
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    // If DB unavailable, fall back to simple admin check (hardcoded for emergencies only)
    if ($admin_username === 'admin' && $admin_password === 'admin123') {
        $_SESSION['user_id'] = 'admin_01';
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        $_SESSION['login_time'] = time();
        header('Location: admin.php');
        exit();
    }
    header('Location: admin_login.php?error=1');
    exit();
}

// Check if admins table exists
$table_check = $conn->query("SHOW TABLES LIKE 'admins'");

if ($table_check && $table_check->num_rows > 0) {
    // Admins table exists, verify credentials from database
    $stmt = $conn->prepare('SELECT id, username, password FROM admins WHERE username = ? LIMIT 1');
    
    if ($stmt) {
        $stmt->bind_param('s', $admin_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            // If password is hashed (recommended)
            if (password_verify($admin_password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = 'admin';
                $_SESSION['login_time'] = time();
                header('Location: admin.php');
                $stmt->close();
                $conn->close();
                exit();
            }
            // If password is plain text (not recommended - transition to hashing)
            else if ($row['password'] === $admin_password) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = 'admin';
                $_SESSION['login_time'] = time();
                header('Location: admin.php');
                $stmt->close();
                $conn->close();
                exit();
            }
        }
        $stmt->close();
    }
} else {
    // Admins table doesn't exist, use fallback hardcoded credentials
    if ($admin_username === 'admin' && $admin_password === 'admin123') {
        $_SESSION['user_id'] = 'admin_01';
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        $_SESSION['login_time'] = time();
        header('Location: admin.php');
        $conn->close();
        exit();
    }
}

$conn->close();

// Authentication failed
header('Location: admin_login.php?error=1');
exit();
?>
