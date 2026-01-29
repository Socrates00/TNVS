<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ByaHERO - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="home.css">
</head>
<body>

    <header class="home-header">
        <div class="brand-group-home">
    <img src="logo.png" alt="ByaHERO" class="home-logo-img" id="adminTrigger" onclick="secretAdminAccess()">
    <span class="home-brand-text">Bya<span class="highlight">HERO</span></span>
</div>
        <div class="header-icons">
            <div class="notif-circle"><i class="fas fa-bell"></i></div>
            <div class="user-circle">JD</div>
        </div>
    </header>

    <div class="main-container">
        <section class="wallet-section">
            <div class="wallet-card">
                <div class="wallet-left">
                    <p class="wallet-label">ByaHERO Pay</p>
                    <h2 class="wallet-balance">₱1,240.50</h2>
                </div>
                <button class="topup-btn">TOP UP</button>
            </div>
        </section>

        <section class="services-section">
            <h3 class="section-title">Anong kailangan mo, Hero?</h3>
            <div class="service-grid">
                <a href="customer.php" class="service-item">
                    <div class="service-icon ride"><i class="fas fa-car"></i></div>
                    <span>Ride</span>
                </a>
                <div class="service-item">
                    <div class="service-icon express"><i class="fas fa-box"></i></div>
                    <span>Express</span>
                </div>
                <div class="service-item">
                    <div class="service-icon fleet"><i class="fas fa-taxi"></i></div>
                    <span>Fleet</span>
                </div>
                <div class="service-item">
                    <div class="service-icon safety"><i class="fas fa-shield-halved"></i></div>
                    <span>Safety</span>
                </div>
            </div>
        </section>

        <div class="promo-banner">
            <div class="promo-text">
                <h4>Mag-ByaHERO na!</h4>
                <p>Enjoy 10% off on your next 5 rides.</p>
            </div>
            <i class="fas fa-gift promo-icon"></i>
        </div>
    </div>

    <nav class="bottom-nav">
        <div class="nav-item active">
            <i class="fas fa-house"></i>
            <span>Home</span>
        </div>
        <div class="nav-item">
            <i class="fas fa-clock-rotate-left"></i>
            <span>Activity</span>
        </div>
        <div class="nav-item">
            <i class="fas fa-wallet"></i>
            <span>Payment</span>
        </div>
        <div class="nav-item">
            <i class="fas fa-user"></i>
            <span>Account</span>
        </div>
    </nav>

</body>
</html>

<script>
    let clickCount = 0;
    let clickTimer;

    function secretAdminAccess() {
        clickCount++;
        
        clearTimeout(clickTimer);
        
        // Reset count kung tumigil ang pag-click sa loob ng 500ms
        clickTimer = setTimeout(() => {
            clickCount = 0;
        }, 500);

        // Kapag naka-3 clicks, hihingi ng password bago pumasok sa Admin Side
        if (clickCount === 3) {
            clickCount = 0; // Reset counter agad
            
            // Password prompt
            let password = prompt("ByaHERO Security: Enter Admin PIN");

            // Palitan ang '1234' ng gusto mong password
            if (password === "admin123") {
                alert("Access Granted! Welcome Hero.");
                window.location.href = 'index.php';
            } else {
                alert("Wrong PIN. Access Denied.");
            }
        }
    }
</script>