<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create fuel logs table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS fuel_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT,
    driver_id INT,
    fuel_amount DECIMAL(5,2),
    fuel_cost DECIMAL(10,2),
    fuel_price_per_liter DECIMAL(7,2),
    odometer_reading INT,
    fuel_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes VARCHAR(500),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
)");

// Fetch fuel statistics
$fuel_stats = $conn->query("SELECT 
    COUNT(*) as total_logs,
    SUM(fuel_amount) as total_liters,
    SUM(fuel_cost) as total_cost,
    AVG(fuel_price_per_liter) as avg_price_per_liter,
    MAX(fuel_date) as last_refuel
FROM fuel_logs")->fetch_assoc();

// Fetch fuel consumption by vehicle
$fuel_by_vehicle = $conn->query("SELECT 
    v.id, v.model, v.plate_number,
    COUNT(fl.id) as refuel_count,
    SUM(fl.fuel_amount) as total_liters,
    SUM(fl.fuel_cost) as total_cost
FROM vehicles v
LEFT JOIN fuel_logs fl ON v.id = fl.vehicle_id
GROUP BY v.id
ORDER BY total_cost DESC
LIMIT 10");

// Fetch fuel consumption by driver
$fuel_by_driver = $conn->query("SELECT 
    d.id, d.first_name, d.last_name, d.driver_id,
    COUNT(fl.id) as refuel_count,
    SUM(fl.fuel_amount) as total_liters,
    SUM(fl.fuel_cost) as total_cost
FROM drivers d
LEFT JOIN fuel_logs fl ON d.id = fl.driver_id
GROUP BY d.id
ORDER BY total_cost DESC
LIMIT 10");

// Fuel efficiency (km per liter) - requires odometer data
$fuel_efficiency = $conn->query("SELECT 
    v.model, v.plate_number,
    AVG((fl.odometer_reading / fl.fuel_amount)) as avg_kmpl
FROM vehicles v
LEFT JOIN fuel_logs fl ON v.id = fl.vehicle_id
WHERE fl.fuel_amount > 0
GROUP BY v.id
ORDER BY avg_kmpl DESC");

$total_liters = isset($fuel_stats['total_liters']) ? $fuel_stats['total_liters'] : 0;
$total_cost = isset($fuel_stats['total_cost']) ? $fuel_stats['total_cost'] : 0;
$avg_price = isset($fuel_stats['avg_price_per_liter']) ? $fuel_stats['avg_price_per_liter'] : 0;
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .fuel-container {
        max-width: 1400px;
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

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border-left: 5px solid #f97316;
    }

    .stat-card.liters {
        border-left-color: #0ea5e9;
    }

    .stat-card.price {
        border-left-color: #f59e0b;
    }

    .stat-card.efficiency {
        border-left-color: #10b981;
    }

    .stat-label {
        font-size: 12px;
        color: #999;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 5px;
    }

    .stat-unit {
        font-size: 13px;
        color: #999;
    }

    .section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
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

    .table-responsive {
        overflow-x: auto;
    }

    .fuel-table {
        width: 100%;
        border-collapse: collapse;
    }

    .fuel-table th {
        background: #f8fafb;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #f0f0f0;
    }

    .fuel-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        color: #666;
    }

    .fuel-table tbody tr:hover {
        background: #f8fafb;
    }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        background: #e3f2fd;
        color: #1976d2;
    }

    .badge.high {
        background: #ffebee;
        color: #c62828;
    }

    .badge.normal {
        background: #e8f5e9;
        color: #2e7d32;
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
    }

    .back-button:hover {
        background: #f97316;
        color: white;
    }

    .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 25px;
    }

    .empty-message {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .highlight {
        font-weight: 700;
        color: #f97316;
    }
</style>

<main class="fuel-container">
    <div class="page-header">
        <h1>⛽ Fuel Management</h1>
        <a href="admin.php" class="back-button">← Back to Dashboard</a>
    </div>

    <!-- Key Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Cost</div>
            <div class="stat-value">₱<?php echo number_format($total_cost, 2); ?></div>
            <div class="stat-unit"><?php echo intval($fuel_stats['total_logs']); ?> refuel logs</div>
        </div>
        
        <div class="stat-card liters">
            <div class="stat-label">Total Fuel Purchased</div>
            <div class="stat-value"><?php echo number_format($total_liters, 2); ?></div>
            <div class="stat-unit">Liters</div>
        </div>
        
        <div class="stat-card price">
            <div class="stat-label">Average Price Per Liter</div>
            <div class="stat-value">₱<?php echo number_format($avg_price, 2); ?></div>
            <div class="stat-unit">Current market</div>
        </div>
        
        <div class="stat-card efficiency">
            <div class="stat-label">Fuel Budget Status</div>
            <div class="stat-value">Ready</div>
            <div class="stat-unit">For integration</div>
        </div>
    </div>

    <div class="grid-2">
        <!-- Fuel Consumption by Vehicle -->
        <div class="section">
            <div class="section-header">🚗 Fuel By Vehicle</div>
            <div class="table-responsive">
                <?php if ($fuel_by_vehicle->num_rows == 0): ?>
                    <div class="empty-message">
                        <div class="empty-icon">📋</div>
                        <p>No fuel logs yet</p>
                    </div>
                <?php else: ?>
                    <table class="fuel-table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Refuels</th>
                                <th>Liters</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $fuel_by_vehicle->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($row['model']); ?></div>
                                    <div style="font-size: 0.85rem; color: #999;"><?php echo htmlspecialchars($row['plate_number']); ?></div>
                                </td>
                                <td><?php echo intval($row['refuel_count']); ?></td>
                                <td><span class="highlight"><?php echo number_format($row['total_liters'] ?? 0, 2); ?>L</span></td>
                                <td>₱<?php echo number_format($row['total_cost'] ?? 0, 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fuel Consumption by Driver -->
        <div class="section">
            <div class="section-header">👤 Fuel By Driver</div>
            <div class="table-responsive">
                <?php if ($fuel_by_driver->num_rows == 0): ?>
                    <div class="empty-message">
                        <div class="empty-icon">📋</div>
                        <p>No fuel logs yet</p>
                    </div>
                <?php else: ?>
                    <table class="fuel-table">
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Refuels</th>
                                <th>Liters</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $fuel_by_driver->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                                    <div style="font-size: 0.85rem; color: #999;">ID: <?php echo htmlspecialchars($row['driver_id']); ?></div>
                                </td>
                                <td><?php echo intval($row['refuel_count']); ?></td>
                                <td><span class="highlight"><?php echo number_format($row['total_liters'] ?? 0, 2); ?>L</span></td>
                                <td>₱<?php echo number_format($row['total_cost'] ?? 0, 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Fuel Efficiency -->
    <div class="section">
        <div class="section-header">⚡ Fuel Efficiency (KM/L)</div>
        <div class="table-responsive">
            <?php if (!$fuel_efficiency || $fuel_efficiency->num_rows == 0): ?>
                <div class="empty-message">
                    <div class="empty-icon">📊</div>
                    <p>Efficiency data coming from partner integration</p>
                </div>
            <?php else: ?>
                <table class="fuel-table">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Avg KM/L</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $fuel_efficiency->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($row['model']); ?></div>
                                <div style="font-size: 0.85rem; color: #999;"><?php echo htmlspecialchars($row['plate_number']); ?></div>
                            </td>
                            <td><span class="highlight"><?php echo number_format($row['avg_kmpl'] ?? 0, 2); ?></span></td>
                            <td>
                                <?php 
                                $kmpl = $row['avg_kmpl'] ?? 0;
                                if ($kmpl >= 8) {
                                    echo '<span class="badge normal">✓ Excellent</span>';
                                } elseif ($kmpl >= 6) {
                                    echo '<span class="badge">⚠ Good</span>';
                                } else {
                                    echo '<span class="badge high">⛔ Poor</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</main>

<?php $conn->close(); ?>
