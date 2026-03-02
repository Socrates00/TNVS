<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user info
$user_stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
if ($user_stmt) {
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();
} else {
    $user = array(
        'username' => $_SESSION['username'] ?? 'User',
        'email' => 'user@byahero.com'
    );
}

// Fetch all bookings for the user, sorted by date
$bookings_query = "SELECT b.*, rt.name as ride_type_name, rt.icon as ride_icon 
                   FROM bookings b 
                   LEFT JOIN ride_types rt ON b.ride_type_id = rt.id
                   WHERE b.user_id = " . intval($_SESSION['user_id']) . " 
                   ORDER BY b.created_at DESC";
$user_bookings = $conn->query($bookings_query);

function getInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return $initials ?: 'U';
}

$initials = getInitials($_SESSION['username'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Activity - ByaHERO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="home.css">
    <style>
        .activity-container {
            max-width: 100%;
            background: #f8f9fa;
            min-height: 100vh;
            padding-bottom: 100px;
        }

        .activity-header {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .activity-header a {
            color: #333;
            font-size: 24px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }

        .activity-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            flex: 1;
        }

        .activity-list {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .activity-item {
            background: white;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            border-left: 4px solid #00b14f;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: 0.2s;
        }

        .activity-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .activity-item.cancelled {
            border-left-color: #f44336;
        }

        .activity-item.pending {
            border-left-color: #ff9800;
        }

        .activity-item.completed {
            border-left-color: #4caf50;
        }

        .activity-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            background: #f0f0f0;
            color: var(--hero-green);
        }

        .activity-details {
            flex: 1;
            min-width: 0;
        }

        .activity-destination {
            font-weight: 700;
            color: #333;
            margin-bottom: 4px;
            word-break: break-word;
        }

        .activity-meta {
            font-size: 12px;
            color: #999;
            margin-bottom: 6px;
        }

        .activity-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .activity-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-completed {
            background: #c8e6c9;
            color: #2e7d32;
        }

        .status-pending {
            background: #fff9c4;
            color: #f57f17;
        }

        .status-cancelled {
            background: #ffcdd2;
            color: #c62828;
        }

        .status-accepted {
            background: #bbdefb;
            color: #1565c0;
        }

        .status-in_progress {
            background: #c8e6c9;
            color: #2e7d32;
        }

        .activity-price {
            font-weight: 800;
            color: #333;
            font-size: 14px;
        }

        .activity-empty {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .activity-empty i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
            color: #ddd;
        }

        .activity-empty h3 {
            margin: 0;
            color: #666;
        }

        /* Override CSS variables */
        :root {
            --hero-green: #00b14f;
            --hero-dark: #1a1a1a;
            --hero-gray: #f8f9fa;
        }

        .profile { position: relative; display: inline-block; }
        .profile-dropdown { display: none; position: absolute; right: 0; top: 52px; background: #fff; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); min-width: 140px; overflow: hidden; z-index: 3000; }
        .profile-dropdown.show { display: block; }
        .dropdown-item { display: block; padding: 10px 14px; color: #111; text-decoration: none; font-weight: 700; }
        .dropdown-item:hover { background: #f5f7f8; }
    </style>
</head>
<body>
    <header class="home-header" style="position: sticky; top: 0; z-index: 1000;">
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

    <div class="activity-container">
        <div class="activity-header">
            <a href="home.php"><i class="fas fa-chevron-left"></i></a>
            <h1>Activity</h1>
        </div>

        <?php if($user_bookings && $user_bookings->num_rows > 0): ?>
            <div class="activity-list">
                <?php while($booking = $user_bookings->fetch_assoc()): 
                    $status = $booking['status'] ?? 'pending';
                    $icon = $booking['ride_icon'] ?? 'fa-car';
                    $rideName = $booking['ride_type_name'] ?? 'Ride';
                    $fare = $booking['actual_fare'] ?? $booking['estimated_fare'];
                    $destination = $booking['destination_location'];
                    $date = new DateTime($booking['created_at']);
                    $formatted_date = $date->format('M d, Y');
                    $formatted_time = $date->format('g:i A');
                ?>
                <div class="activity-item <?php echo $status; ?>">
                    <div class="activity-icon">
                        <i class="fas <?php echo htmlspecialchars($icon); ?>"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-destination">
                            <?php echo htmlspecialchars(strlen($destination) > 45 ? substr($destination, 0, 45) . '...' : $destination); ?>
                        </div>
                        <div class="activity-meta">
                            <i class="fas fa-calendar"></i> <?php echo $formatted_date; ?> at <?php echo $formatted_time; ?><br>
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($rideName); ?>
                        </div>
                        <div class="activity-footer">
                            <span class="activity-status status-<?php echo $status; ?>">
                                <?php echo strtoupper($status); ?>
                            </span>
                            <span class="activity-price">₱<?php echo number_format($fare, 2); ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="activity-empty">
                <i class="fas fa-inbox"></i>
                <h3>No rides yet</h3>
                <p>Your ride history will appear here</p>
                <a href="customer.php" style="margin-top: 20px; display: inline-block; background: var(--hero-green); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700;">Book a Ride</a>
            </div>
        <?php endif; ?>
    </div>

    <nav class="bottom-nav">
        <a href="home.php" class="nav-item">
            <i class="fas fa-house"></i>
            <span>Home</span>
        </a>
        <a href="activity.php" class="nav-item active">
            <i class="fas fa-clock-rotate-left"></i>
            <span>Activity</span>
        </a>
        <a href="payment.php" class="nav-item">
            <i class="fas fa-wallet"></i>
            <span>Payment</span>
        </a>
        <a href="account.php" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Account</span>
        </a>
    </nav>

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

            document.addEventListener('click', function(){
                if (dropdown.classList.contains('show')) closeDropdown();
            });

            document.addEventListener('keydown', function(e){
                if (e.key === 'Escape' && dropdown.classList.contains('show')) closeDropdown();
            });
        })();
    </script>
</body>
</html>
