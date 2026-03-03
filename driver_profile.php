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

// Fetch all drivers
$drivers = $conn->query("SELECT d.*, v.model, v.plate_number FROM drivers d LEFT JOIN vehicles v ON d.assigned_vehicle_id = v.id ORDER BY d.created_at DESC");

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

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .empty-state-text {
        font-size: 1.1rem;
        margin-bottom: 10px;
    }

    .empty-state-subtext {
        font-size: 0.9rem;
        color: #bbb;
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

    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <div class="section-header">👥 All Drivers</div>
        
        <?php if ($total_drivers == 0): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <div class="empty-state-text">No Drivers Available</div>
                <div class="empty-state-subtext">Driver information will be posted here when received from partner group</div>
            </div>
        <?php else: ?>
            <table class="drivers-table">
                <thead>
                    <tr>
                        <th>DRIVER INFO</th>
                        <th>LICENSE</th>
                        <th>STATUS</th>
                        <th>ASSIGNED VEHICLE</th>
                        <th>TOTAL TRIPS</th>
                        <th>RATING</th>
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

                        <!-- Total Trips -->
                        <td>
                            <div style="font-size: 0.9rem;">
                                <strong><?php echo $driver['total_trips']; ?></strong> trips
                            </div>
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
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<?php $conn->close(); ?>
