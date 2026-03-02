<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create cost tracking table if it doesn't exist
$create_sql = "CREATE TABLE IF NOT EXISTS vehicle_costs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT NOT NULL,
    cost_type VARCHAR(50),
    amount DECIMAL(10,2),
    description VARCHAR(255),
    cost_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_date (cost_date),
    INDEX idx_type (cost_type)
)";

$conn->query($create_sql);

// Get filter parameters
$time_filter = isset($_GET['filter']) ? $_GET['filter'] : 'monthly';
$selected_vehicle = isset($_GET['vehicle']) ? intval($_GET['vehicle']) : 0;

// Fetch vehicles
$vehicles = $conn->query("SELECT id, model, plate_number FROM vehicles ORDER BY model");

// Get current vehicle if selected
$current_vehicle = null;
if ($selected_vehicle > 0) {
    $current_vehicle = $conn->query("SELECT * FROM vehicles WHERE id = $selected_vehicle")->fetch_assoc();
}

// Initialize cost totals
$total_costs = array(
    'fuel_total' => 0,
    'maintenance_total' => 0,
    'other_total' => 0,
    'grand_total' => 0
);
$all_costs = null;

// Check if table has data before querying
$table_check = $conn->query("SELECT COUNT(*) as cnt FROM vehicle_costs LIMIT 1");

if ($table_check) {
    // Build date filter
    if ($time_filter === 'daily') {
        $date_condition = "DATE(cost_date) = CURDATE()";
    } else {
        $date_condition = "cost_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    }

    // Build vehicle filter
    $vehicle_condition = "";
    if ($selected_vehicle > 0) {
        $vehicle_condition = "AND vehicle_id = $selected_vehicle";
    }

    // Calculate total costs
    $total_costs_result = $conn->query("
        SELECT 
            COALESCE(SUM(CASE WHEN cost_type = 'fuel' THEN amount ELSE 0 END), 0) as fuel_total,
            COALESCE(SUM(CASE WHEN cost_type = 'maintenance' THEN amount ELSE 0 END), 0) as maintenance_total,
            COALESCE(SUM(CASE WHEN cost_type = 'other' THEN amount ELSE 0 END), 0) as other_total,
            COALESCE(SUM(amount), 0) as grand_total
        FROM vehicle_costs
        WHERE $date_condition $vehicle_condition
    ");
    
    if ($total_costs_result) {
        $total_costs = $total_costs_result->fetch_assoc();
    }

    // Fetch all costs for the selected period
    $all_costs = $conn->query("
        SELECT vc.*, v.model, v.plate_number
        FROM vehicle_costs vc
        LEFT JOIN vehicles v ON vc.vehicle_id = v.id
        WHERE $date_condition $vehicle_condition
        ORDER BY vc.cost_date DESC
    ");
}

// Handle add cost
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cost'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $cost_type = $_POST['cost_type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $cost_date = $_POST['cost_date'];
    
    $stmt = $conn->prepare("INSERT INTO vehicle_costs (vehicle_id, cost_type, amount, description, cost_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issds", $vehicle_id, $cost_type, $amount, $description, $cost_date);
    $stmt->execute();
    $stmt->close();
    header("Location: analytics.php?vehicle=$vehicle_id&filter=$time_filter");
    exit();
}
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .analytics-container {
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
        background: #00b14f;
        color: white;
    }

    .section {
        background: white;
        border: 1px solid #f0f0f0;
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

    .filter-controls {
        display: flex;
        gap: 15px;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8fafb;
        border-radius: 8px;
    }

    .filter-controls select {
        padding: 10px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
        font-size: 0.9rem;
    }

    .form-group input,
    .form-group select {
        padding: 10px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        font-size: 0.9rem;
        font-family: inherit;
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
        margin-top: 10px;
    }

    .btn-submit {
        background: #00b14f;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: #009440;
        box-shadow: 0 4px 12px rgba(0, 177, 79, 0.3);
    }

    .cost-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .cost-card {
        background: linear-gradient(135deg, #f8fafb, #ffffff);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #00b14f;
    }

    .cost-card.fuel {
        border-left-color: #f97316;
    }

    .cost-card.maintenance {
        border-left-color: #3b5998;
    }

    .cost-card.other {
        border-left-color: #999;
    }

    .cost-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .cost-amount {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .grand-total {
        grid-column: 1 / -1;
        background: linear-gradient(135deg, #00b14f, #009440);
        color: white !important;
        border: none;
    }

    .grand-total .cost-label {
        color: rgba(255,255,255,0.9);
    }

    .grand-total .cost-amount {
        color: white;
    }

    .costs-table {
        width: 100%;
        border-collapse: collapse;
    }

    .costs-table thead {
        background: #f8fafb;
        border-bottom: 2px solid #f0f0f0;
    }

    .costs-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .costs-table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        color: #666;
    }

    .costs-table tbody tr:hover {
        background: #f8fafb;
    }

    .cost-type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .cost-type-badge.fuel {
        background: #fef3c7;
        color: #92400e;
    }

    .cost-type-badge.maintenance {
        background: #dbeafe;
        color: #1e40af;
    }

    .cost-type-badge.other {
        background: #e5e7eb;
        color: #374151;
    }

    .vehicle-info {
        font-weight: 600;
        color: #1a1a1a;
    }

    .vehicle-plate {
        font-size: 0.85rem;
        color: #999;
    }

    .no-data {
        padding: 40px;
        text-align: center;
        color: #999;
    }
</style>

<main class="analytics-container">
    <div class="page-header">
        <h1>📈 Cost Analytics</h1>
        <a href="admin.php" class="back-button">← Back to Dashboard</a>
    </div>

    <!-- Vehicle Selection & Time Filter -->
    <div class="section">
        <div class="section-header">1️⃣ Vehicle Info</div>
        <div class="filter-controls">
            <form method="get" style="display: flex; gap: 15px; align-items: center;">
                <div class="form-group" style="margin: 0;">
                    <label for="vehicle">Select Vehicle</label>
                    <select id="vehicle" name="vehicle" onchange="this.form.submit()" style="width: 200px;">
                        <option value="">-- All Vehicles --</option>
                        <?php 
                        $vehicles2 = $conn->query("SELECT id, model, plate_number FROM vehicles ORDER BY model");
                        while ($v = $vehicles2->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $v['id']; ?>" <?php echo $selected_vehicle == $v['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($v['model']) . " (" . htmlspecialchars($v['plate_number']) . ")"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label for="time_filter">Time Period</label>
                    <select id="time_filter" name="filter" onchange="this.form.submit()" style="width: 150px;">
                        <option value="daily" <?php echo $time_filter === 'daily' ? 'selected' : ''; ?>>Daily</option>
                        <option value="monthly" <?php echo $time_filter === 'monthly' ? 'selected' : ''; ?>>Monthly (30 Days)</option>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($current_vehicle): ?>
        <div style="background: #f8fafb; padding: 15px; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <div style="font-size: 0.85rem; color: #999; text-transform: uppercase; margin-bottom: 5px;">Vehicle Model</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #1a1a1a;"><?php echo htmlspecialchars($current_vehicle['model']); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #999; text-transform: uppercase; margin-bottom: 5px;">Plate Number / ID</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #1a1a1a;"><?php echo htmlspecialchars($current_vehicle['plate_number']); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Add Cost Form -->
    <div class="section">
        <div class="section-header">➕ Add Cost Entry</div>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="vehicle_id">Vehicle *</label>
                    <select id="vehicle_id" name="vehicle_id" required>
                        <option value="">-- Select Vehicle --</option>
                        <?php 
                        $vehicles3 = $conn->query("SELECT id, model, plate_number FROM vehicles ORDER BY model");
                        while ($v = $vehicles3->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $v['id']; ?>" <?php echo $selected_vehicle == $v['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($v['model']) . " (" . htmlspecialchars($v['plate_number']) . ")"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cost_type">Cost Type *</label>
                    <select id="cost_type" name="cost_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="fuel">⛽ Fuel Cost</option>
                        <option value="maintenance">🔧 Maintenance Cost</option>
                        <option value="other">📦 Other Expenses</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Amount (₱) *</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0" placeholder="0.00" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" placeholder="e.g., Regular fueling, Oil change, etc.">
                </div>
                <div class="form-group">
                    <label for="cost_date">Cost Date *</label>
                    <input type="date" id="cost_date" name="cost_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="add_cost" class="btn-submit">✓ Add Cost Entry</button>
            </div>
        </form>
    </div>

    <!-- Cost Breakdown -->
    <div class="section">
        <div class="section-header">2️⃣ Cost Breakdown (<?php echo $time_filter === 'daily' ? 'Today' : 'Last 30 Days'; ?>)</div>
        
        <div class="cost-grid">
            <div class="cost-card fuel">
                <div class="cost-label">⛽ Fuel Cost</div>
                <div class="cost-amount">₱<?php echo number_format($total_costs['fuel_total'] ?? 0, 2); ?></div>
            </div>
            <div class="cost-card maintenance">
                <div class="cost-label">🔧 Maintenance Cost</div>
                <div class="cost-amount">₱<?php echo number_format($total_costs['maintenance_total'] ?? 0, 2); ?></div>
            </div>
            <div class="cost-card other">
                <div class="cost-label">📦 Other Expenses</div>
                <div class="cost-amount">₱<?php echo number_format($total_costs['other_total'] ?? 0, 2); ?></div>
            </div>
            <div class="cost-card grand-total">
                <div class="cost-label">💰 TOTAL COST (AUTO-CALCULATED)</div>
                <div class="cost-amount">₱<?php echo number_format($total_costs['grand_total'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>

    <!-- Cost Details Table -->
    <div class="section">
        <div class="section-header">📋 Cost Details</div>
        
        <?php if ($all_costs && $all_costs->num_rows > 0): ?>
        <table class="costs-table">
            <thead>
                <tr>
                    <th>DATE</th>
                    <th>VEHICLE</th>
                    <th>TYPE</th>
                    <th>DESCRIPTION</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cost = $all_costs->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($cost['cost_date'])); ?></td>
                    <td>
                        <div class="vehicle-info"><?php echo htmlspecialchars($cost['model'] ?? 'Unknown'); ?></div>
                        <div class="vehicle-plate"><?php echo htmlspecialchars($cost['plate_number'] ?? 'N/A'); ?></div>
                    </td>
                    <td>
                        <span class="cost-type-badge <?php echo $cost['cost_type']; ?>">
                            <?php 
                            if ($cost['cost_type'] === 'fuel') echo '⛽ Fuel';
                            elseif ($cost['cost_type'] === 'maintenance') echo '🔧 Maintenance';
                            else echo '📦 Other';
                            ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($cost['description'] ?? '-'); ?></td>
                    <td style="font-weight: 600; color: #00b14f;">₱<?php echo number_format($cost['amount'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">
            <p>No cost entries for the selected period.</p>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php $conn->close(); ?>
