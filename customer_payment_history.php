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

// Ensure customer_payments table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'customer_payments'");
if ($table_exists && $table_exists->num_rows == 0) {
    $create_table = $conn->query("CREATE TABLE IF NOT EXISTS customer_payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        booking_id INT NOT NULL,
        user_id INT NOT NULL,
        ride_type VARCHAR(100),
        pickup_location VARCHAR(300),
        destination_location VARCHAR(300),
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50),
        transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('completed', 'refunded', 'pending') DEFAULT 'completed',
        refund_amount DECIMAL(10,2) DEFAULT 0,
        refund_date TIMESTAMP NULL,
        notes VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_date (transaction_date),
        INDEX idx_status (status)
    )");
}

// Get filter parameters
$date_filter = isset($_GET['date']) ? $_GET['date'] : 'this_month';
$payment_method_filter = isset($_GET['method']) ? $_GET['method'] : 'all';

// Validate filters
$valid_dates = ['today', 'this_week', 'this_month', 'all'];
$valid_methods = ['all', 'cash', 'card', 'gcash'];

if (!in_array($date_filter, $valid_dates)) {
    $date_filter = 'this_month';
}
if (!in_array($payment_method_filter, $valid_methods)) {
    $payment_method_filter = 'all';
}

// Build date query
$date_query = '';
switch ($date_filter) {
    case 'today':
        $date_query = "AND DATE(cp.transaction_date) = CURDATE()";
        break;
    case 'this_week':
        $date_query = "AND WEEK(cp.transaction_date) = WEEK(NOW()) AND YEAR(cp.transaction_date) = YEAR(NOW())";
        break;
    case 'this_month':
        $date_query = "AND MONTH(cp.transaction_date) = MONTH(NOW()) AND YEAR(cp.transaction_date) = YEAR(NOW())";
        break;
    default:
        $date_query = "";
}

// Build payment method query
$method_query = '';
if ($payment_method_filter !== 'all') {
    $method_filter = $conn->real_escape_string($payment_method_filter);
    $method_query = "AND cp.payment_method = '$method_filter'";
}

// Fetch user info
$user_stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
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

// Map ride type to icon
function getRideIcon($ride_type) {
    $types = [
        'ByaHERO Sedan' => 'fa-car',
        'ByaHERO 6-Str' => 'fa-van-shuttle',
        'ByaHERO Moto' => 'fa-motorcycle'
    ];
    return $types[$ride_type] ?? 'fa-car';
}

// Get status color
function getStatusColor($status) {
    $colors = [
        'completed' => '#c8e6c9',
        'refunded' => '#fff9c4',
        'pending' => '#bbdefb'
    ];
    return $colors[$status] ?? '#c8e6c9';
}

function getStatusTextColor($status) {
    $colors = [
        'completed' => '#2e7d32',
        'refunded' => '#f57f17',
        'pending' => '#1565c0'
    ];
    return $colors[$status] ?? '#2e7d32';
}

// Fetch user's payment records
$table_check = $conn->query("SHOW TABLES LIKE 'customer_payments'");
$table_exists = $table_check && $table_check->num_rows > 0;

if (!$table_exists) {
    // Initialize empty values if table doesn't exist
    $payments = null;
    $stats = [
        'total_spent' => 0,
        'total_refunded' => 0,
        'total_rides' => 0,
        'completed_rides' => 0,
        'refunded_rides' => 0
    ];
    $payment_breakdown = null;
} else {
    $payments_sql = "
        SELECT 
            cp.id, cp.booking_id, cp.ride_type, cp.pickup_location, cp.destination_location,
            cp.amount, cp.payment_method, cp.transaction_date, cp.status, cp.refund_amount,
            cp.refund_date, cp.notes
        FROM customer_payments cp
        WHERE cp.user_id = " . intval($_SESSION['user_id']) . "
        $date_query
        $method_query
        ORDER BY cp.transaction_date DESC
    ";

    $payments = $conn->query($payments_sql);

    // Calculate statistics
    $stats_sql = "
        SELECT 
            COALESCE(SUM(amount), 0) as total_spent,
            COALESCE(SUM(CASE WHEN status = 'refunded' THEN refund_amount ELSE 0 END), 0) as total_refunded,
            COUNT(*) as total_rides,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_rides,
            COUNT(CASE WHEN status = 'refunded' THEN 1 END) as refunded_rides
        FROM customer_payments cp
        WHERE cp.user_id = " . intval($_SESSION['user_id']) . "
        $date_query
        $method_query
    ";

    $stats = $conn->query($stats_sql)->fetch_assoc();

    // Get payment method breakdown
    $payment_breakdown_sql = "
        SELECT 
            payment_method,
            COUNT(*) as count,
            COALESCE(SUM(amount), 0) as total
        FROM customer_payments cp
        WHERE cp.user_id = " . intval($_SESSION['user_id']) . "
        $date_query
        $method_query
        GROUP BY payment_method
        ORDER BY total DESC
    ";

    $payment_breakdown = $conn->query($payment_breakdown_sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Payment History - ByaHERO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<link rel="stylesheet" href="customer.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --hero-green: #00b14f;
        --text-dark: #1a1a1a;
        --text-gray: #666;
        --bg-white: #ffffff;
    }

    .payment-container {
        padding: 0 20px;
        max-width: 800px;
        margin: 0 auto;
        padding-bottom: 100px;
    }

    .payment-header-section {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 0 30px 0;
        padding: 0;
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

    .payment-header-section h1 {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0;
    }

    .payment-header-section {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 0 30px 0;
        padding: 0;
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

    .payment-header-section h1 {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0;
    }

    .payment-item {
        background: white;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        border-left: 4px solid var(--hero-green);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        margin-bottom: 12px;
        transition: 0.2s;
    }

    .payment-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .payment-item.refunded {
        border-left-color: #ff9800;
    }

    .payment-item.pending {
        border-left-color: #ffb74d;
    }

    .payment-icon {
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

    .payment-details {
        flex: 1;
        min-width: 0;
    }

    .payment-destination {
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 4px;
        word-break: break-word;
        font-size: 14px;
    }

    .payment-meta {
        font-size: 12px;
        color: #999;
        margin-bottom: 6px;
    }

    .payment-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .payment-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        background: #c8e6c9;
        color: #2e7d32;
    }

    .payment-status.refunded {
        background: #fff9c4;
        color: #f57f17;
    }

    .payment-status.pending {
        background: #bbdefb;
        color: #1565c0;
    }

    .payment-amount {
        font-weight: 700;
        font-size: 14px;
        color: var(--text-dark);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state i {
        font-size: 48px;
        display: block;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-state strong {
        display: block;
        margin-bottom: 5px;
        color: #666;
    }

    body {
        background-color: #f5f7fa;
        padding-bottom: 80px;
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

    @media (max-width: 768px) {
        .payment-container {
            padding: 0 15px;
        }

        .payment-header-section h1 {
            font-size: 20px;
        }

        .payment-header-section h1 {
            font-size: 20px;
        }

        .payment-item {
            padding: 14px;
            margin-bottom: 10px;
        }

        .payment-icon {
            width: 45px;
            height: 45px;
            font-size: 20px;
        }

        .payment-destination {
            font-size: 13px;
        }

        .payment-amount {
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .payment-container {
            padding: 0 12px;
        }

        .payment-header-section {
            gap: 10px;
            margin: 15px 0 20px 0;
        }

        .payment-header-section h1 {
            font-size: 18px;
        }

        .back-arrow {
            width: 36px;
            height: 36px;
            font-size: 16px;
        }

        .payment-header-section {
            gap: 10px;
            margin: 15px 0 20px 0;
        }

        .payment-header-section h1 {
            font-size: 18px;
        }

        .back-arrow {
            width: 36px;
            height: 36px;
            font-size: 16px;
        }

        .payment-item {
            padding: 12px;
            margin-bottom: 8px;
            gap: 10px;
        }

        .payment-icon {
            width: 42px;
            height: 42px;
            font-size: 18px;
        }

        .payment-destination {
            font-size: 13px;
            margin-bottom: 3px;
        }

        .payment-meta {
            font-size: 11px;
            margin-bottom: 4px;
        }

        .payment-footer {
            gap: 6px;
        }

        .payment-status {
            padding: 3px 10px;
            font-size: 10px;
        }

        .payment-amount {
            font-size: 13px;
        }
    }
</style>

<div class="payment-container">
    <div class="payment-header-section">
        <a href="home.php" class="back-arrow">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1>Payment History</h1>
    </div>

    <?php if (!$table_exists): ?>
    <div style="background: #cfe2ff; color: #084298; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #084298; text-align: center;">
        <strong>⚠️ Setup Required</strong><br>
        <small>Please run the <a href="database_setup.php" style="color: #084298; text-decoration: underline;">database setup</a> first.</small>
    </div>
    <?php elseif ($payments && $payments->num_rows > 0): ?>
        <?php while ($payment = $payments->fetch_assoc()): ?>
        <div class="payment-item <?php echo strtolower($payment['status']); ?>">
            <div class="payment-icon">
                <i class="fas <?php echo getRideIcon($payment['ride_type']); ?>"></i>
            </div>
            <div class="payment-details">
                <div class="payment-destination">
                    <?php echo htmlspecialchars(substr($payment['pickup_location'], 0, 40)); ?>
                </div>
                <div class="payment-meta">
                    <i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y @ g:i A', strtotime($payment['transaction_date'])); ?>
                </div>
                <div class="payment-meta">
                    <i class="fas fa-car"></i> <?php echo htmlspecialchars($payment['ride_type'] ?? 'Standard'); ?>
                </div>
                <div class="payment-footer">
                    <span class="payment-status <?php echo strtolower($payment['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($payment['status'])); ?>
                    </span>
                    <span class="payment-amount">₱<?php echo number_format($payment['amount'], 2); ?></span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <strong>No payment records found</strong><br>
        <small>You haven't made any rides yet. Start by booking your first ride!</small>
    </div>
    <?php endif; ?>
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
    <a href="payment.php" class="nav-item active">
        <i class="fas fa-wallet"></i>
        <span>Payment</span>
    </a>
    <a href="account.php" class="nav-item">
        <i class="fas fa-user"></i>
        <span>Account</span>
    </a>
</nav>

<script>
function getRideIcon(ride_type) {
    const types = {
        'ByaHERO Sedan': 'fa-car',
        'ByaHERO 6-Str': 'fa-van-shuttle',
        'ByaHERO Moto': 'fa-motorcycle'
    };
    return types[ride_type] || 'fa-car';
}
</script>

</body>
</html>
