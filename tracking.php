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