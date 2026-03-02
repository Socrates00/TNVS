<?php
$conn = new mysqli("localhost", "root", "", "byahero_db");

echo "<h2>Database Connection: </h2>";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "✓ Connected successfully<br><br>";
}

// Check tables
echo "<h2>Drivers Table:</h2>";
$drivers = $conn->query("SELECT id, first_name, last_name, status FROM drivers LIMIT 5");
if (!$drivers) {
    echo "❌ Error: " . $conn->error;
} else {
    echo "✓ Table exists. Records: " . $drivers->num_rows . "<br>";
    while ($row = $drivers->fetch_assoc()) {
        echo "- Driver ID: " . $row['id'] . ", Name: " . $row['first_name'] . " " . $row['last_name'] . ", Status: " . $row['status'] . "<br>";
    }
}

echo "<h2>Ride Types Table:</h2>";
$rides = $conn->query("SELECT id, name, base_price, per_km_price FROM ride_types");
if (!$rides) {
    echo "❌ Error: " . $conn->error;
} else {
    echo "✓ Table exists. Records: " . $rides->num_rows . "<br>";
    while ($row = $rides->fetch_assoc()) {
        echo "- Ride ID: " . $row['id'] . ", Name: " . $row['name'] . ", Base: ₱" . $row['base_price'] . ", Per KM: ₱" . $row['per_km_price'] . "<br>";
    }
}

echo "<h2>Bookings Table:</h2>";
$bookings = $conn->query("SELECT id, booking_id, user_id, driver_id, status FROM bookings ORDER BY created_at DESC LIMIT 5");
if (!$bookings) {
    echo "❌ Error: " . $conn->error;
} else {
    echo "✓ Table exists. Total records: " . $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'] . "<br>";
    if ($bookings->num_rows > 0) {
        while ($row = $bookings->fetch_assoc()) {
            echo "- Booking ID: " . $row['booking_id'] . ", User: " . $row['user_id'] . ", Driver: " . ($row['driver_id'] ?? 'None') . ", Status: " . $row['status'] . "<br>";
        }
    } else {
        echo "No bookings yet<br>";
    }
}
?>
