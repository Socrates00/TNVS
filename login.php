<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ByaHERO - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --hero-green: #00b14f; /* Green base sa Confirm Booking button */
            --hero-dark: #008f40;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            font-family: 'Inter', -apple-system, sans-serif; 
            /* GAGAMITIN ANG IYONG loginBG DITO */
            background: url('loginBG.jpg') no-repeat center center fixed; 
            background-size: cover;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh;
            padding: 20px;
            position: relative;
        }

        /* Overlay para hindi "masakit sa mata" ang background */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.45); /* Dark tint overlay */
            z-index: 1;
        }

        /* Login Card na may Glassmorphism effect */
        .login-card { 
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.92); /* Semi-transparent white */
            backdrop-filter: blur(12px); /* Blur effect para sa glass look */
            -webkit-backdrop-filter: blur(12px);
            padding: 45px 30px; 
            border-radius: 30px; /* Rounded corners base sa Booking Sheet */
            box-shadow: 0 25px 50px rgba(0,0,0,0.3); 
            width: 100%; 
            max-width: 420px; 
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .logo-container { margin-bottom: 25px; }
        .logo { 
            width: 180px; /* Malaking logo base sa request mo */
            height: auto;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.1));
            transition: transform 0.3s ease;
        }
        .logo:hover { transform: scale(1.05); }

        h2 { 
            font-weight: 800; 
            margin-bottom: 5px; 
            color: #1a1a1a; 
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

        /* Input styling base sa search container */
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
            border-color: var(--hero-green);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(0, 177, 79, 0.1);
        }

        .input-group input:focus + i { color: var(--hero-green); }

        /* Button design base sa Confirm Booking */
        .btn-login { 
            width: 100%; 
            padding: 18px; 
            border: none; 
            background: var(--hero-green); 
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
            box-shadow: 0 8px 20px rgba(0, 177, 79, 0.3);
        }

        .btn-login:hover { 
            background: var(--hero-dark); 
            transform: translateY(-2px);
        }

        .footer-links {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

        .footer-links a {
            color: var(--hero-green);
            text-decoration: none;
            font-weight: 700;
        }

        /* Mobile Adjustments */
        @media (max-width: 480px) {
            .login-card { padding: 40px 25px; border-radius: 25px; }
            h2 { font-size: 22px; }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-container">
            <img src="logo.png" class="logo" alt="ByaHERO Logo">
        </div>
        
        <h2>Welcome, Hero!</h2>
        <p class="subtitle">Log in to start your journey.</p>
        
        <?php if(isset($_GET['error'])): ?>
            <div style="background: #fff2f2; color: #ff4757; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
                <i class="fas fa-circle-exclamation"></i> Invalid username or password.
            </div>
        <?php endif; ?>

        <form action="auth.php" method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn-login">
                Sign In
                <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
            </button>
        </form>

       <div class="footer-links">
    <?php if(isset($_GET['registered'])): ?>
        <div style="background: rgba(0, 177, 79, 0.1); color: var(--hero-green); padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 700;">
            <i class="fas fa-check-circle"></i> Registration Successful, Hero! Login na.
        </div>
    <?php endif; ?>

    Don't have an account? <a href="register.php">Register Now</a> </div>

</body>
</html>