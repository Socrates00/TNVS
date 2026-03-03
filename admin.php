<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle add vehicle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vehicle'])) {
    $model = $_POST['model'];
    $plate = $_POST['plate'];
    $driver = $_POST['driver'];
    $status = $_POST['status'];
    $maintenance = $_POST['maintenance'];
    $stmt = $conn->prepare("INSERT INTO vehicles (model, plate_number, driver_name, status, last_maintenance) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $model, $plate, $driver, $status, $maintenance);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Handle update vehicle status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['vehicle_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE vehicles SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Fetch stats
$active_vehicles = $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE status = 'available'")->fetch_assoc()['count'];
$total_vehicles = $conn->query("SELECT COUNT(*) as count FROM vehicles")->fetch_assoc()['count'];
$on_trip = $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE status = 'on_trip'")->fetch_assoc()['count'];
$maintenance_vehicles = $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE status = 'maintenance'")->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM ride_requests WHERE status = 'pending'")->fetch_assoc()['count'];

// Fetch vehicles
$vehicles = $conn->query("SELECT * FROM vehicles LIMIT 10");
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        width: 90%;
        max-width: 400px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 20px;
    }

    .close-btn:hover,
    .close-btn:focus {
        color: black;
    }

    .modal-content h2 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 22px;
        color: #1a1a1a;
    }

    .modal-content .form-group {
        margin-bottom: 20px;
    }

    .modal-content label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .modal-content select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
    }

    .btn-edit {
        padding: 8px 16px;
        background-color: #3b5998;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        transition: 0.3s;
    }

    .btn-edit:hover {
        background-color: #2d4373;
        box-shadow: 0 4px 12px rgba(59, 89, 152, 0.3);
    }

    .btn-update {
        width: 100%;
        padding: 12px;
        background-color: #00b14f;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: 0.3s;
        margin-bottom: 10px;
    }

    .btn-update:hover {
        background-color: #009440;
        box-shadow: 0 4px 12px rgba(0, 177, 79, 0.3);
    }

    .btn-cancel {
        width: 100%;
        padding: 12px;
        background-color: #e0e0e0;
        color: #333;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: 0.3s;
    }

    .btn-cancel:hover {
        background-color: #d0d0d0;
    }

    .progress.bg-orange {
        background-color: #f97316;
    }
</style>

<main class="container">
    <div class="header-flex">
        <h1 class="dashboard-title">Fleet Management</h1>
        <div class="system-badge">SYSTEM LIVE</div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Active Vehicles</span>
            <span class="stat-value"><?php echo $active_vehicles; ?></span>
            <span class="stat-trend trend-up">Total: <?php echo $total_vehicles; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Trips Today</span>
            <span class="stat-value"><?php echo $on_trip; ?></span>
            <span class="stat-sub">Ongoing</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Daily Revenue</span>
            <span class="stat-value">₱24,500</span>
            <span class="stat-sub">Target: ₱30k</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Approvals</span>
            <span class="stat-value"><?php echo $pending_requests; ?></span>
            <span class="stat-trend trend-new">New requests</span>
        </div>
    </div>

    <div class="main-content-layout">
        <div class="content-card fleet-section">
            <div class="card-header">
                <h3>Active Fleet Management</h3>
                <button type="button" class="btn-add" onclick="toggleAddForm()">+ Add Vehicle</button>
            </div>

            <div id="add-vehicle-form" class="add-vehicle-form" style="display: none;">
                <h4>Add New Vehicle</h4>
                <form method="post" class="vehicle-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="model">Model</label>
                            <input type="text" id="model" name="model" required>
                        </div>
                        <div class="form-group">
                            <label for="plate">Plate Number</label>
                            <input type="text" id="plate" name="plate" required>
                        </div>
                        <div class="form-group">
                            <label for="driver">Driver Name</label>
                            <input type="text" id="driver" name="driver" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="available">Available</option>
                                <option value="on_trip">On Trip</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="maintenance">Last Maintenance</label>
                            <input type="date" id="maintenance" name="maintenance" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="add_vehicle" class="btn-add">Add Vehicle</button>
                        </div>
                    </div>
                </form>
            </div>
            <table class="fleet-table">
                <thead>
                    <tr>
                        <th>VEHICLE / PLATE</th>
                        <th>ASSIGNED DRIVER</th>
                        <th>STATUS</th>
                        <th>LAST MAINT.</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($vehicle['model']); ?></strong><br><small><?php echo htmlspecialchars($vehicle['plate_number']); ?></small></td>
                        <td><?php echo htmlspecialchars($vehicle['driver_name']); ?></td>
                        <td><span class="status-badge status-<?php echo str_replace('_', '-', $vehicle['status']); ?>"><?php echo strtoupper(str_replace('_', ' ', $vehicle['status'])); ?></span></td>
                        <td><?php echo htmlspecialchars($vehicle['last_maintenance']); ?></td>
                        <td>
                            <button type="button" class="btn-edit" onclick="openEditForm(<?php echo $vehicle['id']; ?>, '<?php echo htmlspecialchars($vehicle['status']); ?>')">Edit Status</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="sidebar-card">
            <h3>Fleet Distribution</h3>
            <div class="dist-item">
                <div class="dist-info"><span>Available</span><span><?php echo $active_vehicles; ?> Units</span></div>
                <div class="progress-bar"><div class="progress bg-green" style="width: <?php echo $total_vehicles > 0 ? ($active_vehicles / $total_vehicles * 100) : 0; ?>%;"></div></div>
            </div>
            <div class="dist-item">
                <div class="dist-info"><span>On Trip</span><span><?php echo $on_trip; ?> Units</span></div>
                <div class="progress-bar"><div class="progress bg-blue" style="width: <?php echo $total_vehicles > 0 ? ($on_trip / $total_vehicles * 100) : 0; ?>%;"></div></div>
            </div>
            <div class="dist-item">
                <div class="dist-info"><span>Maintenance</span><span><?php echo $maintenance_vehicles; ?> Units</span></div>
                <div class="progress-bar"><div class="progress bg-orange" style="width: <?php echo $total_vehicles > 0 ? ($maintenance_vehicles / $total_vehicles * 100) : 0; ?>%;"></div></div>
            </div>
        </div>
    </div>

    <div class="feature-menu">
        <h2 class="menu-title">Maintenance & Operations</h2>
        <div class="feature-grid">
            <a href="maintenance.php" class="feature-card maintenance-records" title="Maintenance Records">
                <div class="feature-icon">📋</div>
                <div class="feature-text">
                    <h4>Maintenance Records</h4>
                    <p>Add & view service history & upcoming services calendar</p>
                </div>
            </a>
            <a href="driver_profile.php" class="feature-card driver-management" title="Driver Profile Management">
                <div class="feature-icon">👤</div>
                <div class="feature-text">
                    <h4>Driver Profile Management</h4>
                    <p>Manage driver profiles & information</p>
                </div>
            </a>
            <a href="analytics.php" class="feature-card cost-analytics" title="Cost Analytics">
                <div class="feature-icon">📈</div>
                <div class="feature-text">
                    <h4>Cost Analytics</h4>
                    <p>Cost breakdown by vehicle</p>
                </div>
            </a>
            <a href="fuel_management.php" class="feature-card fuel-management" title="Fuel Management">
                <div class="feature-icon">⛽</div>
                <div class="feature-text">
                    <h4>Fuel Management</h4>
                    <p>Track fuel consumption & expenses</p>
                </div>
            </a>
        </div>
    </div>
</main>

<div id="edit-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeEditForm()">&times;</span>
        <h2>Edit Vehicle Status</h2>
        <form method="post" id="edit-form">
            <input type="hidden" name="vehicle_id" id="edit-vehicle-id">
            <div class="form-group">
                <label for="edit-status">Status</label>
                <select id="edit-status" name="status" required>
                    <option value="available">Available</option>
                    <option value="on_trip">On Trip</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <button type="submit" name="update_status" class="btn-update">Update Status</button>
            <button type="button" onclick="closeEditForm()" class="btn-cancel">Cancel</button>
        </form>
    </div>
</div>

<script>
function toggleAddForm() {
    var form = document.getElementById('add-vehicle-form');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        form.style.maxHeight = '0';
        setTimeout(() => {
            form.style.maxHeight = '500px';
            form.style.opacity = '1';
        }, 10);
    } else {
        form.style.maxHeight = '0';
        form.style.opacity = '0';
        setTimeout(() => {
            form.style.display = 'none';
        }, 300);
    }
}

function openEditForm(vehicleId, currentStatus) {
    document.getElementById('edit-modal').style.display = 'block';
    document.getElementById('edit-vehicle-id').value = vehicleId;
    document.getElementById('edit-status').value = currentStatus;
}

function closeEditForm() {
    document.getElementById('edit-modal').style.display = 'none';
}

window.onclick = function(event) {
    var modal = document.getElementById('edit-modal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php $conn->close(); ?>
