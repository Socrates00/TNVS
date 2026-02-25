<!DOCTYPE html>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

function getInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return $initials ?: 'U'; // Default to 'U' if no name
}

$initials = getInitials($_SESSION['username'] ?? 'User');
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ByaHERO - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="home.css">
    <style>
        /* Make sure links don't have default blue styling */
        .service-item { text-decoration: none; color: inherit; }
        /* Profile dropdown */
        .profile { position: relative; display: inline-block; }
        .profile-dropdown { display: none; position: absolute; right: 0; top: 52px; background: #fff; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); min-width: 140px; overflow: hidden; z-index: 3000; }
        .profile-dropdown.show { display: block; }
        .dropdown-item { display: block; padding: 10px 14px; color: #111; text-decoration: none; font-weight: 700; }
        .dropdown-item:hover { background: #f5f7f8; }
    </style>
</head>
<body>

    <header class="home-header">
        <div class="brand-group-home">
            <img src="logo.png" alt="ByaHERO" class="home-logo-img">
            <span class="home-brand-text">Bya<span class="highlight">HERO</span></span>
        </div>
        <div class="header-icons">
            <div class="notif-circle"><i class="fas fa-bell"></i></div>
            <div class="profile">
                <div class="user-circle" id="profileToggle" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $initials; ?></div>
                <div class="profile-dropdown" id="profileDropdown" aria-hidden="true">
                    <form id="logoutForm" method="post" action="logout.php" style="margin:0;">
                        <button type="submit" class="dropdown-item logout-btn" style="width:100%;text-align:left;border:none;background:none;cursor:pointer;">Logout</button>
                    </form>
                </div>
            </div>
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

                <a href="express.php" class="service-item">
                    <div class="service-icon express"><i class="fas fa-box"></i></div>
                    <span>Express</span>
                </a>

                <a href="fleet.php" class="service-item">
                    <div class="service-icon fleet"><i class="fas fa-truck"></i></div>
                    <span>Fleet</span>
                </a>

                <a href="safety.php" class="service-item">
                    <div class="service-icon safety"><i class="fas fa-shield-halved"></i></div>
                    <span>Safety</span>
                </a>
            </div>
        </section>

        <div class="promo-banner">
            <div class="promo-text">
                <h4>Mag-ByaHERO na!</h4>
                <p>Enjoy 10% off on your next 5 rides.</p>
            </div>
            <i class="fas fa-gift promo-icon"></i>
        </div>

        <section class="offers-section">
            <h3 class="section-title">Exclusive Offers</h3>
            <div class="offers-container">
                <div class="offer-card">
                    <div class="offer-badge">RIDE</div>
                    <h4>Weekend Special</h4>
                    <p>₱20 off rides on weekends</p>
                    <span class="offer-expiry">Valid till Feb 28</span>
                </div>
                <div class="offer-card">
                    <div class="offer-badge express">EXPRESS</div>
                    <h4>Fast Delivery</h4>
                    <p>30% off on orders above ₱500</p>
                    <span class="offer-expiry">Valid till Feb 28</span>
                </div>
            </div>
        </section>

        <section class="stats-section">
            <h3 class="section-title">Your ByaHERO Stats</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">24</div>
                    <p class="stat-label">Total Rides</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">₱2,450</div>
                    <p class="stat-label">Amount Saved</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">⭐ 4.9</div>
                    <p class="stat-label">Your Rating</p>
                </div>
            </div>
        </section>

        <section class="tips-section">
            <h3 class="section-title">Pro Tips</h3>
            <div class="tips-container">
                <div class="tip-item">
                    <i class="fas fa-lightbulb"></i>
                    <div>
                        <h4>Book in Advance</h4>
                        <p>Save up to 15% when you schedule your ride ahead</p>
                    </div>
                </div>
                <div class="tip-item">
                    <i class="fas fa-users"></i>
                    <div>
                        <h4>Share Rides</h4>
                        <p>Invite friends and earn ₱50 rewards per referral</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <nav class="bottom-nav">
            <script>
                // Toggle profile dropdown with keyboard and ARIA support
                (function(){
                    var toggle = document.getElementById('profileToggle');
                    var dropdown = document.getElementById('profileDropdown');
                    if (!toggle || !dropdown) return;

                    function closeDropdown() {
                        dropdown.classList.remove('show');
                        dropdown.setAttribute('aria-hidden', 'true');
                        toggle.setAttribute('aria-expanded', 'false');
                    }

                    function openDropdown() {
                        dropdown.classList.add('show');
                        dropdown.setAttribute('aria-hidden', 'false');
                        toggle.setAttribute('aria-expanded', 'true');
                    }

                    toggle.addEventListener('click', function(e){
                        e.stopPropagation();
                        if (dropdown.classList.contains('show')) closeDropdown(); else openDropdown();
                    });

                    toggle.addEventListener('keydown', function(e){
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            e.stopPropagation();
                            if (dropdown.classList.contains('show')) closeDropdown(); else openDropdown();
                        }
                    });

                    // Close when clicking outside
                    document.addEventListener('click', function(){
                        if (dropdown.classList.contains('show')) closeDropdown();
                    });

                    // Close on Escape
                    document.addEventListener('keydown', function(e){
                        if (e.key === 'Escape' && dropdown.classList.contains('show')) closeDropdown();
                    });
                })();
            </script>
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
