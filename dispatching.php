<?php 
include('session_check.php');
include('includes/header.php');

$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle success/error messages
$success_msg = '';
$error_msg = '';

if (isset($_GET['assigned'])) {
    $success_msg = '✓ Driver assigned successfully!';
}
if (isset($_GET['rejected'])) {
    $success_msg = '✓ Booking rejected!';
}

// ===== HANDLE DRIVER ASSIGNMENT =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $booking_id = intval($_POST['booking_id']);
    
    if ($action === 'assign') {
        $driver_id = intval($_POST['driver_id']);
        
        // Verify booking and driver exist
        $booking_check = $conn->query("SELECT id FROM bookings WHERE id = $booking_id");
        $driver_check = $conn->query("SELECT id FROM drivers WHERE id = $driver_id");
        
        if ($booking_check->num_rows > 0 && $driver_check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE bookings SET driver_id = ?, status = 'accepted' WHERE id = ?");
            $stmt->bind_param("ii", $driver_id, $booking_id);
            if ($stmt->execute()) {
                $success_msg = '✓ Driver assigned successfully!';
            } else {
                $error_msg = 'Error assigning driver: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_msg = 'Invalid booking or driver';
        }
    } 
    elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $success_msg = '✓ Booking cancelled!';
        } else {
            $error_msg = 'Error cancelling booking';
        }
        $stmt->close();
    }
    elseif ($action === 'start_trip') {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'in_progress' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $success_msg = '✓ Trip started!';
        }
        $stmt->close();
    }
    elseif ($action === 'complete_trip') {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'completed', completed_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            // Record payment when trip is completed
            recordPaymentRecord($conn, $booking_id);
            $success_msg = '✓ Trip completed!';
        }
        $stmt->close();
    }
}

// ===== HELPER FUNCTION TO RECORD PAYMENT =====
function recordPaymentRecord($conn, $booking_id) {
    // Get booking details
    $stmt = $conn->prepare("
        SELECT b.id, b.user_id, b.estimated_fare, b.payment_method, 
               b.pickup_location, b.destination_location,
               rt.name as ride_type
        FROM bookings b
        LEFT JOIN ride_types rt ON b.ride_type_id = rt.id
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
    
    if ($booking && $booking['user_id']) {
        // Insert payment record
        $insert_stmt = $conn->prepare("
            INSERT INTO customer_payments 
            (booking_id, user_id, ride_type, pickup_location, destination_location, amount, payment_method, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')
        ");
        
        $insert_stmt->bind_param(
            "iisssds",
            $booking_id,
            $booking['user_id'],
            $booking['ride_type'],
            $booking['pickup_location'],
            $booking['destination_location'],
            $booking['estimated_fare'],
            $booking['payment_method']
        );
        
        $insert_stmt->execute();
        $insert_stmt->close();
    }
}

// ===== FETCH STATISTICS =====
$pending_count = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch_assoc()['count'];
$accepted_count = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'accepted'")->fetch_assoc()['count'];
$in_progress_count = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'in_progress'")->fetch_assoc()['count'];
$completed_today = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
$available_drivers = $conn->query("SELECT COUNT(*) as count FROM drivers WHERE status = 'active'")->fetch_assoc()['count'];
$on_trip_drivers = $conn->query("SELECT COUNT(*) as count FROM drivers WHERE status = 'on_trip'")->fetch_assoc()['count'];

// ===== FETCH PENDING RIDE REQUESTS =====
$pending_bookings = $conn->query("
    SELECT 
        b.id, b.booking_id, b.pickup_location, b.destination_location, 
        b.estimated_distance, b.estimated_fare, b.created_at, b.payment_method,
        u.username as customer_name, u.id as user_id,
        rt.name as ride_type
    FROM bookings b 
    LEFT JOIN users u ON b.user_id = u.id 
    LEFT JOIN ride_types rt ON b.ride_type_id = rt.id 
    WHERE b.status = 'pending' 
    ORDER BY b.created_at DESC 
    LIMIT 20
");

// ===== FETCH ONGOING TRIPS (ACCEPTED & IN_PROGRESS) =====
$active_trips = $conn->query("
    SELECT 
        b.id, b.booking_id, b.pickup_location, b.destination_location,
        b.estimated_distance, b.status, b.created_at,
        u.username as customer_name,
        d.first_name, d.last_name, d.id as driver_id,
        rt.name as ride_type
    FROM bookings b 
    LEFT JOIN users u ON b.user_id = u.id 
    LEFT JOIN drivers d ON b.driver_id = d.id 
    LEFT JOIN ride_types rt ON b.ride_type_id = rt.id 
    WHERE b.status IN ('accepted', 'in_progress') 
    ORDER BY b.created_at DESC 
    LIMIT 20
");

// ===== FETCH AVAILABLE DRIVERS =====
$available_drivers_list = $conn->query("
    SELECT id, first_name, last_name, average_rating, contact_number
    FROM drivers 
    WHERE status = 'active' 
    ORDER BY first_name ASC
");
?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .assignment-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .assignment-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }
    
    .modal-content h3 {
        margin-top: 0;
        margin-bottom: 20px;
        color: #333;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
        font-size: 14px;
    }
    
    .form-group select,
    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    
    .btn-primary {
        flex: 1;
        padding: 12px;
        background: var(--hero-green, #27ae60);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: background 0.3s;
    }
    
    .btn-primary:hover {
        background: #229954;
    }
    
    .btn-secondary {
        flex: 1;
        padding: 12px;
        background: #f0f0f0;
        color: #333;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: background 0.3s;
    }
    
    .btn-secondary:hover {
        background: #e0e0e0;
    }
    
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .booking-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    
    .booking-id {
        font-weight: 700;
        color: var(--hero-green, #27ae60);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .booking-customer {
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-accepted {
        background: #cfe2ff;
        color: #084298;
    }
    
    .status-in-progress {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-completed {
        background: #d1e7dd;
        color: #0f5132;
    }
    
    .booking-route {
        font-size: 13px;
        color: #666;
        margin-bottom: 10px;
        line-height: 1.5;
    }
    
    .route-icon {
        color: var(--hero-green, #27ae60);
        margin-right: 5px;
    }
    
    .booking-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 12px;
        font-size: 13px;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .detail-label {
        color: #999;
        font-weight: 500;
    }
    
    .detail-value {
        color: #333;
        font-weight: 600;
    }
    
    .booking-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }
    
    .btn-action {
        flex: 1;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-assign {
        background: var(--hero-green, #27ae60);
        color: white;
    }
    
    .btn-assign:hover {
        background: #229954;
    }
    
    .btn-reject {
        background: #f8d7da;
        color: #721c24;
    }
    
    .btn-reject:hover {
        background: #f5c6cb;
    }
    
    .btn-action-sm {
        padding: 6px 10px;
        font-size: 11px;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 700;
        margin: 30px 0 20px 0;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .stat-label {
        display: block;
        font-size: 12px;
        color: #999;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    
    .stat-value {
        display: block;
        font-size: 32px;
        font-weight: 700;
        color: var(--hero-green, #27ae60);
        margin-bottom: 8px;
    }
    
    .stat-sub {
        display: block;
        font-size: 12px;
        color: #666;
    }
    
    .no-data {
        text-align: center;
        padding: 40px 20px;
        color: #999;
        background: white;
        border-radius: 8px;
        border: 1px dashed #ddd;
    }
    
    .close-modal {
        cursor: pointer;
        float: right;
        font-size: 24px;
        color: #999;
    }
    
    .close-modal:hover {
        color: #333;
    }
</style>

<main class="container" style="padding: 20px;">
    <!-- Alerts -->
    <?php if ($success_msg): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <?php echo $success_msg; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error_msg): ?>
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <?php echo $error_msg; ?>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 class="dashboard-title" style="margin-bottom: 5px;">Taxi Dispatching System</h1>
            <p style="font-size: 13px; color: #999;">Real-time ride management and driver assignment</p>
        </div>
        <div class="system-badge" style="background: #27ae60; color: white; padding: 10px 20px; border-radius: 6px;">● SYSTEM LIVE</div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Pending Requests</span>
            <span class="stat-value"><?php echo $pending_count; ?></span>
            <span class="stat-sub">Awaiting assignment</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Active Drivers</span>
            <span class="stat-value"><?php echo $available_drivers; ?></span>
            <span class="stat-sub">Available for dispatch</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Ongoing Trips</span>
            <span class="stat-value"><?php echo $in_progress_count + $accepted_count; ?></span>
            <span class="stat-sub">Currently in service</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Completed Today</span>
            <span class="stat-value"><?php echo $completed_today; ?></span>
            <span class="stat-sub">Successfully delivered</span>
        </div>
    </div>

    <!-- ===== PENDING RIDE REQUESTS ===== -->
    <div class="section-title">
        <i class="fas fa-exclamation-circle" style="color: #ff9800;"></i>
        Pending Ride Requests (<?php echo $pending_count; ?>)
    </div>

    <?php if ($pending_bookings && $pending_bookings->num_rows > 0): ?>
        <?php while ($booking = $pending_bookings->fetch_assoc()): ?>
        <div class="booking-card">
            <div class="booking-header">
                <div>
                    <div class="booking-id">REQ #<?php echo htmlspecialchars($booking['booking_id']); ?></div>
                    <div class="booking-customer"><?php echo htmlspecialchars($booking['customer_name'] ?? 'Unknown Customer'); ?></div>
                </div>
                <span class="status-badge status-pending">PENDING</span>
            </div>

            <div class="booking-route">
                <i class="fas fa-circle-dot route-icon"></i> 
                <strong><?php echo htmlspecialchars(substr($booking['pickup_location'], 0, 40)); ?></strong>
                <br>
                <i class="fas fa-location-dot route-icon" style="color: #f44336;"></i> 
                <strong><?php echo htmlspecialchars(substr($booking['destination_location'], 0, 40)); ?></strong>
            </div>

            <div class="booking-details">
                <div class="detail-item">
                    <span class="detail-label">Ride Type:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['ride_type'] ?? 'Standard'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Est. Fare:</span>
                    <span class="detail-value">₱<?php echo number_format($booking['estimated_fare'], 2); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Distance:</span>
                    <span class="detail-value"><?php echo number_format($booking['estimated_distance'], 2); ?> km</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment:</span>
                    <span class="detail-value"><?php echo ucfirst(htmlspecialchars($booking['payment_method'])); ?></span>
                </div>
            </div>

            <div class="booking-actions">
                <button class="btn-action btn-assign" onclick="openAssignModal(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['customer_name']); ?>')">
                    <i class="fas fa-handshake"></i> Assign Driver
                </button>
                <button class="btn-action btn-reject" onclick="rejectBooking(<?php echo $booking['id']; ?>)">
                    <i class="fas fa-xmark"></i> Reject
                </button>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-data">
            <i class="fas fa-check-circle" style="font-size: 32px; color: #27ae60; margin-bottom: 10px; display: block;"></i>
            <strong>No pending requests</strong><br>
            <small>All ride requests have been assigned or completed</small>
        </div>
    <?php endif; ?>

    <!-- ===== ONGOING TRIPS ===== -->
    <div class="section-title">
        <i class="fas fa-car" style="color: #2196F3;"></i>
        Ongoing Trips (<?php echo $in_progress_count + $accepted_count; ?>)
    </div>

    <?php if ($active_trips && $active_trips->num_rows > 0): ?>
        <?php while ($trip = $active_trips->fetch_assoc()): ?>
        <div class="booking-card">
            <div class="booking-header">
                <div>
                    <div class="booking-id">TRIP #<?php echo htmlspecialchars($trip['booking_id']); ?></div>
                    <div class="booking-customer"><?php echo htmlspecialchars($trip['customer_name'] ?? 'Unknown'); ?></div>
                </div>
                <span class="status-badge status-<?php echo str_replace('_', '-', $trip['status']); ?>">
                    <?php echo strtoupper(str_replace('_', ' ', $trip['status'])); ?>
                </span>
            </div>

            <div class="booking-route">
                <i class="fas fa-circle-dot route-icon"></i> 
                <strong><?php echo htmlspecialchars(substr($trip['pickup_location'], 0, 40)); ?></strong>
                <br>
                <i class="fas fa-location-dot route-icon" style="color: #f44336;"></i> 
                <strong><?php echo htmlspecialchars(substr($trip['destination_location'], 0, 40)); ?></strong>
            </div>

            <div class="booking-details">
                <div class="detail-item">
                    <span class="detail-label">Driver:</span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars(($trip['first_name'] ?? 'N/A') . ' ' . ($trip['last_name'] ?? '')); ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Ride Type:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($trip['ride_type'] ?? 'Standard'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Distance:</span>
                    <span class="detail-value"><?php echo number_format($trip['estimated_distance'], 2); ?> km</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Time:</span>
                    <span class="detail-value"><?php echo date('H:i', strtotime($trip['created_at'])); ?></span>
                </div>
            </div>

            <div class="booking-actions">
                <?php if ($trip['status'] === 'accepted'): ?>
                <form method="POST" style="display: flex; gap: 8px; width: 100%;">
                    <input type="hidden" name="action" value="start_trip">
                    <input type="hidden" name="booking_id" value="<?php echo $trip['id']; ?>">
                    <button type="submit" class="btn-action btn-assign" style="flex: 1;">
                        <i class="fas fa-play"></i> Start Trip
                    </button>
                </form>
                <?php elseif ($trip['status'] === 'in_progress'): ?>
                <form method="POST" style="display: flex; gap: 8px; width: 100%;">
                    <input type="hidden" name="action" value="complete_trip">
                    <input type="hidden" name="booking_id" value="<?php echo $trip['id']; ?>">
                    <button type="submit" class="btn-action btn-assign" style="flex: 1;">
                        <i class="fas fa-check"></i> Complete Trip
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-data">
            <i class="fas fa-inbox" style="font-size: 32px; color: #999; margin-bottom: 10px; display: block;"></i>
            <strong>No active trips</strong><br>
            <small>There are currently no ongoing trips</small>
        </div>
    <?php endif; ?>

</main>

<!-- Assignment Modal -->
<div id="assignmentModal" class="assignment-modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeAssignModal()">&times;</span>
        <h3><i class="fas fa-user-tie" style="color: var(--hero-green, #27ae60); margin-right: 10px;"></i> Assign Driver</h3>
        
        <form method="POST" id="assignmentForm">
            <input type="hidden" name="action" value="assign">
            <input type="hidden" name="booking_id" id="assignBookingId">

            <div class="form-group">
                <label>Customer</label>
                <input type="text" id="customerDisplay" readonly style="background: #f5f5f5;">
            </div>

            <div class="form-group">
                <label for="driverSelect">Select Driver <span style="color: red;">*</span></label>
                <select name="driver_id" id="driverSelect" required>
                    <option value="">-- Choose a driver --</option>
                    <?php 
                    if ($available_drivers_list && $available_drivers_list->num_rows > 0):
                        $available_drivers_list->data_seek(0);
                        while ($driver = $available_drivers_list->fetch_assoc()): ?>
                    <option value="<?php echo $driver['id']; ?>">
                        <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?> 
                        <?php if ($driver['average_rating']): ?>
                            (⭐ <?php echo number_format($driver['average_rating'], 2); ?>)
                        <?php endif; ?>
                    </option>
                    <?php endwhile; else: ?>
                    <option value="">No drivers available</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check"></i> Assign Driver
                </button>
                <button type="button" class="btn-secondary" onclick="closeAssignModal()">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignModal(bookingId, customerName) {
    document.getElementById('assignBookingId').value = bookingId;
    document.getElementById('customerDisplay').value = customerName;
    document.getElementById('assignmentModal').classList.add('active');
    document.getElementById('driverSelect').focus();
}

function closeAssignModal() {
    document.getElementById('assignmentModal').classList.remove('active');
}

function rejectBooking(bookingId) {
    if (confirm('Are you sure you want to reject this booking?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="booking_id" value="${bookingId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('assignmentModal');
    if (event.target == modal) {
        closeAssignModal();
    }
}
</script>

<?php include('includes/footer.php'); ?>
