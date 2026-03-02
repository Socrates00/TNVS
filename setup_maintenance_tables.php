<?php
/**
 * Database Setup for Maintenance Module
 * Creates all required tables for the comprehensive maintenance system
 */

$conn = new mysqli("localhost", "root", "", "byahero_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$queries = [
    // Vehicle Warranty Table
    "CREATE TABLE IF NOT EXISTS vehicle_warranty (
        id INT PRIMARY KEY AUTO_INCREMENT,
        vehicle_id INT NOT NULL,
        warranty_type VARCHAR(100),
        provider VARCHAR(100),
        start_date DATE,
        expiration_date DATE,
        coverage_details TEXT,
        claim_limit DECIMAL(10, 2),
        policy_number VARCHAR(50),
        contact_number VARCHAR(20),
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
    )",
    
    // Maintenance Notifications Table
    "CREATE TABLE IF NOT EXISTS maintenance_notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        vehicle_id INT,
        maintenance_type VARCHAR(100),
        scheduled_date DATE,
        notification_method VARCHAR(50),
        recipient_email VARCHAR(100),
        recipient_phone VARCHAR(20),
        message TEXT,
        status VARCHAR(20) DEFAULT 'scheduled',
        sent_date DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
    )",
    
    // Seasonal Checklist Table
    "CREATE TABLE IF NOT EXISTS seasonal_checklist (
        id INT PRIMARY KEY AUTO_INCREMENT,
        task_name VARCHAR(200) NOT NULL,
        season VARCHAR(50),
        priority VARCHAR(20),
        estimated_cost DECIMAL(10, 2),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Vehicle Checklist Tracking
    "CREATE TABLE IF NOT EXISTS vehicle_checklist_tracking (
        id INT PRIMARY KEY AUTO_INCREMENT,
        vehicle_id INT NOT NULL,
        seasonal_checklist_id INT NOT NULL,
        is_completed BOOLEAN DEFAULT FALSE,
        completed_date DATETIME,
        notes TEXT,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
        FOREIGN KEY (seasonal_checklist_id) REFERENCES seasonal_checklist(id) ON DELETE CASCADE
    )",
    
    // Service Quality Ratings Table
    "CREATE TABLE IF NOT EXISTS service_quality_ratings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        maintenance_id INT,
        provider_id INT,
        rating INT,
        quality_score INT,
        timeliness_score INT,
        comments TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (maintenance_id) REFERENCES maintenance_records(id) ON DELETE SET NULL
    )",
    
    // Before/After Photos Table
    "CREATE TABLE IF NOT EXISTS before_after_photos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        maintenance_id INT,
        photo_type VARCHAR(20),
        photo_path VARCHAR(255),
        description TEXT,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (maintenance_id) REFERENCES maintenance_records(id) ON DELETE SET NULL
    )",
    
    // Service Providers Table
    "CREATE TABLE IF NOT EXISTS service_providers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        provider_name VARCHAR(150) NOT NULL,
        contact_person VARCHAR(100),
        phone VARCHAR(20),
        email VARCHAR(100),
        address TEXT,
        specialization VARCHAR(200),
        rating DECIMAL(3, 2),
        total_services INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

$success = 0;
$errors = [];

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        $success++;
    } else {
        $errors[] = $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Database Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 600px; width: 100%; }
        h1 { color: #333; margin-bottom: 30px; text-align: center; }
        .status { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .error { background: #f8d7da; color: #721c24; border-left: 4px solid #f5c6cb; }
        .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 25px 0; }
        .stat-box { background: #f8f9fa; padding: 20px; border-radius: 5px; text-align: center; }
        .stat-number { font-size: 32px; font-weight: bold; color: #00b14f; }
        .stat-label { color: #666; font-size: 12px; margin-top: 5px; }
        .back-btn { display: inline-block; background: #00b14f; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; margin-top: 20px; }
        .back-btn:hover { background: #008f40; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Maintenance Database Setup</h1>
        
        <div class="status success">
            ✓ <strong>Database Connection Successful</strong><br>
            Connected to: <code>byahero_db</code>
        </div>

        <div class="stats">
            <div class="stat-box">
                <div class="stat-number"><?php echo $success; ?></div>
                <div class="stat-label">Tables Created/Verified</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo count($errors); ?></div>
                <div class="stat-label">Errors</div>
            </div>
        </div>

        <?php if (count($errors) === 0): ?>
        <div class="status success">
            ✓ <strong>All Tables Ready!</strong><br>
            All database tables for the maintenance module have been successfully created or already exist.
        </div>
        
        <h3 style="margin-top: 30px; margin-bottom: 15px;">Tables Created:</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="padding: 8px 0; border-bottom: 1px solid #eee;">📋 vehicle_warranty</li>
            <li style="padding: 8px 0; border-bottom: 1px solid #eee;">🔔 maintenance_notifications</li>
            <li style="padding: 8px 0; border-bottom: 1px solid #eee;">✓ seasonal_checklist</li>
            <li style="padding: 8px 0; border-bottom: 1px solid #eee;">📊 vehicle_checklist_tracking</li>
            <li style="padding: 8px 0; border-bottom: 1px solid #eee;">⭐ service_quality_ratings</li>
            <li style="padding: 8px 0; border-bottom: 1px solid #eee;">📷 before_after_photos</li>
            <li style="padding: 8px 0;">🏢 service_providers</li>
        </ul>

        <?php else: ?>
        <div class="status warning">
            ⚠️ <strong><?php echo count($errors); ?> Error(s) Occurred</strong><br>
            <?php foreach ($errors as $error): ?>
                <div style="margin-top: 10px; font-size: 12px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <a href="admin.php" class="back-btn">← Back to Dashboard</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
