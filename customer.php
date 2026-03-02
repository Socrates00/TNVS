<?php
// 1. SESSION CHECK - Dapat laging nasa pinakataas
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create ride types table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS ride_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    icon VARCHAR(50),
    base_price DECIMAL(10,2),
    per_km_price DECIMAL(10,2),
    capacity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create bookings table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id VARCHAR(20) UNIQUE,
    user_id INT,
    driver_id INT,
    ride_type_id INT,
    pickup_location VARCHAR(300),
    destination_location VARCHAR(300),
    pickup_lat DECIMAL(10,8),
    pickup_lng DECIMAL(11,8),
    dest_lat DECIMAL(10,8),
    dest_lng DECIMAL(11,8),
    estimated_distance DECIMAL(10,2),
    estimated_fare DECIMAL(10,2),
    actual_fare DECIMAL(10,2),
    payment_method VARCHAR(50),
    promo_code VARCHAR(50),
    status ENUM('pending', 'accepted', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_user (user_id),
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    FOREIGN KEY (ride_type_id) REFERENCES ride_types(id) ON DELETE SET NULL
)");;

// Insert default ride types if empty
$check = $conn->query("SELECT COUNT(*) as count FROM ride_types")->fetch_assoc();
if ($check['count'] == 0) {
    $conn->query("INSERT INTO ride_types (name, icon, base_price, per_km_price, capacity) VALUES 
    ('ByaHERO Sedan', 'fa-car', 50, 15, 4),
    ('ByaHERO 6-Str', 'fa-van-shuttle', 80, 20, 6),
    ('ByaHERO Moto', 'fa-motorcycle', 30, 8, 1)");
}

// Fetch user info
$user_stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
if ($user_stmt) {
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();
} else {
    // Table doesn't exist, create fallback
    $user = array(
        'username' => $_SESSION['username'] ?? 'User',
        'email' => 'user@byahero.com'
    );
}


// Fetch ride types
$ride_types = $conn->query("SELECT * FROM ride_types ORDER BY base_price ASC");
if (!$ride_types) {
    // Table might not have records, create defaults
    $conn->query("INSERT INTO ride_types (name, icon, base_price, per_km_price, capacity) VALUES 
    ('ByaHERO Sedan', 'fa-car', 50, 15, 4),
    ('ByaHERO 6-Str', 'fa-van-shuttle', 80, 20, 6),
    ('ByaHERO Moto', 'fa-motorcycle', 30, 8, 1)");
    $ride_types = $conn->query("SELECT * FROM ride_types ORDER BY base_price ASC");
}

// Fetch user's last 5 bookings
$bookings_query = "SELECT * FROM bookings WHERE user_id = " . intval($_SESSION['user_id']) . " ORDER BY created_at DESC LIMIT 5";
$user_bookings = $conn->query($bookings_query);
if (!$user_bookings) {
    // Create an empty result object
    $user_bookings = new stdClass();
    $user_bookings->num_rows = 0;
}

// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Security token validation failed");
    }

    $pickup = $conn->real_escape_string($_POST['pickup_location']);
    $destination = $conn->real_escape_string($_POST['destination_location']);
    $pickup_lat = floatval($_POST['pickup_lat']);
    $pickup_lng = floatval($_POST['pickup_lng']);
    $dest_lat = floatval($_POST['dest_lat']);
    $dest_lng = floatval($_POST['dest_lng']);
    $ride_type_id = intval($_POST['ride_type_id']);
    $estimated_distance = floatval($_POST['estimated_distance']);
    $estimated_fare = floatval($_POST['estimated_fare']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    $promo_code = isset($_POST['promo_code']) ? $conn->real_escape_string($_POST['promo_code']) : null;

    // Generate unique booking ID
    $booking_id = "BK" . date('YmdHis') . rand(1000, 9999);

    // Insert booking with NO driver assigned - admin will assign manually
    $driver_id = null;
    $booking_stmt = $conn->prepare(
        "INSERT INTO bookings (booking_id, user_id, driver_id, ride_type_id, pickup_location, destination_location, pickup_lat, pickup_lng, dest_lat, dest_lng, estimated_distance, estimated_fare, payment_method, promo_code, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
    );
    $booking_stmt->bind_param(
        "siiissddddddss",
        $booking_id, $_SESSION['user_id'], $driver_id, $ride_type_id,
        $pickup, $destination, $pickup_lat, $pickup_lng, $dest_lat, $dest_lng,
        $estimated_distance, $estimated_fare, $payment_method, $promo_code
    );

    if ($booking_stmt->execute()) {
        $booking_id_created = $booking_id;
        $booking_stmt->close();
        header("Location: customer.php?booking_confirmed=" . urlencode($booking_id_created));
        exit();
    } else {
        $error_msg = "Booking Error: " . $booking_stmt->error;
        $booking_stmt->close();
    }
}

// Check if booking was just confirmed
$booking_confirmed = isset($_GET['booking_confirmed']) ? $conn->real_escape_string($_GET['booking_confirmed']) : null;
$error_msg = isset($error_msg) ? $error_msg : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ByaHERO - Mobile TNVS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="customer.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    
    <style>
        .user-info { padding: 15px; background: #f8f9fa; border-radius: 10px; margin-bottom: 15px; font-size: 13px; }
        .user-info strong { display: block; color: var(--hero-green); }
        .ride-history { padding: 15px; background: #f8f9fa; border-radius: 10px; margin-top: 15px; max-height: 150px; overflow-y: auto; }
        .history-item { padding: 8px 0; border-bottom: 1px solid #e0e0e0; font-size: 12px; }
        .history-item:last-child { border-bottom: none; }
        .history-status { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
        .status-completed { background: #c8e6c9; color: #2e7d32; }
        .status-pending { background: #fff9c4; color: #f57f17; }
        .status-cancelled { background: #ffcdd2; color: #c62828; }
        .booking-confirmed { background: #c8e6c9; padding: 15px; border-radius: 10px; margin-bottom: 15px; text-align: center; }
        .booking-confirmed strong { color: #2e7d32; display: block; margin-bottom: 5px; }
        .distance-info { background: #e3f2fd; padding: 10px; border-radius: 8px; margin: 10px 0; font-size: 13px; }
        
        /* Autocomplete Styles */
        .location-input-wrapper { position: relative; }
        .autocomplete-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .autocomplete-item {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s;
        }
        .autocomplete-item:hover {
            background: #f5f5f5;
        }
        .autocomplete-item i {
            color: #ff4757;
            font-size: 14px;
        }
        .autocomplete-item-text {
            flex: 1;
            font-size: 13px;
        }
        .autocomplete-item-name {
            font-weight: 600;
            color: #333;
        }
        .autocomplete-item-desc {
            font-size: 11px;
            color: #777;
        }
    </style>
</head>
<body>

    <div id="map-surface" class="map-surface" style="height: 100vh; width: 100%;" style="height: 100vh; width: 100%;"></div>

    <?php include('includes/customer_nav.php'); ?>

    <div class="booking-sheet">
        <div class="drag-handle"></div>

        <!-- User Info -->
        <div class="user-info">
            <i class="fas fa-user-circle"></i> <strong><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></strong>
            <?php echo htmlspecialchars($user['email'] ?? 'Loading...'); ?>
        </div>

        <!-- Booking Confirmed Alert -->
        <?php if ($booking_confirmed): ?>
        <div class="booking-confirmed">
            <strong>✓ Booking Confirmed!</strong>
            Booking ID: <?php echo htmlspecialchars($booking_confirmed); ?><br>
            <small>Driver is being matched...</small>
        </div>
        <?php endif; ?>

        <!-- Error Message Alert -->
        <?php if ($error_msg): ?>
        <div style="background: #ffebee; border-left: 4px solid #f44336; padding: 12px; margin-bottom: 15px; border-radius: 4px; color: #c62828;">
            <strong>❌ Booking Error</strong><br>
            <small><?php echo htmlspecialchars($error_msg); ?></small>
        </div>
        <?php endif; ?>

        <div class="search-container">
            <div class="loc-item">
                <i class="fas fa-circle-dot" style="color: var(--hero-green);"></i>
                <input type="text" id="pickupInput" placeholder="Detecting location..." readonly>
            </div>
            <div style="height: 1px; background: #eee; margin-left: 30px;"></div>
            <div class="loc-item location-input-wrapper">
                <i class="fas fa-location-dot" style="color: #ff4757;"></i>
                <input type="text" id="destInput" placeholder="Where to, Hero?" oninput="debounceSearchSuggestions(this.value)" autocomplete="off">
                <div id="autocompleteDropdown" class="autocomplete-dropdown" style="display: none;"></div>
            </div>
        </div>

        <!-- Distance Info -->
        <div id="distanceInfo" class="distance-info" style="display: none;">
            <i class="fas fa-route"></i> Distance: <span id="distanceDisplay">0</span> km | Estimated Fare: <span id="fareDisplay">₱0</span>
        </div>

        <p style="font-size: 11px; font-weight: 800; color: #aaa; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 1px;">Available Rides</p>
        
        <div class="ride-scroll">
            <?php 
            if ($ride_types && $ride_types->num_rows > 0):
                $ride_types->data_seek(0);
                $first = true;
                while($ride = $ride_types->fetch_assoc()): ?>
                <div class="ride-card <?php echo $first ? 'active' : ''; ?>" onclick="selectRide(this, <?php echo $ride['id']; ?>, '<?php echo htmlspecialchars($ride['name']); ?>', <?php echo $ride['base_price']; ?>, <?php echo $ride['per_km_price']; ?>)">
                    <i class="fas <?php echo htmlspecialchars($ride['icon']); ?>"></i>
                    <span class="name"><?php echo htmlspecialchars($ride['name']); ?></span>
                    <span class="price">₱<?php echo number_format($ride['base_price'], 2); ?></span>
                </div>
                <?php $first = false; endwhile; ?>
            <?php else: ?>
                <div class="ride-card active" onclick="selectRide(this, 1, 'ByaHERO Sedan', 50, 15)">
                    <i class="fas fa-car"></i>
                    <span class="name">ByaHERO Sedan</span>
                    <span class="price">₱50.00</span>
                </div>
                <div class="ride-card" onclick="selectRide(this, 2, 'ByaHERO 6-Str', 80, 20)">
                    <i class="fas fa-van-shuttle"></i>
                    <span class="name">ByaHERO 6-Str</span>
                    <span class="price">₱80.00</span>
                </div>
                <div class="ride-card" onclick="selectRide(this, 3, 'ByaHERO Moto', 30, 8)">
                    <i class="fas fa-motorcycle"></i>
                    <span class="name">ByaHERO Moto</span>
                    <span class="price">₱30.00</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="payment-selector" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 0 5px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-wallet" style="color: #555;"></i>
                <select id="paymentMethod" style="border: none; background: transparent; font-weight: 700; font-size: 14px; cursor: pointer;">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="gcash">GCash</option>
                </select>
            </div>
            <input type="text" id="promoCode" placeholder="Promo code" style="width: 100px; padding: 5px; border: 1px solid #ddd; border-radius: 5px; font-size: 12px;">
        </div>

        <form method="POST" id="bookingForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="confirm_booking" value="1">
            <input type="hidden" id="pickupLatInput" name="pickup_lat" value="">
            <input type="hidden" id="pickupLngInput" name="pickup_lng" value="">
            <input type="hidden" id="destLatInput" name="dest_lat" value="">
            <input type="hidden" id="destLngInput" name="dest_lng" value="">
            <input type="hidden" id="rideTypeInput" name="ride_type_id" value="">
            <input type="hidden" id="distanceInput" name="estimated_distance" value="">
            <input type="hidden" id="fareInput" name="estimated_fare" value="">
            <input type="hidden" id="pickupLocationInput" name="pickup_location" value="">
            <input type="hidden" id="destLocationInput" name="destination_location" value="">
            <input type="hidden" id="paymentInput" name="payment_method" value="cash">
            <input type="hidden" id="promoInput" name="promo_code" value="">

            <button type="submit" class="btn-confirm-booking" onclick="return validateBooking()">
                <span>Confirm Booking</span>
                <span id="display-price">₱0 <i class="fas fa-arrow-right" style="margin-left: 10px;"></i></span>
            </button>
        </form>

        <!-- Ride History -->
        <?php if($user_bookings && $user_bookings->num_rows > 0): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <strong style="font-size: 12px;">Recent Rides</strong>
            <button type="button" id="toggleHistoryBtn" onclick="toggleRideHistory()" style="border: none; background: none; cursor: pointer; font-size: 14px; color: #666; padding: 5px 10px;">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="ride-history" id="rideHistoryContent">
            <?php while($booking = $user_bookings->fetch_assoc()): ?>
            <div class="history-item">
                <div><?php echo htmlspecialchars(substr($booking['destination_location'], 0, 40)); ?></div>
                <span class="history-status status-<?php echo $booking['status']; ?>"><?php echo strtoupper($booking['status']); ?></span>
                <small> • ₱<?php echo number_format($booking['actual_fare'] ?? $booking['estimated_fare'], 2); ?></small>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <script>
        // --- INITIALIZE MAP ---
        const map = L.map('map-surface', { zoomControl: true }).setView([14.5995, 120.9842], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const geocoder = L.Control.Geocoder.nominatim();
        let userMarker, destMarker, searchTimeout;
        let selectedRideTypeId = null;
        let selectedRidePrice = 0;
        let selectedPerKmPrice = 0;
        let userLat, userLng, destLat, destLng;

        const userIcon = L.divIcon({
            className: 'user-location-dot',
            html: '<div style="background: #2196F3; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.3);"></div>',
            iconSize: [22, 22]
        });

        // --- HAVERSINE FORMULA FOR DISTANCE ---
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Earth radius in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // --- CALCULATE FARE ---
        function calculateFare() {
            if (userLat && userLng && destLat && destLng && selectedRideTypeId) {
                const distance = calculateDistance(userLat, userLng, destLat, destLng);
                const fare = selectedRidePrice + (distance * selectedPerKmPrice);
                
                document.getElementById('distanceInfo').style.display = 'block';
                document.getElementById('distanceDisplay').textContent = distance.toFixed(2);
                document.getElementById('fareDisplay').textContent = '₱' + fare.toFixed(2);
                document.getElementById('distanceInput').value = distance.toFixed(2);
                document.getElementById('fareInput').value = fare.toFixed(2);
                document.getElementById('display-price').innerHTML = `₱${fare.toFixed(2)} <i class="fas fa-arrow-right"></i>`;
            }
        }

        // --- LIVE LOCATION & ADDRESS DETECTION ---
        function detectUserLocation() {
            if (navigator.geolocation) {
                document.getElementById('pickupInput').value = "Detecting your location...";

                navigator.geolocation.getCurrentPosition((position) => {
                    userLat = position.coords.latitude;
                    userLng = position.coords.longitude;
                    const coords = [userLat, userLng];

                    if (!userMarker) {
                        userMarker = L.marker(coords, {icon: userIcon}).addTo(map);
                    } else {
                        userMarker.setLatLng(coords);
                    }
                    map.flyTo(coords, 18);

                    document.getElementById('pickupLatInput').value = userLat;
                    document.getElementById('pickupLngInput').value = userLng;

                    // FETCH ADDRESS NAME
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${userLat}&lon=${userLng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.address) {
                            const road = data.address.road || data.address.pedestrian || data.address.suburb || "";
                            const city = data.address.city || data.address.town || "";
                            const address = road + (city ? ", " + city : "");
                            document.getElementById('pickupInput').value = address || "Current Location";
                            document.getElementById('pickupLocationInput').value = address;
                        } else {
                            document.getElementById('pickupInput').value = "Current Location";
                            document.getElementById('pickupLocationInput').value = "Current Location";
                        }
                        calculateFare();
                    })
                    .catch(err => {
                        console.log("Geocoding error:", err);
                        document.getElementById('pickupInput').value = "Location Detected";
                        document.getElementById('pickupLocationInput').value = "Location Detected (" + userLat.toFixed(4) + ", " + userLng.toFixed(4) + ")";
                        calculateFare();
                    });

                }, (error) => {
                    console.log("Geolocation error:", error);
                    document.getElementById('pickupInput').value = "Enable location access";
                    alert("Please enable location access for better experience");
                }, { enableHighAccuracy: true, timeout: 10000 });
            }
        }

        detectUserLocation();

        // --- DEBOUNCED AUTOCOMPLETE SUGGESTIONS ---
        let autocompleteTimeout;
        function debounceSearchSuggestions(val) {
            clearTimeout(autocompleteTimeout);
            const dropdown = document.getElementById('autocompleteDropdown');
            
            if (!val || val.length < 2) {
                dropdown.style.display = 'none';
                return;
            }

            // Show loading state
            dropdown.innerHTML = '<div style="padding: 12px; text-align: center; color: #999;"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
            dropdown.style.display = 'block';

            autocompleteTimeout = setTimeout(() => {
                handleSearchSuggestions(val);
            }, 300); // 300ms delay for better performance
        }

        // --- AUTOCOMPLETE SUGGESTIONS ---
        function handleSearchSuggestions(val) {
            const dropdown = document.getElementById('autocompleteDropdown');
            
            // Fetch suggestions from Nominatim with optimized query
            fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(val)}&format=json&limit=10&countrycodes=ph&viewbox=120.5,14.1,121.5,15.3`)
                .then(response => response.json())
                .then(results => {
                    if (results && results.length > 0) {
                        dropdown.innerHTML = '';
                        results.forEach(result => {
                            const div = document.createElement('div');
                            div.className = 'autocomplete-item';
                            
                            // Extract display name and address
                            const name = result.name || result.display_name.split(',')[0];
                            const address = result.display_name;
                            
                            div.innerHTML = `
                                <i class="fas fa-map-pin"></i>
                                <div class="autocomplete-item-text">
                                    <div class="autocomplete-item-name">${name}</div>
                                    <div class="autocomplete-item-desc">${address.substring(0, 50)}...</div>
                                </div>
                            `;
                            
                            div.addEventListener('click', () => {
                                selectDestination(name, result.display_name, parseFloat(result.lat), parseFloat(result.lon));
                            });
                            
                            dropdown.appendChild(div);
                        });
                        dropdown.style.display = 'block';
                    } else {
                        dropdown.innerHTML = '<div style="padding: 12px; text-align: center; color: #999;">No results found</div>';
                        dropdown.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.log("Autocomplete error:", err);
                    dropdown.innerHTML = '<div style="padding: 12px; text-align: center; color: #c62828;">Error fetching results</div>';
                    dropdown.style.display = 'block';
                });
        }

        // --- SELECT DESTINATION FROM SUGGESTIONS ---
        function selectDestination(placeName, fullAddress, lat, lng) {
            destLat = lat;
            destLng = lng;
            
            document.getElementById('destInput').value = placeName;
            document.getElementById('destLatInput').value = destLat;
            document.getElementById('destLngInput').value = destLng;
            document.getElementById('destLocationInput').value = fullAddress;
            
            if (destMarker) destMarker.setLatLng([destLat, destLng]);
            else destMarker = L.marker([destLat, destLng]).addTo(map);
            
            map.flyTo([destLat, destLng], 16, { animate: true, duration: 1.5 });
            
            // Hide dropdown
            document.getElementById('autocompleteDropdown').style.display = 'none';
            
            calculateFare();
        }

        // --- SEARCH & AUTO-MOVE TO DESTINATION (Fallback) ---
        function handleSearch(val) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (val.length > 2) {
                    geocoder.geocode(val + ", Philippines", (results) => {
                        if (results && results.length > 0) {
                            destLat = results[0].center.lat;
                            destLng = results[0].center.lng;
                            
                            document.getElementById('destLatInput').value = destLat;
                            document.getElementById('destLngInput').value = destLng;
                            document.getElementById('destLocationInput').value = val;
                            
                            if (destMarker) destMarker.setLatLng([destLat, destLng]);
                            else destMarker = L.marker([destLat, destLng]).addTo(map);
                            
                            map.flyTo([destLat, destLng], 16, { animate: true, duration: 1.5 });
                            calculateFare();
                        }
                    });
                }
            }, 1000);
        }

        // --- UI LOGIC ---
        function selectRide(element, rideTypeId, name, basePrice, perKmPrice) {
            document.querySelectorAll('.ride-card').forEach(c => c.classList.remove('active'));
            element.classList.add('active');
            selectedRideTypeId = rideTypeId;
            selectedRidePrice = basePrice;
            selectedPerKmPrice = perKmPrice;
            document.getElementById('rideTypeInput').value = rideTypeId;
            calculateFare();
        }

        // --- VALIDATE BOOKING ---
        function validateBooking() {
            const pickup = document.getElementById('pickupInput').value;
            const dest = document.getElementById('destInput').value;
            
            if (!pickup || pickup.includes("Detecting") || pickup.includes("Enable")) {
                alert("Please wait for location to be detected");
                return false;
            }
            if (!dest) {
                alert("Please enter your destination");
                return false;
            }
            if (!selectedRideTypeId) {
                alert("Please select a ride type");
                return false;
            }
            if (!userLat || !userLng || !destLat || !destLng) {
                alert("Unable to detect location. Please try again.");
                return false;
            }
            
            document.getElementById('paymentInput').value = document.getElementById('paymentMethod').value;
            document.getElementById('promoInput').value = document.getElementById('promoCode').value;
            
            return true;
        }

        // --- TOGGLE RIDE HISTORY ---
        function toggleRideHistory() {
            const historyContent = document.getElementById('rideHistoryContent');
            const btn = document.getElementById('toggleHistoryBtn');
            
            if (historyContent.style.display === 'none') {
                historyContent.style.display = 'block';
                btn.innerHTML = '<i class="fas fa-chevron-down"></i>';
            } else {
                historyContent.style.display = 'none';
                btn.innerHTML = '<i class="fas fa-chevron-up"></i>';
            }
        }

        // --- CLOSE AUTOCOMPLETE WHEN CLICKING ELSEWHERE ---
        document.addEventListener('click', (e) => {
            const destInput = document.getElementById('destInput');
            const dropdown = document.getElementById('autocompleteDropdown');
            
            if (!destInput.parentElement.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>