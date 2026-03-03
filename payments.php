<?php
// Support both admin and customer sessions
if (session_status() === PHP_SESSION_NONE) {
    // Try admin session first
    session_name('ADMIN_SESSION');
    session_start();
    
    // If no admin session, try default customer session
    if (!isset($_SESSION['user_id'])) {
        session_destroy();
        session_name('PHPSESSID');
        session_start();
    }
}

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create refunds table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS refunds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    amount DECIMAL(10,2),
    reason VARCHAR(500),
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
)");

// Handle refund request
$refund_msg = '';
$refund_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'refund') {
    $booking_id = intval($_POST['booking_id']);
    $reason = $conn->real_escape_string($_POST['reason']);
    
    // Get booking amount
    $booking = $conn->query("SELECT id, estimated_fare FROM bookings WHERE id = $booking_id")->fetch_assoc();
    
    if ($booking) {
        $stmt = $conn->prepare("INSERT INTO refunds (booking_id, amount, reason, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("ids", $booking_id, $booking['estimated_fare'], $reason);
        if ($stmt->execute()) {
            $refund_msg = '✓ Refund request submitted for processing';
        } else {
            $refund_error = 'Error submitting refund: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Get filter parameters
$payment_method_filter = isset($_GET['method']) ? $_GET['method'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : 'today';

// Validate filters
$valid_methods = ['all', 'cash', 'card', 'gcash'];
$valid_dates = ['today', 'week', 'month'];

if (!in_array($payment_method_filter, $valid_methods)) {
    $payment_method_filter = 'all';
}
if (!in_array($date_filter, $valid_dates)) {
    $date_filter = 'today';
}

// Build date query
$date_query = '';
switch ($date_filter) {
    case 'today':
        $date_query = "AND DATE(b.created_at) = CURDATE()";
        break;
    case 'week':
        $date_query = "AND b.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $date_query = "AND b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        break;
    default:
        $date_query = "AND DATE(b.created_at) = CURDATE()";
}

// Build payment method query
$method_query = '';
if ($payment_method_filter !== 'all') {
    $method_filter = $conn->real_escape_string($payment_method_filter);
    $method_query = "AND b.payment_method = '$method_filter'";
}

// Fetch transactions
$transactions_sql = "
    SELECT 
        b.id, b.booking_id, b.payment_method, b.estimated_fare, b.actual_fare,
        b.status, b.created_at,
        u.username as customer_name,
        d.first_name, d.last_name,
        rt.name as ride_type
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN drivers d ON b.driver_id = d.id
    LEFT JOIN ride_types rt ON b.ride_type_id = rt.id
    WHERE b.status IN ('completed', 'in_progress')
    $date_query
    $method_query
    ORDER BY b.created_at DESC
    LIMIT 50
";

$transactions = $conn->query($transactions_sql);

// Calculate statistics
$today_revenue = $conn->query("
    SELECT COALESCE(SUM(estimated_fare), 0) as total 
    FROM bookings 
    WHERE status IN ('completed', 'in_progress') 
    AND DATE(created_at) = CURDATE()
")->fetch_assoc()['total'];

$total_transactions = $conn->query("
    SELECT COUNT(*) as count 
    FROM bookings 
    WHERE status IN ('completed', 'in_progress')
    AND DATE(created_at) = CURDATE()
")->fetch_assoc()['count'];

$completed_transactions = $conn->query("
    SELECT COUNT(*) as count 
    FROM bookings 
    WHERE status = 'completed'
    AND DATE(created_at) = CURDATE()
")->fetch_assoc()['count'];

$pending_refunds = $conn->query("
    SELECT COUNT(*) as count FROM refunds WHERE status = 'pending'
")->fetch_assoc()['count'];

// Calculate payment method breakdown
$gcash_revenue = $conn->query("
    SELECT COALESCE(SUM(estimated_fare), 0) as total 
    FROM bookings 
    WHERE payment_method = 'gcash' 
    AND DATE(created_at) = CURDATE()
")->fetch_assoc()['total'];

$cash_revenue = $conn->query("
    SELECT COALESCE(SUM(estimated_fare), 0) as total 
    FROM bookings 
    WHERE payment_method = 'cash' 
    AND DATE(created_at) = CURDATE()
")->fetch_assoc()['total'];

$card_revenue = $conn->query("
    SELECT COALESCE(SUM(estimated_fare), 0) as total 
    FROM bookings 
    WHERE payment_method = 'card' 
    AND DATE(created_at) = CURDATE()
")->fetch_assoc()['total'];

$total_revenue = $today_revenue;
$gcash_percent = $total_revenue > 0 ? round(($gcash_revenue / $total_revenue) * 100) : 0;
$cash_percent = $total_revenue > 0 ? round(($cash_revenue / $total_revenue) * 100) : 0;
$card_percent = $total_revenue > 0 ? round(($card_revenue / $total_revenue) * 100) : 0;
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<main class="container">
    <div class="header-flex">
        <h1 class="dashboard-title">Payment Management</h1>
        <div class="system-badge">SYSTEM LIVE</div>
    </div>

    <!-- Revenue Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Today's Revenue</span>
            <span class="stat-value">₱<?php echo number_format($today_revenue, 0); ?></span>
            <span class="stat-trend trend-up">✓ Real-time</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Transactions</span>
            <span class="stat-value"><?php echo $total_transactions; ?></span>
            <span class="stat-sub"><?php echo $completed_transactions; ?> Completed</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Refunds</span>
            <span class="stat-value"><?php echo $pending_refunds; ?></span>
            <span class="stat-sub">Requires processing</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Avg Transaction</span>
            <span class="stat-value">₱<?php echo $total_transactions > 0 ? number_format($today_revenue / $total_transactions, 0) : 0; ?></span>
            <span class="stat-sub">Per ride</span>
        </div>
    </div>

    <!-- Payment Method Distribution -->
    <div class="main-content-layout">
        <div class="content-card">
            <div class="card-header">
                <h3>Transaction Ledger</h3>
                <div class="table-controls">
                    <select class="btn-secondary" style="padding: 8px 15px;" onchange="filterByMethod(this.value)">
                        <option value="all">All Methods</option>
                        <option value="cash" <?php echo $payment_method_filter === 'cash' ? 'selected' : ''; ?>>Cash</option>
                        <option value="card" <?php echo $payment_method_filter === 'card' ? 'selected' : ''; ?>>Card</option>
                        <option value="gcash" <?php echo $payment_method_filter === 'gcash' ? 'selected' : ''; ?>>GCash</option>
                    </select>
                    <select class="btn-secondary" style="padding: 8px 15px;" onchange="filterByDate(this.value)">
                        <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="month" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                    </select>
                    <button class="btn-secondary" style="padding: 8px 15px;" onclick="exportTransactions()">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
            </div>

            <?php if ($refund_msg): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
                <?php echo $refund_msg; ?>
            </div>
            <?php endif; ?>

            <?php if ($refund_error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
                <?php echo $refund_error; ?>
            </div>
            <?php endif; ?>

            <?php if ($transactions && $transactions->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="fleet-table">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>BOOKING ID</th>
                            <th>CUSTOMER</th>
                            <th>DRIVER</th>
                            <th>METHOD</th>
                            <th>AMOUNT</th>
                            <th>STATUS</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($txn = $transactions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, H:i', strtotime($txn['created_at'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($txn['booking_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($txn['customer_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars(($txn['first_name'] ?? 'N/A') . ' ' . ($txn['last_name'] ?? '')); ?></td>
                            <td>
                                <span class="method-badge badge-<?php echo strtolower($txn['payment_method']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($txn['payment_method'])); ?>
                                </span>
                            </td>
                            <td><strong>₱<?php echo number_format($txn['estimated_fare'], 2); ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo $txn['status']; ?>" style="padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                    <?php echo ucfirst(str_replace('_', ' ', $txn['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-refund" onclick="openRefundModal(<?php echo $txn['id']; ?>, '<?php echo htmlspecialchars($txn['booking_id']); ?>')">
                                    <i class="fas fa-undo"></i> Refund
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                <i class="fas fa-inbox" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                <strong>No transactions found</strong><br>
                <small>There are no transactions for the selected filters</small>
            </div>
            <?php endif; ?>
        </div>

        <div class="sidebar-card">
            <h3>Payment Method Split</h3>
            <div class="dist-item">
                <div class="dist-info">
                    <span>Cash</span>
                    <span>₱<?php echo number_format($cash_revenue, 0); ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress bg-blue" style="width: <?php echo $cash_percent; ?>%;"></div>
                </div>
                <small style="color: #888; font-size: 12px;"><?php echo $cash_percent; ?>% of total</small>
            </div>
            <div class="dist-item">
                <div class="dist-info">
                    <span>Card</span>
                    <span>₱<?php echo number_format($card_revenue, 0); ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $card_percent; ?>%; background: #8b5cf6;"></div>
                </div>
                <small style="color: #888; font-size: 12px;"><?php echo $card_percent; ?>% of total</small>
            </div>
            <div class="dist-item">
                <div class="dist-info">
                    <span>GCash</span>
                    <span>₱<?php echo number_format($gcash_revenue, 0); ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress bg-green" style="width: <?php echo $gcash_percent; ?>%;"></div>
                </div>
                <small style="color: #888; font-size: 12px;"><?php echo $gcash_percent; ?>% of total</small>
            </div>
            <hr style="margin: 15px 0; border: none; border-top: 1px solid #eee;">
            <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; text-align: center;">
                <small style="color: #666;">Total Revenue</small>
                <div style="font-size: 20px; font-weight: 700; color: #27ae60;">₱<?php echo number_format($total_revenue, 2); ?></div>
            </div>
        </div>
    </div>

    <!-- Refund Modal -->
    <div id="refundModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5);">
        <div style="background: white; margin: 10% auto; padding: 30px; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
            <span style="cursor: pointer; float: right; font-size: 24px; color: #999;" onclick="closeRefundModal()">&times;</span>
            <h3 style="margin-top: 0; color: #333;">Request Refund</h3>
            
            <form method="POST" id="refundForm" onsubmit="return validateRefundForm()">
                <input type="hidden" name="action" value="refund">
                <input type="hidden" name="booking_id" id="refundBookingId">

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 14px;">
                        Booking ID
                    </label>
                    <input type="text" id="refundBookingDisplay" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f5f5f5;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 14px;">
                        Reason for Refund <span style="color: red;">*</span>
                    </label>
                    <textarea name="reason" id="refundReason" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: Arial; min-height: 100px;" placeholder="Please explain the reason for refund..."></textarea>
                    <small id="charCount" style="color: #888; font-size: 12px; display: block; margin-top: 5px;">0/500 characters</small>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" style="flex: 1; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background 0.3s;">
                        <i class="fas fa-check"></i> Submit Refund Request
                    </button>
                    <button type="button" style="flex: 1; padding: 12px; background: #f0f0f0; color: #333; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background 0.3s;" onclick="closeRefundModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>


<style>
    .status-completed { background: #d1e7dd; color: #0f5132; }
    .status-in_progress { background: #d1ecf1; color: #0c5460; }
    .status-pending { background: #fff3cd; color: #856404; }
    
    .method-badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-cash { background: #cfe2ff; color: #084298; }
    .badge-card { background: #e2e3e5; color: #383d41; }
    .badge-gcash { background: #d1ecf1; color: #0c5460; }
    
    .btn-refund {
        background: #f8d7da;
        color: #721c24;
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: background 0.3s;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-refund:hover {
        background: #f5c6cb;
    }
</style>

<script>
    function openRefundModal(bookingId, bookingCode) {
        document.getElementById('refundBookingId').value = bookingId;
        document.getElementById('refundBookingDisplay').value = bookingCode;
        document.getElementById('refundReason').value = '';
        document.getElementById('charCount').textContent = '0/500 characters';
        document.getElementById('refundModal').style.display = 'flex';
        document.getElementById('refundModal').style.alignItems = 'center';
        document.getElementById('refundModal').style.justifyContent = 'center';
    }
    
    function closeRefundModal() {
        document.getElementById('refundModal').style.display = 'none';
        document.getElementById('refundForm').reset();
    }
    
    function validateRefundForm() {
        const reason = document.getElementById('refundReason').value.trim();
        
        if (reason.length < 5) {
            alert('Please provide a reason with at least 5 characters.');
            return false;
        }
        
        if (reason.length > 500) {
            alert('Reason cannot exceed 500 characters.');
            return false;
        }
        
        return true;
    }
    
    // Update character count in real-time
    document.addEventListener('DOMContentLoaded', function() {
        const reasonField = document.getElementById('refundReason');
        if (reasonField) {
            reasonField.addEventListener('input', function() {
                document.getElementById('charCount').textContent = this.value.length + '/500 characters';
            });
        }
    });
    
    function filterByMethod(method) {
        const currentDate = new URLSearchParams(window.location.search).get('date') || 'today';
        window.location.href = '?method=' + encodeURIComponent(method) + '&date=' + encodeURIComponent(currentDate);
    }
    
    function filterByDate(date) {
        const currentMethod = new URLSearchParams(window.location.search).get('method') || 'all';
        window.location.href = '?method=' + encodeURIComponent(currentMethod) + '&date=' + encodeURIComponent(date);
    }
    
    function exportTransactions() {
        let csv = 'Date,Booking ID,Customer,Driver,Payment Method,Amount,Status\n';
        document.querySelectorAll('.fleet-table tbody tr').forEach(row => {
            let cells = row.querySelectorAll('td');
            let rowData = [];
            cells.forEach((cell, index) => {
                if (index < 7) { // Skip action column
                    rowData.push('"' + cell.innerText.replace(/"/g, '""') + '"');
                }
            });
            csv += rowData.join(',') + '\n';
        });
        
        let blob = new Blob([csv], { type: 'text/csv' });
        let url = window.URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = 'transactions_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('refundModal');
        if (event.target == modal) {
            closeRefundModal();
        }
    }
</script>

<?php include('includes/footer.php'); ?>
