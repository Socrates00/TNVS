<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle add warranty record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_warranty'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $warranty_type = $_POST['warranty_type'];
    $expiration_date = $_POST['expiration_date'];
    $provider = $_POST['provider'];
    $coverage = $_POST['coverage'];
    $notes = $_POST['notes'];
    
    $stmt = $conn->prepare("INSERT INTO vehicle_warranty (vehicle_id, warranty_type, expiration_date, provider, coverage, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $vehicle_id, $warranty_type, $expiration_date, $provider, $coverage, $notes);
    $stmt->execute();
    $stmt->close();
    header("Location: warranty.php");
    exit();
}

// Fetch vehicles for dropdown
$vehicles = $conn->query("SELECT id, model, plate_number FROM vehicles ORDER BY model");

// Fetch warranty records
$warranty_records = $conn->query("SELECT vw.*, v.model, v.plate_number FROM vehicle_warranty vw 
                                 LEFT JOIN vehicles v ON vw.vehicle_id = v.id 
                                 ORDER BY vw.expiration_date ASC LIMIT 50");
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .warranty-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .warranty-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .warranty-header h1 {
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

    .warranty-table {
        width: 100%;
        border-collapse: collapse;
    }

    .warranty-table thead {
        background: #f8fafb;
        border-bottom: 2px solid #f0f0f0;
    }

    .warranty-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .warranty-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        color: #666;
    }

    .warranty-table tbody tr:hover {
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

    .warranty-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        background: #e6f7ed;
        color: #009440;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-badge.active {
        background: #e6f7ed;
        color: #009440;
    }

    .status-badge.expiring {
        background: #fff3e0;
        color: #e65100;
    }

    .status-badge.expired {
        background: #ffebee;
        color: #c62828;
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

<main class="warranty-container">
    <div class="warranty-header">
        <h1>🛡️ Vehicle Warranty</h1>
        <a href="admin.php" class="back-button">← Back to Dashboard</a>
    </div>

    <div class="add-form-section">
        <div class="form-title">➕ Add New Warranty</div>
        <form method="post" class="warranty-form">
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
                    <label for="warranty_type">Warranty Type *</label>
                    <select id="warranty_type" name="warranty_type" required>
                        <option value="">-- Select type --</option>
                        <option value="Manufacturer">Manufacturer</option>
                        <option value="Extended">Extended</option>
                        <option value="Parts">Parts</option>
                        <option value="Labor">Labor</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="expiration_date">Expiration Date *</label>
                    <input type="date" id="expiration_date" name="expiration_date" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="provider">Warranty Provider *</label>
                    <input type="text" id="provider" name="provider" placeholder="e.g., Toyota Warranty" required>
                </div>
                <div class="form-group">
                    <label for="coverage">Coverage Details *</label>
                    <input type="text" id="coverage" name="coverage" placeholder="e.g., Full Coverage, Parts Only" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" placeholder="Add warranty details, terms, or special notes..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="add_warranty" class="btn-submit">✓ Add Warranty</button>
                <button type="reset" class="btn-reset">Clear Form</button>
            </div>
        </form>
    </div>

    <div class="records-section">
        <div class="records-header">
            <h2>Active Warranties</h2>
        </div>
        <table class="warranty-table">
            <thead>
                <tr>
                    <th>VEHICLE</th>
                    <th>WARRANTY TYPE</th>
                    <th>EXPIRATION DATE</th>
                    <th>PROVIDER</th>
                    <th>COVERAGE</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($warranty_records && $warranty_records->num_rows > 0):
                    while ($record = $warranty_records->fetch_assoc()): 
                        $expiration = new DateTime($record['expiration_date']);
                        $today = new DateTime();
                        $diff = $today->diff($expiration)->days;
                        
                        if ($expiration < $today) {
                            $status = 'expired';
                            $status_text = 'Expired';
                        } elseif ($diff <= 30) {
                            $status = 'expiring';
                            $status_text = 'Expiring Soon';
                        } else {
                            $status = 'active';
                            $status_text = 'Active';
                        }
                ?>
                <tr>
                    <td>
                        <div class="vehicle-info"><?php echo htmlspecialchars($record['model'] ?? 'Unknown'); ?></div>
                        <div class="vehicle-plate"><?php echo htmlspecialchars($record['plate_number'] ?? 'N/A'); ?></div>
                    </td>
                    <td><span class="warranty-badge"><?php echo htmlspecialchars($record['warranty_type']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($record['expiration_date'])); ?></td>
                    <td><?php echo htmlspecialchars($record['provider']); ?></td>
                    <td><?php echo htmlspecialchars($record['coverage']); ?></td>
                    <td><span class="status-badge <?php echo $status; ?>"><?php echo $status_text; ?></span></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="no-records">
                        <p>No warranty records found. Add one to get started!</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php $conn->close(); ?>
