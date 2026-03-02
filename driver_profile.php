<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create drivers table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS drivers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    driver_id VARCHAR(20) UNIQUE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    contact_number VARCHAR(20),
    license_number VARCHAR(50) UNIQUE,
    license_expiration DATE,
    status ENUM('active', 'on_trip', 'suspended') DEFAULT 'active',
    assigned_vehicle_id INT,
    total_trips INT DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 5.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
)");

// Handle add driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_driver'])) {
    $driver_id = $_POST['driver_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_number = $_POST['contact_number'];
    $license_number = $_POST['license_number'];
    $license_expiration = $_POST['license_expiration'];
    
    $stmt = $conn->prepare("INSERT INTO drivers (driver_id, first_name, last_name, contact_number, license_number, license_expiration, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("ssssss", $driver_id, $first_name, $last_name, $contact_number, $license_number, $license_expiration);
    $stmt->execute();
    $stmt->close();
    header("Location: driver_profile.php");
    exit();
}

// Handle update driver status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $driver_id = $_POST['driver_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE drivers SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $driver_id);
    $stmt->execute();
    $stmt->close();
    header("Location: driver_profile.php");
    exit();
}

// Handle assign vehicle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_vehicle'])) {
    $driver_id = $_POST['driver_id'];
    $vehicle_id = $_POST['vehicle_id'];
    
    $stmt = $conn->prepare("UPDATE drivers SET assigned_vehicle_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $vehicle_id, $driver_id);
    $stmt->execute();
    $stmt->close();
    header("Location: driver_profile.php");
    exit();
}

// Handle suspend driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suspend_driver'])) {
    $driver_id = $_POST['driver_id'];
    
    $stmt = $conn->prepare("UPDATE drivers SET status = 'suspended' WHERE id = ?");
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $stmt->close();
    header("Location: driver_profile.php");
    exit();
}

// Handle activate driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_driver'])) {
    $driver_id = $_POST['driver_id'];
    
    $stmt = $conn->prepare("UPDATE drivers SET status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $stmt->close();
    header("Location: driver_profile.php");
    exit();
}

// Fetch all drivers
$drivers = $conn->query("SELECT d.*, v.model, v.plate_number FROM drivers d LEFT JOIN vehicles v ON d.assigned_vehicle_id = v.id ORDER BY d.created_at DESC");

// Fetch available vehicles
$vehicles = $conn->query("SELECT id, model, plate_number FROM vehicles ORDER BY model");

// Count stats
$total_drivers = $conn->query("SELECT COUNT(*) as count FROM drivers")->fetch_assoc()['count'];
$active_drivers = $conn->query("SELECT COUNT(*) as count FROM drivers WHERE status = 'active'")->fetch_assoc()['count'];
$on_trip_drivers = $conn->query("SELECT COUNT(*) as count FROM drivers WHERE status = 'on_trip'")->fetch_assoc()['count'];
$suspended_drivers = $conn->query("SELECT COUNT(*) as count FROM drivers WHERE status = 'suspended'")->fetch_assoc()['count'];
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .driver-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .page-header h1 {
        font-size: 2rem;
        color: #1a1a1a;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border-left: 4px solid #00b14f;
    }

    .stat-label {
        font-size: 13px;
        color: #999;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .add-form-section {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .form-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 15px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .form-group input,
    .form-group select {
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: border 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #00b14f;
        box-shadow: 0 0 0 3px rgba(0, 177, 79, 0.1);
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-submit {
        background: #00b14f;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: #009440;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 177, 79, 0.3);
    }

    .drivers-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .drivers-table thead {
        background: #f8fafb;
        border-bottom: 2px solid #f0f0f0;
    }

    .drivers-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .drivers-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        color: #666;
    }

    .drivers-table tbody tr:hover {
        background: #f8fafb;
    }

    .driver-name {
        font-weight: 600;
        color: #1a1a1a;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-active {
        background: #e8fdf0;
        color: #009440;
    }

    .status-on-trip {
        background: #e3f2fd;
        color: #1976d2;
    }

    .status-suspended {
        background: #ffebee;
        color: #c62828;
    }

    .rating-stars {
        color: #ffc107;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-edit {
        padding: 6px 12px;
        background: #3b5998;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-edit:hover {
        background: #2d4373;
    }

    .btn-suspend {
        padding: 6px 12px;
        background: #f97316;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-suspend:hover {
        background: #d85f0d;
    }

    .btn-activate {
        padding: 6px 12px;
        background: #00b14f;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-activate:hover {
        background: #009440;
    }

    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        display: none;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        width: 90%;
        max-width: 500px;
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-btn:hover {
        color: black;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 15px;
        background: #f0f0f0;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        color: #333;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .back-button:hover {
        background: #00b14f;
        color: white;
    }
</style>

<main class="driver-container">
    <div class="page-header">
        <h1>👤 Driver Profile Management</h1>
        <a href="admin.php" class="back-button">← Back to Dashboard</a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">Total Drivers</div>
            <div class="stat-value"><?php echo $total_drivers; ?></div>
        </div>
        <div class="stat-box" style="border-left-color: #00b14f;">
            <div class="stat-label">Active</div>
            <div class="stat-value"><?php echo $active_drivers; ?></div>
        </div>
        <div class="stat-box" style="border-left-color: #1976d2;">
            <div class="stat-label">On Trip</div>
            <div class="stat-value"><?php echo $on_trip_drivers; ?></div>
        </div>
        <div class="stat-box" style="border-left-color: #f97316;">
            <div class="stat-label">Suspended</div>
            <div class="stat-value"><?php echo $suspended_drivers; ?></div>
        </div>
    </div>

    <!-- Add Driver Form -->
    <div class="add-form-section">
        <div class="section-header">1️⃣ Driver Info (Required)</div>
        <form method="post" class="driver-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="driver_id">Driver ID *</label>
                    <input type="text" id="driver_id" name="driver_id" placeholder="e.g., DRV001" required>
                </div>
                <div class="form-group">
                    <label for="first_name">Full Name (First) *</label>
                    <input type="text" id="first_name" name="first_name" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Last Name" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contact_number">Contact Number *</label>
                    <input type="tel" id="contact_number" name="contact_number" placeholder="09XXXXXXXXX" required>
                </div>
                <div class="form-group">
                    <label for="license_number">License Number *</label>
                    <input type="text" id="license_number" name="license_number" placeholder="License Number" required>
                </div>
                <div class="form-group">
                    <label for="license_expiration">License Expiry Date *</label>
                    <input type="date" id="license_expiration" name="license_expiration" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="add_driver" class="btn-submit">✓ Add Driver</button>
            </div>
        </form>
    </div>

    <!-- Drivers Table -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <div class="section-header">👥 All Drivers</div>
        <table class="drivers-table">
            <thead>
                <tr>
                    <th>DRIVER INFO</th>
                    <th>LICENSE</th>
                    <th>STATUS</th>
                    <th>ASSIGNED VEHICLE</th>
                    <th>🎯 PERFORMANCE</th>
                    <th>⭐ RATING</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($driver = $drivers->fetch_assoc()): ?>
                <tr>
                    <!-- Driver Info -->
                    <td>
                        <div class="driver-name"><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></div>
                        <div style="font-size: 0.85rem; color: #999;">ID: <?php echo htmlspecialchars($driver['driver_id']); ?></div>
                        <div style="font-size: 0.85rem; color: #999;">📞 <?php echo htmlspecialchars($driver['contact_number']); ?></div>
                    </td>

                    <!-- License Info -->
                    <td>
                        <div style="font-size: 0.9rem;"><?php echo htmlspecialchars($driver['license_number']); ?></div>
                        <div style="font-size: 0.85rem; color: #999;">
                            <?php 
                            $expiry = new DateTime($driver['license_expiration']);
                            $today = new DateTime();
                            $days = $today->diff($expiry)->days;
                            if ($expiry < $today) {
                                echo '<span style="color: #c62828; font-weight: 600;">EXPIRED</span>';
                            } else {
                                echo "Expires: " . $expiry->format('M d, Y');
                            }
                            ?>
                        </div>
                    </td>

                    <!-- Status -->
                    <td>
                        <span class="status-badge status-<?php echo str_replace('_', '-', $driver['status']); ?>">
                            <?php 
                            if ($driver['status'] === 'active') echo '✓ Available';
                            elseif ($driver['status'] === 'on_trip') echo '🚗 On Trip';
                            elseif ($driver['status'] === 'suspended') echo '⛔ Suspended';
                            ?>
                        </span>
                    </td>

                    <!-- Assigned Vehicle -->
                    <td>
                        <?php if ($driver['model']): ?>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($driver['model']); ?></div>
                            <div style="font-size: 0.85rem; color: #999;"><?php echo htmlspecialchars($driver['plate_number']); ?></div>
                        <?php else: ?>
                            <span style="color: #999;">Not Assigned</span>
                        <?php endif; ?>
                    </td>

                    <!-- Performance -->
                    <td>
                        <div style="font-size: 0.9rem;">Total Trips: <strong><?php echo $driver['total_trips']; ?></strong></div>
                    </td>

                    <!-- Rating -->
                    <td>
                        <div class="rating-stars">
                            <?php 
                            $rating = intval($driver['average_rating']);
                            for ($i = 0; $i < $rating; $i++) {
                                echo '★';
                            }
                            for ($i = $rating; $i < 5; $i++) {
                                echo '☆';
                            }
                            ?>
                        </div>
                        <div style="font-size: 0.85rem;"><?php echo $driver['average_rating']; ?>/5.00</div>
                    </td>

                    <!-- Admin Actions -->
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="btn-edit" onclick="openEditModal(<?php echo $driver['id']; ?>, '<?php echo htmlspecialchars($driver['assigned_vehicle_id'] ?? ''); ?>')">Edit</button>
                            <?php if ($driver['status'] !== 'suspended'): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="driver_id" value="<?php echo $driver['id']; ?>">
                                    <button type="submit" name="suspend_driver" class="btn-suspend" onclick="return confirm('Suspend this driver?')">Suspend</button>
                                </form>
                            <?php else: ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="driver_id" value="<?php echo $driver['id']; ?>">
                                    <button type="submit" name="activate_driver" class="btn-activate">Activate</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeEditModal()">&times;</span>
        <h2>2️⃣ Status & Assignment</h2>

        <!-- Status Update -->
        <form method="post" style="margin-bottom: 20px;">
            <input type="hidden" id="modal_driver_id" name="driver_id">
            <div class="form-group">
                <label for="modal_status">Driver Status</label>
                <select id="modal_status" name="status" required>
                    <option value="active">✓ Available</option>
                    <option value="on_trip">🚗 On Trip</option>
                    <option value="suspended">⛔ Suspended</option>
                </select>
            </div>
            <button type="submit" name="update_status" class="btn-submit" style="width: 100%; margin-top: 10px;">Update Status</button>
        </form>

        <!-- Assign Vehicle -->
        <form method="post">
            <input type="hidden" id="modal_driver_id_vehicle" name="driver_id">
            <div class="form-group">
                <label for="modal_vehicle">Assigned Vehicle (Plate No / Model)</label>
                <select id="modal_vehicle" name="vehicle_id" required>
                    <option value="">-- Select Vehicle --</option>
                    <?php 
                    $vehicles->data_seek(0);
                    while ($vehicle = $vehicles->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $vehicle['id']; ?>">
                            <?php echo htmlspecialchars($vehicle['model'] . ' (' . $vehicle['plate_number'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="assign_vehicle" class="btn-submit" style="width: 100%; margin-top: 10px;">Assign Vehicle</button>
        </form>
    </div>
</div>

<script>
    function openEditModal(driverId, vehicleId) {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('modal_driver_id').value = driverId;
        document.getElementById('modal_driver_id_vehicle').value = driverId;
        if (vehicleId) {
            document.getElementById('modal_vehicle').value = vehicleId;
        }
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    window.onclick = function(event) {
        var modal = document.getElementById('editModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

<?php $conn->close(); ?>
