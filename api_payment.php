<?php
/**
 * Payment Management API
 * Handles payment-related operations
 */

session_start();
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Check authorization
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized - Please login']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

// Create refunds table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS refunds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    amount DECIMAL(10,2),
    reason VARCHAR(500),
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
)");

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'get_revenue_stats':
        getRevenueStats();
        break;
    
    case 'get_transactions':
        getTransactions();
        break;
    
    case 'get_payment_methods':
        getPaymentMethods();
        break;
    
    case 'request_refund':
        requestRefund();
        break;
    
    case 'get_refunds':
        getRefunds();
        break;
    
    case 'process_refund':
        processRefund();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}

function getRevenueStats() {
    global $conn;
    
    $date_filter = isset($_POST['date']) ? $_POST['date'] : 'today';
    
    $date_query = '';
    switch ($date_filter) {
        case 'today':
            $date_query = "AND DATE(created_at) = CURDATE()";
            break;
        case 'week':
            $date_query = "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $date_query = "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
    
    $stats = [
        'total_revenue' => $conn->query("SELECT COALESCE(SUM(estimated_fare), 0) as total FROM bookings WHERE status IN ('completed', 'in_progress') $date_query")->fetch_assoc()['total'],
        'total_transactions' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status IN ('completed', 'in_progress') $date_query")->fetch_assoc()['count'],
        'completed_transactions' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed' $date_query")->fetch_assoc()['count'],
        'pending_refunds' => $conn->query("SELECT COUNT(*) as count FROM refunds WHERE status = 'pending'")->fetch_assoc()['count'],
    ];
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

function getTransactions() {
    global $conn;
    
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $payment_method = isset($_POST['method']) ? $_POST['method'] : '';
    $date_filter = isset($_POST['date']) ? $_POST['date'] : '';
    
    // Validate limit and offset
    if ($limit < 1 || $limit > 500) $limit = 50;
    if ($offset < 0) $offset = 0;
    
    // Build date query
    $date_query = '';
    if ($date_filter === 'today') {
        $date_query = "AND DATE(b.created_at) = CURDATE()";
    } elseif ($date_filter === 'week') {
        $date_query = "AND b.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($date_filter === 'month') {
        $date_query = "AND b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
    
    // Build method query with prepared statement
    $method_query = '';
    if ($payment_method && $payment_method !== 'all') {
        $valid_methods = ['cash', 'card', 'gcash'];
        if (in_array($payment_method, $valid_methods)) {
            $method_query = "AND b.payment_method = ?";
        }
    }
    
    $query = "
        SELECT 
            b.id, b.booking_id, b.payment_method, b.estimated_fare,
            b.status, b.created_at,
            u.username as customer_name,
            d.first_name, d.last_name
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN drivers d ON b.driver_id = d.id
        WHERE b.status IN ('completed', 'in_progress')
        $date_query
        $method_query
        ORDER BY b.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    if ($method_query) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $payment_method, $limit, $offset);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = [];
    
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    echo json_encode(['success' => true, 'transactions' => $transactions, 'count' => count($transactions)]);
    $stmt->close();
}

function getPaymentMethods() {
    global $conn;
    
    $query = "
        SELECT 
            payment_method,
            COUNT(*) as count,
            SUM(estimated_fare) as total,
            AVG(estimated_fare) as average
        FROM bookings
        WHERE DATE(created_at) = CURDATE() AND status IN ('completed', 'in_progress')
        GROUP BY payment_method
    ";
    
    $result = $conn->query($query);
    $methods = [];
    
    while ($row = $result->fetch_assoc()) {
        $methods[] = $row;
    }
    
    // Calculate percentages
    $total_revenue = array_sum(array_column($methods, 'total'));
    foreach ($methods as &$method) {
        $method['percentage'] = $total_revenue > 0 ? round(($method['total'] / $total_revenue) * 100) : 0;
    }
    
    echo json_encode(['success' => true, 'methods' => $methods]);
}

function requestRefund() {
    global $conn;
    
    // Check if user is admin or authorized
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    
    // Validate inputs
    if (!$booking_id || !$reason) {
        echo json_encode(['success' => false, 'message' => 'Booking ID and reason are required']);
        return;
    }
    
    if (strlen($reason) < 5 || strlen($reason) > 500) {
        echo json_encode(['success' => false, 'message' => 'Reason must be between 5 and 500 characters']);
        return;
    }
    
    // Get booking amount using prepared statement
    $stmt = $conn->prepare("SELECT id, estimated_fare FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
    
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        return;
    }
    
    // Check if refund already exists
    $check_stmt = $conn->prepare("SELECT id FROM refunds WHERE booking_id = ? AND status != 'rejected'");
    $check_stmt->bind_param("i", $booking_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'A refund request already exists for this booking']);
        $check_stmt->close();
        return;
    }
    $check_stmt->close();
    
    $refund_stmt = $conn->prepare("INSERT INTO refunds (booking_id, amount, reason) VALUES (?, ?, ?)");
    $refund_stmt->bind_param("ids", $booking_id, $booking['estimated_fare'], $reason);
    
    if ($refund_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Refund request submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting refund request']);
    }
    
    $refund_stmt->close();
}

function getRefunds() {
    global $conn;
    
    $status = isset($_POST['status']) ? $_POST['status'] : 'pending';
    $status = $conn->real_escape_string($status);
    
    $query = "
        SELECT 
            r.id, r.booking_id, r.amount, r.reason, r.status, r.requested_at,
            b.booking_id as booking_code,
            u.username as customer_name
        FROM refunds r
        LEFT JOIN bookings b ON r.booking_id = b.id
        LEFT JOIN users u ON b.user_id = u.id
        WHERE r.status = '$status'
        ORDER BY r.requested_at DESC
    ";
    
    $result = $conn->query($query);
    $refunds = [];
    
    while ($row = $result->fetch_assoc()) {
        $refunds[] = $row;
    }
    
    echo json_encode(['success' => true, 'refunds' => $refunds, 'count' => count($refunds)]);
}

function processRefund() {
    global $conn;
    
    // Check if user is admin (authorization)
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only admins can process refunds']);
        return;
    }
    
    $refund_id = isset($_POST['refund_id']) ? intval($_POST['refund_id']) : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    $valid_statuses = ['approved', 'rejected', 'completed'];
    
    if (!$refund_id || !in_array($new_status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE refunds SET status = ?, processed_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $new_status, $refund_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Refund processed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error processing refund']);
    }
    
    $stmt->close();
}

$conn->close();
?>
