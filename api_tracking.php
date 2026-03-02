<?php
/**
 * Real-time Tracking API
 * Provides JSON data for live trip tracking
 */

session_start();
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'get_active_trips':
        getActiveTrips();
        break;
    
    case 'get_trip_details':
        getTripDetails();
        break;
    
    case 'get_trip_statistics':
        getTripStatistics();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}

function getActiveTrips() {
    global $conn;
    
    $status_filter = isset($_POST['status']) ? $_POST['status'] : 'all';
    
    if ($status_filter === 'all') {
        $query = "
            SELECT 
                b.id, b.booking_id, b.pickup_location, b.destination_location,
                b.pickup_lat, b.pickup_lng, b.dest_lat, b.dest_lng,
                b.estimated_distance, b.estimated_fare, b.status, 
                b.created_at, b.completed_at,
                u.username as customer_name, u.email as customer_email,
                d.first_name, d.last_name, d.average_rating, d.contact_number as driver_phone,
                rt.name as ride_type
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN drivers d ON b.driver_id = d.id
            LEFT JOIN ride_types rt ON b.ride_type_id = rt.id
            WHERE b.status IN ('accepted', 'in_progress')
            ORDER BY b.created_at DESC
        ";
    } else {
        $status_filter = $conn->real_escape_string($status_filter);
        $query = "
            SELECT 
                b.id, b.booking_id, b.pickup_location, b.destination_location,
                b.pickup_lat, b.pickup_lng, b.dest_lat, b.dest_lng,
                b.estimated_distance, b.estimated_fare, b.status, 
                b.created_at, b.completed_at,
                u.username as customer_name, u.email as customer_email,
                d.first_name, d.last_name, d.average_rating, d.contact_number as driver_phone,
                rt.name as ride_type
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN drivers d ON b.driver_id = d.id
            LEFT JOIN ride_types rt ON b.ride_type_id = rt.id
            WHERE b.status = '$status_filter'
            ORDER BY b.created_at DESC
        ";
    }
    
    $result = $conn->query($query);
    $trips = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $trips[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'trips' => $trips, 'count' => count($trips)]);
}

function getTripDetails() {
    global $conn;
    
    $trip_id = isset($_POST['trip_id']) ? intval($_POST['trip_id']) : 0;
    
    if (!$trip_id) {
        echo json_encode(['success' => false, 'message' => 'Trip ID required']);
        return;
    }
    
    $query = "
        SELECT 
            b.id, b.booking_id, b.pickup_location, b.destination_location,
            b.pickup_lat, b.pickup_lng, b.dest_lat, b.dest_lng,
            b.estimated_distance, b.estimated_fare, b.actual_fare, b.status, 
            b.created_at, b.completed_at, b.payment_method,
            u.username as customer_name, u.email as customer_email,
            d.first_name, d.last_name, d.average_rating, d.contact_number as driver_phone,
            rt.name as ride_type
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN drivers d ON b.driver_id = d.id
        LEFT JOIN ride_types rt ON b.ride_type_id = rt.id
        WHERE b.id = $trip_id
    ";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $trip = $result->fetch_assoc();
        
        // Calculate elapsed time
        $created = new DateTime($trip['created_at']);
        $now = new DateTime();
        $elapsed = $now->diff($created);
        
        $trip['elapsed_time'] = [
            'hours' => $elapsed->h,
            'minutes' => $elapsed->i,
            'seconds' => $elapsed->s,
            'total_seconds' => $elapsed->days * 86400 + $elapsed->h * 3600 + $elapsed->i * 60 + $elapsed->s
        ];
        
        // Calculate ETA (rough estimate: distance / average speed of 25 km/h)
        $avg_speed = 25; // km/h
        $eta_minutes = ceil($trip['estimated_distance'] / $avg_speed * 60);
        $trip['eta_minutes'] = $eta_minutes;
        
        echo json_encode(['success' => true, 'trip' => $trip]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Trip not found']);
    }
}

function getTripStatistics() {
    global $conn;
    
    $today = date('Y-m-d');
    
    $stats = [
        'active_trips' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status IN ('accepted', 'in_progress')")->fetch_assoc()['count'],
        'accepted_trips' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'accepted'")->fetch_assoc()['count'],
        'in_progress_trips' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'in_progress'")->fetch_assoc()['count'],
        'completed_today' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed' AND DATE(created_at) = '$today'")->fetch_assoc()['count'],
        'total_distance_today' => $conn->query("SELECT SUM(estimated_distance) as total FROM bookings WHERE DATE(created_at) = '$today'")->fetch_assoc()['total'] ?? 0,
        'total_revenue_today' => $conn->query("SELECT SUM(estimated_fare) as total FROM bookings WHERE DATE(created_at) = '$today' AND status IN ('completed', 'in_progress')")->fetch_assoc()['total'] ?? 0
    ];
    
    echo json_encode(['success' => true, 'statistics' => $stats]);
}

$conn->close();
?>
