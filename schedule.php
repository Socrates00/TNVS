<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle add schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $service_type = $_POST['service_type'];
    $scheduled_date = $_POST['scheduled_date'];
    $priority = $_POST['priority'];
    $estimated_cost = $_POST['estimated_cost'];
    $notes = $_POST['notes'];
    
    $stmt = $conn->prepare("INSERT INTO maintenance_schedule (vehicle_id, service_type, scheduled_date, priority, estimated_cost, status, notes) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("issdsss", $vehicle_id, $service_type, $scheduled_date, $priority, $estimated_cost, $notes);
    $stmt->execute();
    $stmt->close();
    header("Location: schedule.php");
    exit();
}

// Fetch vehicles
$vehicles = $conn->query("SELECT id, model, plate_number FROM vehicles ORDER BY model");

// Fetch schedule records
$schedule_records = $conn->query("SELECT ms.*, v.model, v.plate_number FROM maintenance_schedule ms 
                                 LEFT JOIN vehicles v ON ms.vehicle_id = v.id 
                                 ORDER BY ms.scheduled_date ASC LIMIT 50");
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .schedule-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .schedule-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .schedule-header h1 {
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

    .schedule-table {
        width: 100%;
        border-collapse: collapse;
    }

    .schedule-table thead {
        background: #f8fafb;
        border-bottom: 2px solid #f0f0f0;
    }

    .schedule-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .schedule-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        color: #666;
    }

    .schedule-table tbody tr:hover {
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

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-badge.pending {
        background: #fff3e0;
        color: #e65100;
    }

    .status-badge.completed {
        background: #e6f7ed;
        color: #009440;
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

<main class="schedule-container">
    <div class="schedule-header">
        <h1>📅 Maintenance Schedule</h1>
        <a href="admin.php" class="back-button">← Back to Dashboard</a>
    </div>

    <div class="add-form-section">
        <div class="form-title">➕ Schedule Maintenance</div>
        <form method="post" class="schedule-form">
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
                    <label for="service_type">Service Type *</label>
                    <input type="text" id="service_type" name="service_type" placeholder="e.g., Oil Change, Inspections" required>
                </div>
                <div class="form-group">
                    <label for="scheduled_date">Scheduled Date *</label>
                    <input type="date" id="scheduled_date" name="scheduled_date" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="priority">Priority Level *</label>
                    <select id="priority" name="priority" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estimated_cost">Estimated Cost (₱) *</label>
                    <input type="number" id="estimated_cost" name="estimated_cost" step="0.01" min="0" placeholder="0.00" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" placeholder="Add any additional notes..."></textarea>
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
            <h2>Upcoming Services</h2>
        </div>
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>VEHICLE</th>
                    <th>SERVICE TYPE</th>
                    <th>SCHEDULED DATE</th>
                    <th>PRIORITY</th>
                    <th>ESTIMATED COST</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($schedule_records && $schedule_records->num_rows > 0):
                    while ($record = $schedule_records->fetch_assoc()): 
                ?>
                <tr>
                    <td>
                        <div class="vehicle-info"><?php echo htmlspecialchars($record['model'] ?? 'Unknown'); ?></div>
                        <div class="vehicle-plate"><?php echo htmlspecialchars($record['plate_number'] ?? 'N/A'); ?></div>
                    </td>
                    <td><span class="service-badge"><?php echo htmlspecialchars($record['service_type']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($record['scheduled_date'])); ?></td>
                    <td><span class="priority-badge priority-<?php echo $record['priority']; ?>"><?php echo ucfirst($record['priority']); ?></span></td>
                    <td>₱<?php echo number_format($record['estimated_cost'], 2); ?></td>
                    <td><span class="status-badge <?php echo $record['status']; ?>"><?php echo ucfirst($record['status']); ?></span></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="no-records">
                        <p>No scheduled services. Add one to get started!</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php $conn->close(); ?>
