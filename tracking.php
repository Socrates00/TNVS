<?php
session_start();
// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Fetch all active trips with full details
if ($status_filter === 'all') {
    $trips_query = "
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
    $trips_query = "
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

$trips = $conn->query($trips_query);

// Get statistics
$active_trips = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status IN ('accepted', 'in_progress')")->fetch_assoc()['count'];
$accepted_trips = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'accepted'")->fetch_assoc()['count'];
$in_progress_trips = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'in_progress'")->fetch_assoc()['count'];
$completed_today = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<main class="container">
    <div class="header-flex">
        <h1 class="dashboard-title">GPS Tracking System</h1>
        <div class="system-badge">SYSTEM LIVE</div>
    </div>

    <!-- Tracking Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Active Trips</span>
            <span class="stat-value"><?php echo $active_trips; ?></span>
            <span class="stat-trend trend-up">In service now</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Waiting Assignment</span>
            <span class="stat-value"><?php echo $accepted_trips; ?></span>
            <span class="stat-sub">Drivers en route</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">In Progress</span>
            <span class="stat-value"><?php echo $in_progress_trips; ?></span>
            <span class="stat-trend trend-up">On trip</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Completed Today</span>
            <span class="stat-value"><?php echo $completed_today; ?></span>
            <span class="stat-sub">Successful trips</span>
        </div>
    </div>

    <!-- Live Map Card -->
    <div class="content-card">
        <div class="card-header">
            <h3>
                <i class="fas fa-satellite-dish" style="color: #3b82f6;"></i>
                Live Fleet Monitor
            </h3>
            <div class="live-indicator">
                <div class="live-dot"></div>
                <span>Updates every 5s</span>
            </div>
        </div>
        
        <div id="map" style="height: 500px; width: 100%; border-radius: 15px; border: 1px solid #eee;"></div>
    </div>

    <!-- Vehicle List -->
    <div class="content-card">
        <div class="card-header">
            <h3>Active Trips</h3>
            <div style="display: flex; gap: 10px;">
                <button class="btn-secondary" onclick="filterTrips('all')" style="background: <?php echo $status_filter === 'all' ? 'var(--hero-green, #27ae60)' : '#f0f0f0'; ?>; color: <?php echo $status_filter === 'all' ? 'white' : '#333'; ?>;">
                    All
                </button>
                <button class="btn-secondary" onclick="filterTrips('accepted')" style="background: <?php echo $status_filter === 'accepted' ? 'var(--hero-green, #27ae60)' : '#f0f0f0'; ?>; color: <?php echo $status_filter === 'accepted' ? 'white' : '#333'; ?>;">
                    Accepted
                </button>
                <button class="btn-secondary" onclick="filterTrips('in_progress')" style="background: <?php echo $status_filter === 'in_progress' ? 'var(--hero-green, #27ae60)' : '#f0f0f0'; ?>; color: <?php echo $status_filter === 'in_progress' ? 'white' : '#333'; ?>;">
                    In Progress
                </button>
            </div>
        </div>

        <?php if ($trips && $trips->num_rows > 0): ?>
        <div style="overflow-x: auto;">
            <table class="fleet-table">
                <thead>
                    <tr>
                        <th>BOOKING ID</th>
                        <th>DRIVER</th>
                        <th>CUSTOMER</th>
                        <th>ROUTE</th>
                        <th>DISTANCE</th>
                        <th>FARE</th>
                        <th>STATUS</th>
                        <th>TIME</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($trip = $trips->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($trip['booking_id']); ?></strong>
                        </td>
                        <td>
                            <div style="font-weight: 600;">
                                <?php echo htmlspecialchars(($trip['first_name'] ?? 'Unknown') . ' ' . ($trip['last_name'] ?? '')); ?>
                            </div>
                            <?php if ($trip['average_rating']): ?>
                            <small style="color: #999;">⭐ <?php echo number_format($trip['average_rating'], 2); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight: 600;">
                                <?php echo htmlspecialchars($trip['customer_name'] ?? 'Unknown'); ?>
                            </div>
                            <small style="color: #999;"><?php echo htmlspecialchars(substr($trip['customer_email'] ?? '', 0, 25)); ?></small>
                        </td>
                        <td style="font-size: 13px;">
                            <i class="fas fa-circle-dot" style="color: #27ae60; margin-right: 5px;"></i>
                            <strong><?php echo htmlspecialchars(substr($trip['pickup_location'], 0, 20)); ?></strong>
                            <br>
                            <i class="fas fa-location-dot" style="color: #f44336; margin-right: 5px;"></i>
                            <strong><?php echo htmlspecialchars(substr($trip['destination_location'], 0, 20)); ?></strong>
                        </td>
                        <td>
                            <strong><?php echo number_format($trip['estimated_distance'], 2); ?> km</strong>
                        </td>
                        <td>
                            <strong>₱<?php echo number_format($trip['estimated_fare'], 2); ?></strong>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo str_replace('_', '-', $trip['status']); ?>" style="padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                <?php echo str_replace('_', ' ', $trip['status']); ?>
                            </span>
                        </td>
                        <td style="font-size: 12px; color: #666;">
                            <?php 
                            $created = new DateTime($trip['created_at']);
                            $now = new DateTime();
                            $elapsed = $now->diff($created);
                            
                            if ($elapsed->h > 0) {
                                echo $elapsed->h . 'h ' . $elapsed->i . 'm';
                            } else {
                                echo $elapsed->i . 'm ' . $elapsed->s . 's';
                            }
                            ?>
                        </td>
                        <td>
                            <button class="btn-action" onclick="focusTrip(<?php echo $trip['id']; ?>, <?php echo $trip['pickup_lat']; ?>, <?php echo $trip['pickup_lng']; ?>, <?php echo $trip['dest_lat']; ?>, <?php echo $trip['dest_lng']; ?>)" style="background: #2196F3; color: white; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                <i class="fas fa-map"></i> Map
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #999;">
            <i class="fas fa-inbox" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
            <strong>No active trips</strong><br>
            <small>There are currently no trips in this status</small>
        </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
<script>
    // Initialize Map centered on Metro Manila
    var map = L.map('map').setView([14.5995, 120.9842], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Custom icons
    var pickupIcon = L.divIcon({
        className: 'location-marker',
        html: '<div style="background: #27ae60; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i style="color: white; font-size: 12px;">✓</i></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    var destinationIcon = L.divIcon({
        className: 'location-marker',
        html: '<div style="background: #f44336; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i style="color: white; font-size: 14px;">📍</i></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    var driverIcon = L.divIcon({
        className: 'driver-marker',
        html: '<div style="background: #2196F3; width: 28px; height: 28px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 12px rgba(33, 150, 243, 0.4); animation: pulse 2s infinite;"><i style="color: white; font-size: 14px;">🚕</i></div>',
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    var tripLayers = {}; // Store trip layers for management

    // Add CSS for pulsing animation
    var style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { box-shadow: 0 2px 12px rgba(33, 150, 243, 0.4); }
            50% { box-shadow: 0 2px 20px rgba(33, 150, 243, 0.8); }
            100% { box-shadow: 0 2px 12px rgba(33, 150, 243, 0.4); }
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-accepted { background: #cfe2ff; color: #084298; }
        .status-in-progress { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d1e7dd; color: #0f5132; }
        .status-pending { background: #fff3cd; color: #856404; }
    `;
    document.head.appendChild(style);

    // Haversine formula to calculate distance
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth radius in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) + 
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Load trips from database via AJAX
    function loadTripsOnMap() {
        // Clear existing layers
        Object.keys(tripLayers).forEach(key => {
            tripLayers[key].forEach(layer => {
                if (layer) map.removeLayer(layer);
            });
        });
        tripLayers = {};

        // Fetch trips
        fetch('api_tracking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_active_trips&status=<?php echo $status_filter; ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.trips.length > 0) {
                data.trips.forEach(trip => {
                    let layers = [];

                    // Pickup marker
                    let pickupMarker = L.marker(
                        [parseFloat(trip.pickup_lat), parseFloat(trip.pickup_lng)],
                        { icon: pickupIcon }
                    ).addTo(map);
                    pickupMarker.bindPopup(`<strong>PICKUP</strong><br>${trip.pickup_location}`);
                    layers.push(pickupMarker);

                    // Destination marker
                    let destMarker = L.marker(
                        [parseFloat(trip.dest_lat), parseFloat(trip.dest_lng)],
                        { icon: destinationIcon }
                    ).addTo(map);
                    destMarker.bindPopup(`<strong>DESTINATION</strong><br>${trip.destination_location}`);
                    layers.push(destMarker);

                    // Draw route line
                    let routePath = L.polyline(
                        [[parseFloat(trip.pickup_lat), parseFloat(trip.pickup_lng)],
                         [parseFloat(trip.dest_lat), parseFloat(trip.dest_lng)]],
                        { color: '#2196F3', weight: 3, opacity: 0.7, dashArray: '5, 5' }
                    ).addTo(map);
                    layers.push(routePath);

                    // Driver position (at pickup for accepted, midway for in_progress)
                    let driverLat = trip.status === 'accepted' ? 
                        parseFloat(trip.pickup_lat) : 
                        (parseFloat(trip.pickup_lat) + parseFloat(trip.dest_lat)) / 2;
                    let driverLng = trip.status === 'accepted' ? 
                        parseFloat(trip.pickup_lng) : 
                        (parseFloat(trip.pickup_lng) + parseFloat(trip.dest_lng)) / 2;

                    let driverMarker = L.marker(
                        [driverLat, driverLng],
                        { icon: driverIcon }
                    ).addTo(map);
                    driverMarker.bindPopup(`
                        <div style="width: 200px; font-family: Arial, sans-serif;">
                            <strong>${trip.first_name} ${trip.last_name}</strong><br>
                            <small>Customer: ${trip.customer_name}</small><br>
                            <small>Booking: ${trip.booking_id}</small><br>
                            <small style="color: #2196F3;">Status: ${trip.status.toUpperCase()}</small>
                        </div>
                    `);
                    layers.push(driverMarker);

                    tripLayers[trip.id] = layers;
                });
            }
        })
        .catch(err => console.error('Error loading trips:', err));
    }

    // Focus on specific trip
    function focusTrip(tripId, pickupLat, pickupLng, destLat, destLng) {
        let bounds = L.latLngBounds(
            [parseFloat(pickupLat), parseFloat(pickupLng)],
            [parseFloat(destLat), parseFloat(destLng)]
        );
        map.fitBounds(bounds, { padding: [50, 50] });
    }

    // Filter trips
    function filterTrips(status) {
        window.location.href = '?status=' + status;
    }

    // Initial load
    loadTripsOnMap();

    // Auto-refresh every 5 seconds
    setInterval(loadTripsOnMap, 5000);
</script>

<?php include('includes/footer.php'); ?>
