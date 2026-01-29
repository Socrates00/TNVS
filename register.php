<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ByaHERO - Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --hero-green: #00b14f; --hero-dark: #008f40; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: url('loginBG.jpg') no-repeat center center fixed; 
            background-size: cover;
            display: flex; align-items: center; justify-content: center; 
            min-height: 100vh; padding: 20px; position: relative;
        }
        body::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.45); z-index: 1; }

        .register-card { 
            position: relative; z-index: 2;
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(10px); padding: 35px 25px; 
            border-radius: 30px; box-shadow: 0 25px 50px rgba(0,0,0,0.3); 
            width: 100%; max-width: 420px; text-align: center;
        }
        .logo { width: 140px; margin-bottom: 15px; }
        h2 { font-weight: 800; margin-bottom: 20px; color: #1a1a1a; }

        .input-group { position: relative; margin-bottom: 12px; }
        .input-group i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #999; }
        .input-group input { 
            width: 100%; padding: 15px 15px 15px 48px; border: 1.5px solid #eee; 
            border-radius: 16px; font-size: 14px; outline: none; background: #f9f9f9; transition: 0.3s;
        }
        .input-group input:focus { border-color: var(--hero-green); background: #fff; box-shadow: 0 0 10px rgba(0,177,79,0.1); }

        .btn-register { 
            width: 100%; padding: 16px; border: none; background: var(--hero-green); 
            color: white; border-radius: 16px; font-weight: 700; font-size: 16px; 
            cursor: pointer; transition: 0.3s; margin-top: 15px;
            box-shadow: 0 8px 20px rgba(0, 177, 79, 0.3);
        }
        .btn-register:hover { background: var(--hero-dark); transform: translateY(-2px); }
        .footer-links { margin-top: 20px; font-size: 14px; color: #777; }
        .footer-links a { color: var(--hero-green); text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <div class="register-card">
        <img src="logo.png" class="logo" alt="ByaHERO Logo">
        <h2>Join ByaHERO</h2>

        <form action="register_action.php" method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="fullname" placeholder="Full Name (Juan Dela Cruz)" required>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fas fa-phone"></i>
                <input type="tel" name="phone" placeholder="Phone Number (0912...)" required>
            </div>
            <div class="input-group">
                <i class="fas fa-at"></i>
                <input type="text" name="username" placeholder="Choose Username" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Create Password" required>
            </div>

            <button type="submit" class="btn-register">Register as Hero</button>
        </form>

        <div class="footer-links">
            Already a Hero? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>