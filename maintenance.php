<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create maintenance_records table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS maintenance_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT NOT NULL,
    maintenance_type VARCHAR(100),
    service_details VARCHAR(500),
    performed_by VARCHAR(100),
    service_date DATE NOT NULL,
    cost DECIMAL(10,2),
    odometer_at_service INT,
    next_due_mileage INT,
    next_due_date DATE,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    parts_used VARCHAR(300),
    status ENUM('completed', 'pending', 'in_progress') DEFAULT 'completed',
    notes VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_date (service_date),
    INDEX idx_status (status)
)");

// Create maintenance_schedule table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS maintenance_schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT NOT NULL,
    service_type VARCHAR(100),
    scheduled_date DATE,
    priority VARCHAR(20),
    estimated_cost DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'pending',
    notes VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_date (scheduled_date)
)");

// Handle add maintenance record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_maintenance'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $maintenance_type = $_POST['maintenance_type'];
    $service_details = $_POST['service_details'];
    $service_date = $_POST['service_date'];
    $cost = $_POST['cost'];
    $performed_by = $_POST['performed_by'];
    $priority = $_POST['priority'];
    $notes = $_POST['notes'];
    
    $stmt = $conn->prepare("INSERT INTO maintenance_records (vehicle_id, maintenance_type, service_details, service_date, cost, performed_by, priority, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdsss", $vehicle_id, $maintenance_type, $service_details, $service_date, $cost, $performed_by, $priority, $notes);
    $stmt->execute();
    $stmt->close();
    header("Location: maintenance.php");
    exit();
}

// Handle add maintenance schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $vehicle_id = $_POST['vehicle_id_schedule'];
    $service_type = $_POST['service_type'];
    $scheduled_date = $_POST['scheduled_date'];
    $priority = $_POST['priority_schedule'];
    $estimated_cost = $_POST['estimated_cost'];
    $notes = $_POST['schedule_notes'];
    
    $stmt = $conn->prepare("INSERT INTO maintenance_schedule (vehicle_id, service_type, scheduled_date, priority, estimated_cost, status, notes) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("isssds", $vehicle_id, $service_type, $scheduled_date, $priority, $estimated_cost, $notes);
    $stmt->execute();
    $stmt->close();
    header("Location: maintenance.php");
    exit();
}

// Fetch vehicles for dropdown
$vehicles = $conn->query("SELECT id, model, plate_number FROM vehicles ORDER BY model");

// Fetch maintenance records
$query = "SELECT mr.*, v.model, v.plate_number FROM maintenance_records mr 
          LEFT JOIN vehicles v ON mr.vehicle_id = v.id 
          ORDER BY mr.service_date DESC LIMIT 50";
$maintenance_records = $conn->query($query);

// Fetch maintenance schedule
$schedule_query = "SELECT ms.*, v.model, v.plate_number FROM maintenance_schedule ms 
                   LEFT JOIN vehicles v ON ms.vehicle_id = v.id 
                   ORDER BY ms.scheduled_date ASC LIMIT 50";
$schedule_records = $conn->query($schedule_query);
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .maintenance-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .maintenance-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .maintenance-header h1 {
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
        font-weight: 600;
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
    .form-group select,
    .form-group textarea {
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: border 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #00b14f;
        box-shadow: 0 0 0 3px rgba(0, 177, 79, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
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

    .btn-reset {
        background: #f0f0f0;
        color: #333;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-reset:hover {
        background: #e0e0e0;
    }

    .records-section {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .records-header {
        padding: 20px 25px;
        border-bottom: 2px solid #f0f0f0;
        background: #f8fafb;
    }

    .records-header h2 {
        margin: 0;
        font-size: 1.2rem;
        color: #1a1a1a;
    }

    .maintenance-table {
        width: 100%;
        border-collapse: collapse;
    }

    .maintenance-table thead {
        background: #f8fafb;
        border-bottom: 2px solid #f0f0f0;
    }

    .maintenance-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .maintenance-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        color: #666;
    }

    .maintenance-table tbody tr:hover {
        background: #f8fafb;
    }

    .vehicle-info {
        font-weight: 600;
        color: #1a1a1a;
    }

    .vehicle-plate {
        font-size: 0.85rem;
        color: #999;
        margin-top: 3px;
    }

    .service-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        background: #e6f7ed;
        color: #009440;
    }

    .priority-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .priority-badge.priority-low {
        background: #e0f2f1;
        color: #00695c;
    }

    .priority-badge.priority-medium {
        background: #fff3e0;
        color: #e65100;
    }

    .priority-badge.priority-high {
        background: #ffebee;
        color: #c62828;
    }

    .cost-amount {
        font-weight: 600;
        color: #00b14f;
        font-size: 1.05rem;
    }

    .no-records {
        padding: 40px;
        text-align: center;
        color: #999;
    }

    .no-records p {
        margin: 0;
        font-size: 1.1rem;
    }
</style>

<main class="maintenance-container">
    <div class="maintenance-header">
        <h1>📋 Maintenance Records</h1>
        <a href="admin.php" class="back-button">← Back to Dashboard</a>
    </div>

    <div class="add-form-section">
        <div class="form-title">➕ Add New Maintenance Record</div>
        <form method="post" class="maintenance-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="vehicle_id">Select Vehicle *</label>
                    <select id="vehicle_id" name="vehicle_id" required>
                        <option value="">-- Choose a vehicle --</option>
                        <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                            <option value="<?php echo $vehicle['id']; ?>">
                                <?php echo htmlspecialchars($vehicle['model']) . " (" . htmlspecialchars($vehicle['plate_number']) . ")"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="maintenance_type">Maintenance Type *</label>
                    <input type="text" id="maintenance_type" name="maintenance_type" placeholder="e.g., Oil Change, Tire Replacement" required>
                </div>
                <div class="form-group">
                    <label for="service_date">Service Date *</label>
                    <input type="date" id="service_date" name="service_date" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cost">Cost (₱) *</label>
                    <input type="number" id="cost" name="cost" step="0.01" min="0" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label for="performed_by">Performed By *</label>
                    <input type="text" id="performed_by" name="performed_by" placeholder="e.g., ABC Auto Shop" required>
                </div>
                <div class="form-group">
                    <label for="priority">Priority Level *</label>
                    <select id="priority" name="priority" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="service_details">Service Details</label>
                    <textarea id="service_details" name="service_details" placeholder="Describe the work performed..."></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" placeholder="Add any additional notes about the service..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="add_maintenance" class="btn-submit">✓ Add Record</button>
                <button type="reset" class="btn-reset">Clear Form</button>
            </div>
        </form>
    </div>

    <div class="records-section">
        <div class="records-header">
            <h2>Service History</h2>
        </div>
        <table class="maintenance-table">
            <thead>
                <tr>
                    <th>VEHICLE</th>
                    <th>MAINTENANCE TYPE</th>
                    <th>DATE</th>
                    <th>COST</th>
                    <th>PERFORMED BY</th>
                    <th>PRIORITY</th>
                    <th>NOTES</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($maintenance_records && $maintenance_records->num_rows > 0):
                    while ($record = $maintenance_records->fetch_assoc()): 
                ?>
                <tr>
                    <td>
                        <div class="vehicle-info"><?php echo htmlspecialchars($record['model'] ?? 'Unknown'); ?></div>
                        <div class="vehicle-plate"><?php echo htmlspecialchars($record['plate_number'] ?? 'N/A'); ?></div>
                    </td>
                    <td><span class="service-badge"><?php echo htmlspecialchars($record['maintenance_type']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($record['service_date'])); ?></td>
                    <td><span class="cost-amount">₱<?php echo number_format($record['cost'], 2); ?></span></td>
                    <td><?php echo htmlspecialchars($record['performed_by']); ?></td>
                    <td>
                        <span class="priority-badge priority-<?php echo $record['priority']; ?>">
                            <?php echo ucfirst($record['priority']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars(substr($record['notes'] ?? '', 0, 50)); ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="7" class="no-records">
                        <p>No maintenance records found. Add one to get started!</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="add-form-section" style="margin-top: 40px;">
        <div class="form-title">📅 Schedule Upcoming Maintenance</div>
        <form method="post" class="maintenance-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="vehicle_id_schedule">Select Vehicle *</label>
                    <select id="vehicle_id_schedule" name="vehicle_id_schedule" required>
                        <option value="">-- Choose a vehicle --</option>
                        <?php 
                        // Refetch vehicles for schedule form
                        $vehicles_schedule = $conn->query("SELECT id, model, plate_number FROM vehicles ORDER BY model");
                        while ($vehicle = $vehicles_schedule->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $vehicle['id']; ?>">
                                <?php echo htmlspecialchars($vehicle['model']) . " (" . htmlspecialchars($vehicle['plate_number']) . ")"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="service_type">Service Type *</label>
                    <input type="text" id="service_type" name="service_type" placeholder="e.g., Tire Rotation, Oil Change" required>
                </div>
                <div class="form-group">
                    <label for="scheduled_date">Scheduled Date *</label>
                    <input type="date" id="scheduled_date" name="scheduled_date" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="estimated_cost">Estimated Cost (₱)</label>
                    <input type="number" id="estimated_cost" name="estimated_cost" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="priority_schedule">Priority Level *</label>
                    <select id="priority_schedule" name="priority_schedule" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="schedule_notes">Notes</label>
                    <textarea id="schedule_notes" name="schedule_notes" placeholder="Add any additional notes about the scheduled service..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="add_schedule" class="btn-submit">✓ Schedule Service</button>
                <button type="reset" class="btn-reset">Clear Form</button>
            </div>
        </form>
    </div>

    <div class="records-section">
        <div class="records-header">
            <h2>Upcoming Maintenance Schedule</h2>
        </div>
        <table class="maintenance-table">
            <thead>
                <tr>
                    <th>VEHICLE</th>
                    <th>SERVICE TYPE</th>
                    <th>SCHEDULED DATE</th>
                    <th>ESTIMATED COST</th>
                    <th>PRIORITY</th>
                    <th>STATUS</th>
                    <th>NOTES</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($schedule_records && $schedule_records->num_rows > 0):
                    while ($schedule = $schedule_records->fetch_assoc()): 
                        $scheduled = new DateTime($schedule['scheduled_date']);
                        $today = new DateTime();
                        $days_until = $today->diff($scheduled)->days;
                        $is_overdue = $scheduled < $today;
                ?>
                <tr>
                    <td>
                        <div class="vehicle-info"><?php echo htmlspecialchars($schedule['model'] ?? 'Unknown'); ?></div>
                        <div class="vehicle-plate"><?php echo htmlspecialchars($schedule['plate_number'] ?? 'N/A'); ?></div>
                    </td>
                    <td><span class="service-badge"><?php echo htmlspecialchars($schedule['service_type']); ?></span></td>
                    <td>
                        <?php echo date('M d, Y', strtotime($schedule['scheduled_date'])); ?>
                        <div class="vehicle-plate" style="color: #666;">
                            <?php 
                            if ($is_overdue) {
                                echo "<span style='color: #c62828; font-weight: 600;'>Overdue by " . abs($days_until) . " days</span>";
                            } else {
                                echo "In " . $days_until . " days";
                            }
                            ?>
                        </div>
                    </td>
                    <td>
                        <?php 
                        if (!empty($schedule['estimated_cost'])):
                            echo '<span class="cost-amount">₱' . number_format($schedule['estimated_cost'], 2) . '</span>';
                        else:
                            echo '<span style="color: #999;">N/A</span>';
                        endif;
                        ?>
                    </td>
                    <td>
                        <span class="priority-badge priority-<?php echo $schedule['priority']; ?>">
                            <?php echo ucfirst($schedule['priority']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="service-badge" style="background-color: #e3f2fd; color: #1976d2;">
                            <?php echo ucfirst($schedule['status'] ?? 'pending'); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars(substr($schedule['notes'] ?? '', 0, 50)); ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="7" class="no-records">
                        <p>No scheduled maintenance. Add a schedule to get started!</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php $conn->close(); ?>
