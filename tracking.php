<?php
session_start();
// Security check para admin lang
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
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
            <span class="stat-label">Active GPS Units</span>
            <span class="stat-value">42</span>
            <span class="stat-trend trend-up">All connected</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Avg Vehicle Speed</span>
            <span class="stat-value">24 km/h</span>
            <span class="stat-sub">Within limits</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Active Routes</span>
            <span class="stat-value">14</span>
            <span class="stat-trend trend-up">In progress</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Signal Strength</span>
            <span class="stat-value">98%</span>
            <span class="stat-sub" style="color: #00b14f;">Excellent</span>
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
            <h3>Active Vehicle Locations</h3>
            <button class="btn-secondary">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>

        <table class="fleet-table">
            <thead>
                <tr>
                    <th>VEHICLE ID</th>
                    <th>DRIVER</th>
                    <th>CURRENT LOCATION</th>
                    <th>SPEED</th>
                    <th>GPS STATUS</th>
                    <th>LAST UPDATE</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>V-101</strong><br><small>NGT-1284</small></td>
                    <td>Pedro Penduko</td>
                    <td>Quezon City - Commonwealth Ave</td>
                    <td>35 km/h</td>
                    <td><span class="status-badge status-available">ACTIVE</span></td>
                    <td>2s ago</td>
                    <td><a href="#" class="track-btn" onclick="focusVehicle(14.6091, 121.0223)">
                        <i class="fas fa-crosshairs"></i> Center
                    </a></td>
                </tr>
                <tr>
                    <td><strong>V-205</strong><br><small>ABC-4432</small></td>
                    <td>Maria Clara</td>
                    <td>Makati - Ayala Avenue</td>
                    <td>18 km/h</td>
                    <td><span class="status-badge status-available">ACTIVE</span></td>
                    <td>1s ago</td>
                    <td><a href="#" class="track-btn" onclick="focusVehicle(14.5547, 121.0244)">
                        <i class="fas fa-crosshairs"></i> Center
                    </a></td>
                </tr>
                <tr>
                    <td><strong>V-312</strong><br><small>XYZ-7890</small></td>
                    <td>Juan Tamad</td>
                    <td>Manila - Roxas Boulevard</td>
                    <td>42 km/h</td>
                    <td><span class="status-badge status-available">ACTIVE</span></td>
                    <td>3s ago</td>
                    <td><a href="#" class="track-btn" onclick="focusVehicle(14.5764, 120.9822)">
                        <i class="fas fa-crosshairs"></i> Center
                    </a></td>
                </tr>
                <tr>
                    <td><strong>V-408</strong><br><small>DEF-5612</small></td>
                    <td>Andres Bonifacio</td>
                    <td>Taguig - BGC 5th Avenue</td>
                    <td>0 km/h</td>
                    <td><span class="status-badge status-pending">IDLE</span></td>
                    <td>5s ago</td>
                    <td><a href="#" class="track-btn" onclick="focusVehicle(14.5513, 121.0470)">
                        <i class="fas fa-crosshairs"></i> Center
                    </a></td>
                </tr>
            </tbody>
        </table>
    </div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Initialize Map centered on Manila
    var map = L.map('map').setView([14.5995, 120.9842], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Custom vehicle icon
    var vehicleIcon = L.divIcon({
        className: 'vehicle-marker',
        html: '<div style="background: #00b14f; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>',
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    // Sample Vehicle Markers with detailed info
    var vehicles = [
        { id: "V-101", plate: "NGT-1284", driver: "Pedro Penduko", lat: 14.6091, lng: 121.0223, status: "Moving", speed: "35 km/h" },
        { id: "V-205", plate: "ABC-4432", driver: "Maria Clara", lat: 14.5547, lng: 121.0244, status: "Moving", speed: "18 km/h" },
        { id: "V-312", plate: "XYZ-7890", driver: "Juan Tamad", lat: 14.5764, lng: 120.9822, status: "Moving", speed: "42 km/h" },
        { id: "V-408", plate: "DEF-5612", driver: "Andres Bonifacio", lat: 14.5513, lng: 121.0470, status: "Idle", speed: "0 km/h" }
    ];

    var markers = {};

    vehicles.forEach(function(v) {
        var marker = L.marker([v.lat, v.lng], {icon: vehicleIcon}).addTo(map);
        marker.bindPopup(`
            <div style="font-family: Inter, sans-serif;">
                <strong style="font-size: 14px; color: #1a1a1a;">${v.id}</strong><br>
                <small style="color: #666;">${v.plate}</small><br>
                <small style="color: #666;"><strong>Driver:</strong> ${v.driver}</small><br>
                <small style="color: #666;"><strong>Speed:</strong> ${v.speed}</small><br>
                <span style="display: inline-block; margin-top: 5px; background: #e6f7ed; color: #00b14f; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700;">${v.status.toUpperCase()}</span>
            </div>
        `);
        markers[v.id] = marker;
    });

    // Function to focus on specific vehicle
    function focusVehicle(lat, lng) {
        map.setView([lat, lng], 15);
        return false; // Prevent default link behavior
    }

    // Auto-update simulation (every 5 seconds)
    setInterval(function() {
        console.log("GPS update: All vehicles reporting");
        // In production, this would fetch real GPS data via API
    }, 5000);
</script>

<?php include('includes/footer.php'); ?>
