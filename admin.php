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

// Handle delete vehicle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vehicle'])) {
    $id = $_POST['vehicle_id'];
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Fetch stats
$active_vehicles = $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE status = 'available'")->fetch_assoc()['count'];
$total_vehicles = $conn->query("SELECT COUNT(*) as count FROM vehicles")->fetch_assoc()['count'];
$on_trip = $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE status = 'on_trip'")->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM ride_requests WHERE status = 'pending'")->fetch_assoc()['count'];

// Fetch vehicles
$vehicles = $conn->query("SELECT * FROM vehicles LIMIT 10");
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<main class="container">
    <div class="header-flex">
        <h1 class="dashboard-title">Fleet Dashboard</h1>
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
                            <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this vehicle?');">
                                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                <button type="submit" name="delete_vehicle" class="btn-remove">Remove</button>
                            </form>
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
        </div>
    </div>
</main>

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
</script>

<?php $conn->close(); ?>
