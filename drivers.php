<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle add driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_driver'])) {
    $license_number = $_POST['license_number'];
    $license_expiration = $_POST['license_expiration'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    
    $stmt = $conn->prepare("INSERT INTO drivers (license_number, license_expiration, first_name, last_name, phone, email) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $license_number, $license_expiration, $first_name, $last_name, $phone, $email);
    
    if ($stmt->execute()) {
        header("Location: drivers.php?success=1");
        exit();
    }
    $stmt->close();
}

// Handle delete driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_driver'])) {
    $id = $_POST['driver_id'];
    $stmt = $conn->prepare("DELETE FROM drivers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: drivers.php");
    exit();
}

// Handle clock in/out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shift_action'])) {
    $driver_id = $_POST['driver_id'];
    $vehicle_id = isset($_POST['vehicle_id']) ? $_POST['vehicle_id'] : null;
    $action = $_POST['shift_action'];
    
    if ($action === 'clock_in') {
        $stmt = $conn->prepare("INSERT INTO driver_shifts (driver_id, vehicle_id, shift_start, status) VALUES (?, ?, NOW(), 'clocked_in')");
        $stmt->bind_param("ii", $driver_id, $vehicle_id);
    } else {
        // Get the current clocked-in shift
        $stmt = $conn->prepare("UPDATE driver_shifts SET shift_end = NOW(), status = 'clocked_out' WHERE driver_id = ? AND status = 'clocked_in' ORDER BY shift_start DESC LIMIT 1");
        $stmt->bind_param("i", $driver_id);
    }
    
    $stmt->execute();
    $stmt->close();
    header("Location: drivers.php");
    exit();
}

// Fetch drivers with current shift status
$drivers = $conn->query("
    SELECT d.*, 
        (SELECT status FROM driver_shifts WHERE driver_id = d.id ORDER BY shift_start DESC LIMIT 1) as current_shift_status,
        DATEDIFF(d.license_expiration, CURDATE()) as days_until_license_expiry
    FROM drivers d 
    ORDER BY d.created_at DESC
");

// Get drivers with expiring licenses (within 30 days)
$expiring_licenses = $conn->query("
    SELECT d.id, d.first_name, d.last_name, d.license_number, d.license_expiration,
        DATEDIFF(d.license_expiration, CURDATE()) as days_remaining
    FROM drivers d
    WHERE d.license_expiration IS NOT NULL 
    AND DATEDIFF(d.license_expiration, CURDATE()) BETWEEN 0 AND 30
    ORDER BY d.license_expiration ASC
");

// Get clocked-in drivers count
$clocked_in = $conn->query("
    SELECT COUNT(DISTINCT driver_id) as count 
    FROM driver_shifts 
    WHERE status = 'clocked_in'
")->fetch_assoc()['count'];

$total_drivers = $conn->query("SELECT COUNT(*) as count FROM drivers")->fetch_assoc()['count'];
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .drivers-container { max-width: 1200px; margin: 20px auto; }
    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .alert-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    .alert-box h4 { margin: 0 0 10px 0; color: #856404; }
    .alert-item { padding: 5px 0; color: #856404; font-size: 14px; }
    .table-container { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .license-expired { background: #f8d7da; color: #721c24; }
    .license-warning { background: #fff3cd; color: #856404; }
    .license-good { background: #d4edda; color: #155724; }
    .action-buttons { display: flex; gap: 10px; }
    .btn-clock { padding: 6px 12px; font-size: 12px; border: none; border-radius: 4px; cursor: pointer; }
    .btn-clock-in { background: #28a745; color: white; }
    .btn-clock-in:hover { background: #218838; }
    .btn-clock-out { background: #dc3545; color: white; }
    .btn-clock-out:hover { background: #c82333; }
    .btn-edit { background: #007bff; color: white; }
    .btn-edit:hover { background: #0056b3; }
</style>

<main class="drivers-container">
    <div class="header-flex">
        <h1 class="dashboard-title">Driver Management System</h1>
        <button type="button" class="btn-add" onclick="toggleAddForm()">+ Add Driver</button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="stat-label">Total Drivers</span>
            <span class="stat-value"><?php echo $total_drivers; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Clocked In</span>
            <span class="stat-value" style="color: #28a745;"><?php echo $clocked_in; ?></span>
        </div>
    </div>

    <!-- Expiring Licenses Alert -->
    <?php if ($expiring_licenses && $expiring_licenses->num_rows > 0): ?>
    <div class="alert-box">
        <h4>⚠️ Licenses Expiring Soon</h4>
        <?php while ($lic = $expiring_licenses->fetch_assoc()): ?>
            <div class="alert-item">
                <strong><?php echo htmlspecialchars($lic['first_name'] . ' ' . $lic['last_name']); ?></strong> 
                (<?php echo htmlspecialchars($lic['license_number']); ?>) - 
                <?php echo $lic['days_remaining'] <= 0 ? 'EXPIRED' : 'Expires in ' . $lic['days_remaining'] . ' days'; ?>
            </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <!-- Add Driver Form -->
    <div id="add-driver-form" class="table-container" style="display: none;">
        <h3>Add New Driver</h3>
        <form method="post" style="display: grid; gap: 15px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div>
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label for="license_number">License Number *</label>
                    <input type="text" id="license_number" name="license_number" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label for="license_expiration">License Expiration *</label>
                    <input type="date" id="license_expiration" name="license_expiration" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="add_driver" class="btn-add">Add Driver</button>
                <button type="button" onclick="toggleAddForm()" class="btn-remove">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Drivers Table -->
    <div class="table-container">
        <h3>Active Drivers</h3>
        <table class="fleet-table">
            <thead>
                <tr>
                    <th>DRIVER NAME</th>
                    <th>LICENSE #</th>
                    <th>LICENSE EXPIRY</th>
                    <th>PHONE</th>
                    <th>STATUS</th>
                    <th>RATING</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($driver = $drivers->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($driver['license_number']); ?></td>
                    <td>
                        <span class="license-<?php 
                            $days = $driver['days_until_license_expiry'];
                            echo ($days <= 0) ? 'expired' : (($days <= 30) ? 'warning' : 'good'); 
                        ?>">
                            <?php echo htmlspecialchars(date('M d, Y', strtotime($driver['license_expiration']))); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($driver['phone']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $driver['current_shift_status'] === 'clocked_in' ? 'on-trip' : 'available'; ?>">
                            <?php echo $driver['current_shift_status'] === 'clocked_in' ? 'CLOCKED IN' : 'CLOCKED OUT'; ?>
                        </span>
                    </td>
                    <td>⭐ <?php echo number_format($driver['performance_rating'], 2); ?>/5.00</td>
                    <td>
                        <div class="action-buttons">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="driver_id" value="<?php echo $driver['id']; ?>">
                                <?php if ($driver['current_shift_status'] === 'clocked_in'): ?>
                                    <button type="submit" name="shift_action" value="clock_out" class="btn-clock btn-clock-out">Clock Out</button>
                                <?php else: ?>
                                    <button type="submit" name="shift_action" value="clock_in" class="btn-clock btn-clock-in">Clock In</button>
                                <?php endif; ?>
                            </form>
                            <a href="driver_profile.php?id=<?php echo $driver['id']; ?>" class="btn-edit" style="padding: 6px 12px; text-decoration: none; font-size: 12px; border-radius: 4px; display: inline-block;">View Profile</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function toggleAddForm() {
    var form = document.getElementById('add-driver-form');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}
</script>

<?php $conn->close(); ?>
