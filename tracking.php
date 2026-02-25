<<<<<<< HEAD
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
=======
<?php
session_start();
// Security check para admin lang
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-time Tracking - ByaHERO</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --hero-green: #00b14f; --hero-bg: #f8fafb; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--hero-bg); padding: 20px 40px; }

        .container { max-width: 1100px; margin: 0 auto; }

        /* Back Button papunta sa Admin index.php */
        .back-btn { 
            text-decoration: none; color: #1a1a1a; font-weight: 800; 
            display: inline-flex; align-items: center; gap: 10px; 
            margin-bottom: 20px; background: white; padding: 10px 20px; 
            border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .header h1 { font-size: 28px; font-weight: 800; }
        .status-badge { background: #e8fdf0; color: var(--hero-green); padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 800; }

        /* Map Card */
        .map-card { background: white; border-radius: 24px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 25px; }
        .map-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .map-title { font-weight: 800; color: #333; display: flex; align-items: center; gap: 10px; }
        .live-indicator { font-size: 11px; color: #3b82f6; font-weight: 700; display: flex; align-items: center; gap: 5px; }
        .live-dot { width: 8px; height: 8px; background: #3b82f6; border-radius: 50%; animation: pulse 1.5s infinite; }

        #map { height: 450px; width: 100%; border-radius: 15px; border: 1px solid #eee; z-index: 1; }

        /* Status Grid */
        .status-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .status-box { background: white; padding: 20px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border-left: 5px solid var(--hero-green); }
        .status-label { font-size: 13px; color: #888; font-weight: 600; margin-bottom: 5px; }
        .status-value { font-size: 22px; font-weight: 800; color: #1a1a1a; }
        .status-value.blue { color: #2563eb; }

        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
    </style>
</head>
<body>

    <div class="container">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="header">
            <h1>GPS Trackers System</h1>
            <div class="status-badge">SYSTEM LIVE</div>
        </div>

        <div class="map-card">
            <div class="map-header">
                <div class="map-title">
                    <i class="fas fa-satellite-dish" style="color: #3b82f6;"></i>
                    Live Fleet Monitor
                </div>
                <div class="live-indicator">
                    <div class="live-dot"></div> Updates every 5s
                </div>
            </div>
            
            <div id="map"></div>
        </div>

        <div class="status-grid">
            <div class="status-box" style="border-left-color: #2563eb;">
                <div class="status-label">Vehicle Speed (Avg)</div>
                <div class="status-value blue">24 km/h</div>
            </div>
            <div class="status-box" style="border-left-color: var(--hero-green);">
                <div class="status-label">Active Routes</div>
                <div class="status-value">14 Active</div>
            </div>
            <div class="status-box" style="border-left-color: #f59e0b;">
                <div class="status-label">Signal Strength</div>
                <div class="status-value" style="color: #f59e0b;">Excellent</div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize Map centered on Manila
        var map = L.map('map').setView([14.5995, 120.9842], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Sample Vehicle Markers
        var vehicles = [
            { id: "V-101", lat: 14.6091, lng: 121.0223, status: "Moving" },
            { id: "V-205", lat: 14.5547, lng: 121.0244, status: "Idle" }
        ];

        vehicles.forEach(function(v) {
            L.circleMarker([v.lat, v.lng], {
                radius: 8,
                fillColor: "#00b14f",
                color: "#fff",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(map).bindPopup("Vehicle: " + v.id);
        });
    </script>
</body>
</html>
>>>>>>> 749282eb7b691ce991d83ae99e804a2526595e8c
