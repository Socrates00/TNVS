<?php
// Use a separate session name for admin to avoid conflicts with customer session
session_name('ADMIN_SESSION');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ByaHERO - Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --admin-blue: #003d82; /* Professional admin blue */
            --admin-dark: #002347;
            --admin-light: #0055b3;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            font-family: 'Inter', -apple-system, sans-serif; 
            background: url('loginBG.jpg') no-repeat center center fixed; 
            background-size: cover;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh;
            padding: 20px;
            position: relative;
        }

        /* Overlay para sa admin theme */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 40, 80, 0.50); /* Darker overlay for admin */
            z-index: 1;
        }

        /* Admin Login Card */
        .login-card { 
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(12px); 
            -webkit-backdrop-filter: blur(12px);
            padding: 45px 30px; 
            border-radius: 30px; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.4); 
            width: 100%; 
            max-width: 420px; 
            text-align: center;
            border: 2px solid var(--admin-blue);
        }

        .admin-badge {
            display: inline-block;
            background: var(--admin-blue);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .logo-container { margin-bottom: 25px; }
        .logo { 
            width: 180px;
            height: auto;
            filter: drop-shadow(0 5px 15px rgba(0, 61, 130, 0.2));
            transition: transform 0.3s ease;
        }
        .logo:hover { transform: scale(1.05); }

        h2 { 
            font-weight: 800; 
            margin-bottom: 5px; 
            color: var(--admin-blue); 
            font-size: 26px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 35px;
        }

        .input-group { 
            position: relative; 
            margin-bottom: 18px; 
        }

        .input-group i { 
            position: absolute; 
            left: 20px; 
            top: 50%; 
            transform: translateY(-50%);
            color: #999; 
            font-size: 18px;
            transition: 0.3s;
        }

        .input-group input { 
            width: 100%; 
            padding: 18px 18px 18px 55px; 
            border: 1.5px solid #eee; 
            border-radius: 18px; 
            font-size: 16px; 
            outline: none; 
            background: #f9f9f9;
            transition: 0.3s ease;
        }

        .input-group input:focus {
            border-color: var(--admin-blue);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(0, 61, 130, 0.1);
        }

        .input-group input:focus + i { color: var(--admin-blue); }

        /* Admin Button */
        .btn-login { 
            width: 100%; 
            padding: 18px; 
            border: none; 
            background: var(--admin-blue); 
            color: white; 
            border-radius: 18px; 
            font-weight: 700; 
            font-size: 17px; 
            cursor: pointer; 
            transition: 0.3s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 20px rgba(0, 61, 130, 0.3);
        }

        .btn-login:hover { 
            background: var(--admin-dark); 
            transform: translateY(-2px);
        }

        .footer-links {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

        .footer-links a {
            color: var(--admin-blue);
            text-decoration: none;
            font-weight: 700;
        }

        .error-message {
            background: #fff2f2; 
            color: #ff4757; 
            padding: 12px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            font-size: 13px; 
            font-weight: 600;
            border-left: 4px solid #ff4757;
        }

        @media (max-width: 480px) {
            .login-card { padding: 40px 25px; border-radius: 25px; }
            h2 { font-size: 22px; }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="admin-badge">
            <i class="fas fa-lock"></i> Admin Access
        </div>

        <div class="logo-container">
            <img src="logo.png" class="logo" alt="ByaHERO Logo">
        </div>
        
        <h2>Admin Portal</h2>
        <p class="subtitle">Secure Administrator Login</p>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="error-message">
                <i class="fas fa-circle-exclamation"></i> Invalid admin credentials. Access denied.
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['expired'])): ?>
            <div class="error-message">
                <i class="fas fa-clock"></i> Your session has expired. Please login again.
            </div>
        <?php endif; ?>

        <form action="admin_auth_process.php" method="POST">
            <div class="input-group">
                <i class="fas fa-user-shield"></i>
                <input type="text" name="admin_username" placeholder="Admin Username" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="admin_password" placeholder="Admin Password" required>
            </div>

            <button type="submit" class="btn-login">
                Access Admin Panel
                <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
            </button>
        </form>
    </div>

</body>
</html>
