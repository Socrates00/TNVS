<?php
session_start();
// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch user info
$user_stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
if ($user_stmt) {
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();
} else {
    $user = array(
        'username' => $_SESSION['username'] ?? 'User',
        'email' => 'user@byahero.com'
    );
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $conn->real_escape_string($_POST['username'] ?? $user['username']);
    $email = $conn->real_escape_string($_POST['email'] ?? $user['email']);

    $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("ssi", $username, $email, $user_id);
        if ($update_stmt->execute()) {
            $message = "Profile updated successfully!";
            $_SESSION['username'] = $username;
            $user['username'] = $username;
            $user['email'] = $email;
        } else {
            $error = "Error updating profile: " . $update_stmt->error;
        }
        $update_stmt->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Verify current password
        $pwd_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        if ($pwd_stmt) {
            $pwd_stmt->bind_param("i", $user_id);
            $pwd_stmt->execute();
            $pwd_result = $pwd_stmt->get_result()->fetch_assoc();
            $pwd_stmt->close();

            if ($pwd_result && password_verify($current_password, $pwd_result['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $new_pwd_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($new_pwd_stmt) {
                    $new_pwd_stmt->bind_param("si", $hashed_password, $user_id);
                    if ($new_pwd_stmt->execute()) {
                        $message = "Password changed successfully!";
                    } else {
                        $error = "Error changing password: " . $new_pwd_stmt->error;
                    }
                    $new_pwd_stmt->close();
                }
            } else {
                $error = "Current password is incorrect!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Account - ByaHERO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<link rel="stylesheet" href="customer.css">
<style>
    :root {
        --hero-green: #00b14f;
        --text-dark: #1a1a1a;
        --text-gray: #666;
        --bg-white: #ffffff;
    }

    body {
        background-color: #f5f7fa;
        padding-bottom: 100px;
    }

    .account-container {
        padding: 0 20px;
        max-width: 800px;
        margin: 0 auto;
        padding-top: 20px;
    }

    .header-section {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 0 30px 0;
    }

    .back-arrow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: var(--hero-green);
        color: white;
        border-radius: 50%;
        text-decoration: none;
        font-size: 18px;
        transition: 0.2s;
    }

    .back-arrow:hover {
        background: #008a3d;
    }

    .header-section h1 {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .alert-success {
        background: #c8e6c9;
        color: #2e7d32;
        border-left: 4px solid #2e7d32;
    }

    .alert-error {
        background: #ffcdd2;
        color: #c62828;
        border-left: 4px solid #c62828;
    }

    .profile-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: var(--hero-green);
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 14px;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #e1e8ed;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        box-sizing: border-box;
        transition: 0.2s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--hero-green);
        box-shadow: 0 0 0 3px rgba(0, 177, 79, 0.1);
    }

    .form-group input:disabled {
        background: #f5f7fa;
        color: #94a3b8;
    }

    .btn {
        background: var(--hero-green);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        font-size: 14px;
        width: 100%;
    }

    .btn:hover {
        background: #008a3d;
    }

    .btn-secondary {
        background: #e1e8ed;
        color: var(--text-dark);
    }

    .btn-secondary:hover {
        background: #cbd5e0;
    }

    .btn-danger {
        background: #ef5350;
    }

    .btn-danger:hover {
        background: #c62828;
    }

    .btn-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .user-info-display {
        background: #f5f7fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .user-info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e1e8ed;
    }

    .user-info-row:last-child {
        border-bottom: none;
    }

    .user-info-label {
        font-weight: 600;
        color: var(--text-gray);
        font-size: 13px;
    }

    .user-info-value {
        color: var(--text-dark);
        font-weight: 600;
    }

    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 75px;
        background: white;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-around;
        align-items: center;
        z-index: 1000;
    }

    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.2s;
        padding: 0 10px;
    }

    .nav-item:hover {
        color: var(--hero-green);
    }

    .nav-item.active {
        color: var(--hero-green);
    }

    .nav-item i {
        font-size: 20px;
    }

    .nav-item span {
        font-size: 10px;
        font-weight: 700;
    }

    .logout-link {
        display: inline-block;
        color: #ef5350;
        text-decoration: none;
        font-weight: 700;
        margin-top: 20px;
        text-align: center;
    }

    .logout-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 480px) {
        .account-container {
            padding: 0 12px;
        }

        .profile-section {
            padding: 15px;
        }

        .btn-group {
            grid-template-columns: 1fr;
        }

        .section-title {
            font-size: 16px;
        }
    }
</style>

<div class="account-container">
    <div class="header-section">
        <a href="home.php" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1>Account</h1>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Profile Information Section -->
    <div class="profile-section">
        <h2 class="section-title">
            <i class="fas fa-user"></i>
            Profile Information
        </h2>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>

            <button type="submit" name="update_profile" class="btn">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </form>
    </div>

    <!-- Change Password Section -->
    <div class="profile-section">
        <h2 class="section-title">
            <i class="fas fa-lock"></i>
            Change Password
        </h2>
        
        <form method="POST">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Minimum 6 characters" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" name="change_password" class="btn">
                <i class="fas fa-refresh"></i> Change Password
            </button>
        </form>
    </div>

    <!-- Account Actions -->
    <div class="profile-section">
        <h2 class="section-title">
            <i class="fas fa-cog"></i>
            Account Actions
        </h2>
        
        <div class="form-group">
            <button onclick="if(confirm('Are you sure you want to logout?')) { window.location='logout.php'; }" class="btn btn-secondary">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>
</div>

<nav class="bottom-nav">
    <a href="home.php" class="nav-item">
        <i class="fas fa-house"></i>
        <span>Home</span>
    </a>
    <a href="activity.php" class="nav-item">
        <i class="fas fa-clock-rotate-left"></i>
        <span>Activity</span>
    </a>
    <a href="payment.php" class="nav-item">
        <i class="fas fa-wallet"></i>
        <span>Payment</span>
    </a>
    <a href="account.php" class="nav-item active">
        <i class="fas fa-user"></i>
        <span>Account</span>
    </a>
</nav>

</body>
</html>
