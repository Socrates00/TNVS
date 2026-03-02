<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch compliance records
$compliance = $conn->query("SELECT v.id, v.model, v.plate_number, v.last_maintenance,
                          (SELECT MAX(service_date) FROM maintenance_records WHERE vehicle_id = v.id) as latest_service,
                          (SELECT COUNT(*) FROM maintenance_records WHERE vehicle_id = v.id AND status = 'pending') as pending_count
                          FROM vehicles v
                          ORDER BY v.model");
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .compliance-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .compliance-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .compliance-header h1 {
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

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .summary-card {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        text-align: center;
    }

    .summary-card h3 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #999;
        text-transform: uppercase;
        margin: 0 0 15px 0;
        letter-spacing: 0.5px;
    }

    .summary-card .number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #00b14f;
        margin: 0;
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

    .compliance-table {
        width: 100%;
        border-collapse: collapse;
    }

    .compliance-table thead {
        background: #f8fafb;
        border-bottom: 2px solid #f0f0f0;
    }

    .compliance-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .compliance-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        color: #666;
    }

    .compliance-table tbody tr:hover {
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

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-align: center;
    }

    .status-badge.compliant {
        background: #e6f7ed;
        color: #009440;
    }

    .status-badge.warning {
        background: #fff3e0;
        color: #e65100;
    }

    .status-badge.overdue {
        background: #ffebee;
        color: #c62828;
    }

    .alert-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
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

<main class="compliance-container">
    <div class="compliance-header">
        <h1>✅ Compliance Status</h1>
        <a href="admin.php" class="back-button">← Back to Dashboard</a>
    </div>

    <?php
    // Count compliance statistics
    $compliant_count = 0;
    $warning_count = 0;
    $overdue_count = 0;
    
    $compliance_temp = $conn->query("SELECT v.id, v.last_maintenance,
                                    (SELECT MAX(service_date) FROM maintenance_records WHERE vehicle_id = v.id) as latest_service,
                                    (SELECT COUNT(*) FROM maintenance_records WHERE vehicle_id = v.id AND status = 'pending') as pending_count
                                    FROM vehicles v");
    
    while ($row = $compliance_temp->fetch_assoc()) {
        $last_service = $row['latest_service'] ?? $row['last_maintenance'];
        $last_date = new DateTime($last_service);
        $today = new DateTime();
        $days_since = $today->diff($last_date)->days;
        
        if ($row['pending_count'] > 0) {
            $overdue_count++;
        } elseif ($days_since > 180) {
            $overdue_count++;
        } elseif ($days_since > 90) {
            $warning_count++;
        } else {
            $compliant_count++;
        }
    }
    ?>

    <div class="summary-grid">
        <div class="summary-card">
            <h3>✅ Compliant</h3>
            <p class="number"><?php echo $compliant_count; ?></p>
        </div>
        <div class="summary-card">
            <h3>⚠️ Warning</h3>
            <p class="number"><?php echo $warning_count; ?></p>
        </div>
        <div class="summary-card">
            <h3>🔴 Overdue</h3>
            <p class="number"><?php echo $overdue_count; ?></p>
        </div>
    </div>

    <div class="records-section">
        <div class="records-header">
            <h2>Fleet Compliance Overview</h2>
        </div>
        <table class="compliance-table">
            <thead>
                <tr>
                    <th>VEHICLE</th>
                    <th>LAST SERVICE</th>
                    <th>DAYS SINCE SERVICE</th>
                    <th>PENDING SERVICES</th>
                    <th>STATUS</th>
                    <th>ALERTS</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $compliance = $conn->query("SELECT v.id, v.model, v.plate_number, v.last_maintenance,
                                          (SELECT MAX(service_date) FROM maintenance_records WHERE vehicle_id = v.id) as latest_service,
                                          (SELECT COUNT(*) FROM maintenance_records WHERE vehicle_id = v.id AND status = 'pending') as pending_count
                                          FROM vehicles v
                                          ORDER BY v.model");
                
                if ($compliance && $compliance->num_rows > 0):
                    while ($record = $compliance->fetch_assoc()): 
                        $last_service = $record['latest_service'] ?? $record['last_maintenance'];
                        $last_date = new DateTime($last_service);
                        $today = new DateTime();
                        $days_since = $today->diff($last_date)->days;
                        
                        $status = 'compliant';
                        $status_text = 'Compliant';
                        $alerts = '';
                        
                        if ($record['pending_count'] > 0) {
                            $status = 'overdue';
                            $status_text = 'Overdue';
                            $alerts .= '<span class="alert-badge">' . $record['pending_count'] . ' Pending</span> ';
                        }
                        
                        if ($days_since > 180) {
                            $status = 'overdue';
                            $status_text = 'Overdue';
                            if (empty($alerts)) {
                                $alerts .= '<span class="alert-badge">No Service for ' . $days_since . ' days</span>';
                            }
                        } elseif ($days_since > 90) {
                            $status = 'warning';
                            $status_text = 'Warning';
                            if (empty($alerts)) {
                                $alerts .= '<span class="alert-badge">Service Due Soon (' . $days_since . ' days)</span>';
                            }
                        }
                ?>
                <tr>
                    <td>
                        <div class="vehicle-info"><?php echo htmlspecialchars($record['model']); ?></div>
                        <div class="vehicle-plate"><?php echo htmlspecialchars($record['plate_number']); ?></div>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($last_service)); ?></td>
                    <td><strong><?php echo $days_since; ?></strong> days</td>
                    <td><?php echo $record['pending_count'] ?? 0; ?></td>
                    <td><span class="status-badge <?php echo $status; ?>"><?php echo $status_text; ?></span></td>
                    <td><?php echo $alerts; ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="no-records">
                        <p>No vehicles found.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php $conn->close(); ?>
