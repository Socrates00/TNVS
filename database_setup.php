<?php
/**
 * Database Setup Script for Enhanced TNVS Features
 * Run this file once in your browser to create all necessary tables
 */

$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = array(

    // ============ DRIVERS TABLE ============
    "CREATE TABLE IF NOT EXISTS drivers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        license_number VARCHAR(50) UNIQUE NOT NULL,
        license_expiration DATE,
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        phone VARCHAR(20),
        email VARCHAR(100),
        performance_rating DECIMAL(3,2) DEFAULT 5.00,
        fuel_efficiency_score INT DEFAULT 0,
        safety_incidents INT DEFAULT 0,
        punctuality_rating DECIMAL(3,2) DEFAULT 5.00,
        total_miles DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );",

    // ============ DRIVER ATTENDANCE/SHIFTS ============
    "CREATE TABLE IF NOT EXISTS driver_shifts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        driver_id INT NOT NULL,
        vehicle_id INT,
        shift_start DATETIME,
        shift_end DATETIME,
        status ENUM('clocked_in', 'clocked_out', 'on_break') DEFAULT 'clocked_out',
        miles_driven DECIMAL(10,2) DEFAULT 0,
        fuel_consumed DECIMAL(8,2) DEFAULT 0,
        notes VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
    );",

    // ============ FUEL MANAGEMENT ============
    "CREATE TABLE IF NOT EXISTS fuel_management (
        id INT PRIMARY KEY AUTO_INCREMENT,
        vehicle_id INT NOT NULL,
        driver_id INT,
        fuel_date DATE NOT NULL,
        liters_added DECIMAL(8,2) NOT NULL,
        cost DECIMAL(10,2),
        fuel_type VARCHAR(50),
        odometer_reading INT,
        notes VARCHAR(300),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
        FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL
    );",

    // ============ DOCUMENT VAULT ============
    "CREATE TABLE IF NOT EXISTS vehicle_documents (
        id INT PRIMARY KEY AUTO_INCREMENT,
        vehicle_id INT NOT NULL,
        document_type ENUM('OR', 'CR', 'Insurance', 'LTFRB_Permit', 'Other') NOT NULL,
        document_name VARCHAR(255),
        file_path VARCHAR(255),
        expiration_date DATE,
        issue_date DATE,
        alert_enabled BOOLEAN DEFAULT 1,
        alert_days INT DEFAULT 30,
        uploaded_by VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
        INDEX idx_expiration (expiration_date),
        INDEX idx_alert (alert_enabled)
    );",

    // ============ ODOMETER TRACKING ============
    "CREATE TABLE IF NOT EXISTS odometer_readings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        vehicle_id INT NOT NULL,
        driver_id INT,
        odometer_value INT NOT NULL,
        recorded_date DATETIME,
        trip_id INT,
        notes VARCHAR(300),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
        FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
        INDEX idx_vehicle (vehicle_id),
        INDEX idx_date (recorded_date)
    );",

    // ============ MAINTENANCE RECORDS ============
    "CREATE TABLE IF NOT EXISTS maintenance_records (
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
    );",

    // ============ MAINTENANCE ALERTS (PREDICTIVE) ============
    "CREATE TABLE IF NOT EXISTS maintenance_alerts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        vehicle_id INT NOT NULL,
        alert_type VARCHAR(100),
        alert_reason VARCHAR(500),
        current_mileage INT,
        due_at_mileage INT,
        due_date DATE,
        alert_status ENUM('active', 'dismissed', 'resolved') DEFAULT 'active',
        severity ENUM('info', 'warning', 'critical') DEFAULT 'warning',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        resolved_at TIMESTAMP NULL,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
        INDEX idx_vehicle (vehicle_id),
        INDEX idx_status (alert_status),
        INDEX idx_severity (severity)
    );",

    // ============ PARTS INVENTORY ============
    "CREATE TABLE IF NOT EXISTS parts_inventory (
        id INT PRIMARY KEY AUTO_INCREMENT,
        part_name VARCHAR(100) NOT NULL,
        part_code VARCHAR(50),
        category VARCHAR(100),
        quantity_in_stock INT DEFAULT 0,
        reorder_level INT DEFAULT 5,
        unit_cost DECIMAL(10,2),
        supplier VARCHAR(100),
        last_restocked DATE,
        compatible_vehicles VARCHAR(500),
        notes VARCHAR(300),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (category),
        INDEX idx_stock_level (quantity_in_stock)
    );",

    // ============ PARTS USAGE LOG ============
    "CREATE TABLE IF NOT EXISTS parts_usage_log (
        id INT PRIMARY KEY AUTO_INCREMENT,
        part_id INT NOT NULL,
        maintenance_record_id INT,
        quantity_used INT,
        used_date DATE,
        used_by VARCHAR(100),
        notes VARCHAR(300),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (part_id) REFERENCES parts_inventory(id) ON DELETE RESTRICT,
        FOREIGN KEY (maintenance_record_id) REFERENCES maintenance_records(id) ON DELETE SET NULL
    );",

    // ============ CUSTOMER PAYMENT RECORDS ============
    "CREATE TABLE IF NOT EXISTS customer_payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        booking_id INT NOT NULL,
        user_id INT NOT NULL,
        ride_type VARCHAR(100),
        pickup_location VARCHAR(300),
        destination_location VARCHAR(300),
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50),
        transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('completed', 'refunded', 'pending') DEFAULT 'completed',
        refund_amount DECIMAL(10,2) DEFAULT 0,
        refund_date TIMESTAMP NULL,
        notes VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user (user_id),
        INDEX idx_date (transaction_date),
        INDEX idx_status (status)
    );"

);

// Execute all queries
$errors = array();
foreach ($sql as $query) {
    if (!$conn->query($query)) {
        $errors[] = $conn->error;
    }
}

if (empty($errors)) {
    echo "<h2>✓ All tables created successfully!</h2>";
    echo "<p><strong>Tables created:</strong></p>";
    echo "<ul>";
    echo "<li>drivers</li>";
    echo "<li>driver_shifts</li>";
    echo "<li>fuel_management</li>";
    echo "<li>vehicle_documents</li>";
    echo "<li>odometer_readings</li>";
    echo "<li>maintenance_records</li>";
    echo "<li>maintenance_alerts</li>";
    echo "<li>parts_inventory</li>";
    echo "<li>parts_usage_log</li>";
    echo "<li>customer_payments</li>";
    echo "</ul>";
    echo "<p><a href='admin.php'>Go to Admin Dashboard</a></p>";
} else {
    echo "<h2>Errors occurred:</h2>";
    foreach ($errors as $error) {
        echo "<p style='color:red;'>$error</p>";
    }
}

$conn->close();
?>
