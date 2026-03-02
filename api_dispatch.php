<?php
/**
 * Dispatching API
 * Handles AJAX requests for real-time dispatching operations
 */

session_start();
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'get_pending_requests':
        getPendingRequests();
        break;
    
    case 'get_active_trips':
        getActiveTrips();
        break;
    
    case 'assign_driver':
        assignDriver();
        break;
    
    case 'reject_booking':
        rejectBooking();
        break;
    
    case 'get_available_drivers':
        getAvailableDrivers();
        break;
    
    case 'update_trip_status':
        updateTripStatus();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}

function getPendingRequests() {
    global $conn;
    $result = $conn->query("
        SELECT 
            b.id, b.booking_id, b.pickup_location, b.destination_location, 
            b.estimated_distance, b.estimated_fare, b.created_at, b.payment_method,
            u.username as customer_name,
            rt.name as ride_type
        FROM bookings b 
        LEFT JOIN users u ON b.user_id = u.id 
        LEFT JOIN ride_types rt ON b.ride_type_id = rt.id 
        WHERE b.status = 'pending' 
        ORDER BY b.created_at DESC
    ");
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $requests, 'count' => count($requests)]);
}

function getActiveTrips() {
    global $conn;
    $result = $conn->query("
        SELECT 
            b.id, b.booking_id, b.pickup_location, b.destination_location,
            b.estimated_distance, b.status, b.created_at,
            u.username as customer_name,
            d.first_name, d.last_name,
            rt.name as ride_type
        FROM bookings b 
        LEFT JOIN users u ON b.user_id = u.id 
        LEFT JOIN drivers d ON b.driver_id = d.id 
        LEFT JOIN ride_types rt ON b.ride_type_id = rt.id 
        WHERE b.status IN ('accepted', 'in_progress') 
        ORDER BY b.created_at DESC
    ");
    
    $trips = [];
    while ($row = $result->fetch_assoc()) {
        $trips[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $trips, 'count' => count($trips)]);
}

function assignDriver() {
    global $conn;
    
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $driver_id = isset($_POST['driver_id']) ? intval($_POST['driver_id']) : 0;
    
    if (!$booking_id || !$driver_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    // Verify booking exists
    $booking_check = $conn->query("SELECT id FROM bookings WHERE id = $booking_id");
    if ($booking_check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        return;
    }
    
    // Verify driver exists
    $driver_check = $conn->query("SELECT id FROM drivers WHERE id = $driver_id");
    if ($driver_check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Driver not found']);
        return;
    }
    
    // Update booking with driver assignment
    $stmt = $conn->prepare("UPDATE bookings SET driver_id = ?, status = 'accepted' WHERE id = ?");
    $stmt->bind_param("ii", $driver_id, $booking_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Driver assigned successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function rejectBooking() {
    global $conn;
    
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    if (!$booking_id) {
        echo json_encode(['success' => false, 'message' => 'Booking ID required']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function getAvailableDrivers() {
    global $conn;
    
    $result = $conn->query("
        SELECT id, first_name, last_name, performance_rating, phone 
        FROM drivers 
        WHERE status = 'active' 
        ORDER BY first_name ASC
    ");
    
    $drivers = [];
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $drivers, 'count' => count($drivers)]);
}

function updateTripStatus() {
    global $conn;
    
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $new_status = isset($_POST['status']) ? $_POST['status'] : '';
    
    if (!$booking_id || !in_array($new_status, ['in_progress', 'completed'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    if ($new_status === 'completed') {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'completed', completed_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute()) {
            // Record payment when trip is completed
            recordPayment($booking_id);
            echo json_encode(['success' => true, 'message' => 'Trip status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
    } else {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Trip status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
    }
    
    $stmt->close();
}

function recordPayment($booking_id) {
    global $conn;
    
    // Get booking details
    $stmt = $conn->prepare("
        SELECT b.id, b.user_id, b.estimated_fare, b.payment_method, 
               b.pickup_location, b.destination_location,
               rt.name as ride_type
        FROM bookings b
        LEFT JOIN ride_types rt ON b.ride_type_id = rt.id
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
    
    if ($booking && $booking['user_id']) {
        // Insert payment record
        $insert_stmt = $conn->prepare("
            INSERT INTO customer_payments 
            (booking_id, user_id, ride_type, pickup_location, destination_location, amount, payment_method, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')
        ");
        
        $insert_stmt->bind_param(
            "iisssds",
            $booking_id,
            $booking['user_id'],
            $booking['ride_type'],
            $booking['pickup_location'],
            $booking['destination_location'],
            $booking['estimated_fare'],
            $booking['payment_method']
        );
        
        $insert_stmt->execute();
        $insert_stmt->close();
    }
}

$conn->close();
?>
