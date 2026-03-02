<?php
session_start();

// Redirect logged-in users to the appropriate start page
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: admin.php');
        exit();
    }
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ByaHERO — Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --hero-green: #00b14f; --hero-dark: #008f40; }
        * { box-sizing: border-box; margin:0; padding:0 }
        body { font-family: Inter, system-ui, Arial, sans-serif; background: url('loginBG.jpg') center/cover no-repeat fixed; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        body::before { content:''; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:0 }
        .login-card { position:relative; z-index:1; width:100%; max-width:420px; background:rgba(255,255,255,0.94); padding:42px 30px; border-radius:24px; box-shadow:0 25px 50px rgba(0,0,0,0.28); }
        .logo{width:160px;margin:0 auto 18px;display:block}
        h2{font-size:24px;margin:6px 0;color:#111;text-align:center}
        .subtitle{color:#666;text-align:center;margin-bottom:20px}
        .input-group{position:relative;margin-bottom:16px}
        .input-group i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#999}
        .input-group input{width:100%;padding:14px 14px 14px 46px;border-radius:12px;border:1.5px solid #eee;background:#f9f9f9}
        .input-group input:focus{outline:none;border-color:var(--hero-green);background:#fff;box-shadow:0 0 0 6px rgba(0,177,79,0.06)}
        .btn-login{width:100%;padding:14px;border-radius:12px;border:none;background:var(--hero-green);color:#fff;font-weight:800;display:flex;align-items:center;justify-content:center;gap:10px}
        .footer-links{margin-top:16px;text-align:center;color:#777}
        .footer-links a{color:var(--hero-green);font-weight:700;text-decoration:none}
        .alert{padding:12px;border-radius:12px;margin-bottom:14px;font-weight:700}
        .err{background:#fff2f2;color:#ff4757}
        .ok{background:rgba(0,177,79,0.08);color:var(--hero-green)}
    </style>
</head>
<body>
    <div class="login-card">
        <img src="logo.png" class="logo" alt="ByaHERO">
        <h2>Welcome, Hero!</h2>
        <p class="subtitle">Log in to start your journey.</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert err"><i class="fas fa-circle-exclamation"></i> Invalid username or password.</div>
        <?php endif; ?>

        <?php if(isset($_GET['registered'])): ?>
            <div class="alert ok"><i class="fas fa-check-circle"></i> Registration successful — please log in.</div>
        <?php endif; ?>

        <form action="auth_process.php" method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Username or email" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn-login">Sign In <i class="fas fa-arrow-right"></i></button>
        </form>

        <div class="footer-links">
            Don't have an account? <a href="register.php">Register Now</a>
        </div>
    </div>
</body>
</html>
