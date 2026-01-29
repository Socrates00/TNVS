<?php
// 1. SESSION CHECK - Dapat laging nasa pinakataas
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
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
</head>
<body>

    <div id="map-surface" class="map-surface" style="height: 100vh; width: 100%;"></div>

    <?php include('includes/customer_nav.php'); ?>

    <div class="booking-sheet">
        <div class="drag-handle"></div>

        <div class="search-container">
            <div class="loc-item">
                <i class="fas fa-circle-dot" style="color: var(--hero-green);"></i>
                <input type="text" id="pickupInput" placeholder="Inaalam ang iyong pwesto..." readonly>
            </div>
            <div style="height: 1px; background: #eee; margin-left: 30px;"></div>
            <div class="loc-item">
                <i class="fas fa-location-dot" style="color: #ff4757;"></i>
                <input type="text" id="destInput" placeholder="Saan tayo, Hero? (e.g. Litex)" onkeyup="handleSearch(this.value)">
            </div>
        </div>

        <p style="font-size: 11px; font-weight: 800; color: #aaa; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 1px;">Available Rides</p>
        
        <div class="ride-scroll">
            <div class="ride-card active" onclick="selectRide(this, 'Sedan', 142)">
                <i class="fas fa-car"></i>
                <span class="name">ByaHERO Sedan</span>
                <span class="price">₱142.00</span>
            </div>
            <div class="ride-card" onclick="selectRide(this, '6-Str', 210)">
                <i class="fas fa-van-shuttle"></i>
                <span class="name">ByaHERO 6-Str</span>
                <span class="price">₱210.00</span>
            </div>
            <div class="ride-card" onclick="selectRide(this, 'Moto', 65)">
                <i class="fas fa-motorcycle"></i>
                <span class="name">ByaHERO Moto</span>
                <span class="price">₱65.00</span>
            </div>
        </div>

        <div class="payment-selector" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 0 5px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-wallet" style="color: #555;"></i>
                <span id="payment-text" style="font-size: 14px; font-weight: 700;">Cash</span>
            </div>
            <span style="font-size: 12px; font-weight: 700; color: var(--hero-green);">Apply Promo</span>
        </div>

        <button class="btn-confirm-booking" onclick="confirmBooking()">
            <span>Confirm Booking</span>
            <span id="display-price">₱142.00 <i class="fas fa-arrow-right" style="margin-left: 10px;"></i></span>
        </button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <script>
        // --- INITIALIZE MAP ---
        const map = L.map('map-surface', { zoomControl: false }).setView([14.5995, 120.9842], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const geocoder = L.Control.Geocoder.nominatim();
        let userMarker, destMarker, searchTimeout;

        const userIcon = L.divIcon({
            className: 'user-location-dot',
            html: '<div style="background: #2196F3; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.3);"></div>',
            iconSize: [22, 22]
        });

        // --- 2. LIVE LOCATION & ADDRESS DETECTION ---
        function detectUserLocation() {
            if (navigator.geolocation) {
                document.getElementById('pickupInput').value = "Detecting your street...";

                navigator.geolocation.getCurrentPosition((position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const coords = [lat, lng];

                    if (!userMarker) {
                        userMarker = L.marker(coords, {icon: userIcon}).addTo(map);
                    } else {
                        userMarker.setLatLng(coords);
                    }
                    map.flyTo(coords, 18);

                    // FETCH ADDRESS NAME DIRECTLY
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.address) {
                            const road = data.address.road || data.address.pedestrian || data.address.suburb || "";
                            const city = data.address.city || data.address.town || "";
                            document.getElementById('pickupInput').value = road + (city ? ", " + city : "");
                        } else {
                            document.getElementById('pickupInput').value = "Pinned Location";
                        }
                    })
                    .catch(() => {
                        document.getElementById('pickupInput').value = "Location Detected";
                    });

                }, () => {
                    document.getElementById('pickupInput').value = "GPS Access Denied";
                }, { enableHighAccuracy: true });
            }
        }

        detectUserLocation();

        // --- 3. SEARCH & AUTO-MOVE TO DESTINATION ---
        function handleSearch(val) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (val.length > 3) {
                    geocoder.geocode(val + ", Philippines", (results) => {
                        if (results && results.length > 0) {
                            const target = results[0].center;
                            if (destMarker) destMarker.setLatLng(target);
                            else destMarker = L.marker(target).addTo(map);
                            map.flyTo(target, 16, { animate: true, duration: 1.5 });
                        }
                    });
                }
            }, 1000);
        }

        // --- 4. UI LOGIC ---
        function selectRide(element, type, price) {
            document.querySelectorAll('.ride-card').forEach(c => c.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('display-price').innerHTML = `₱${price}.00 <i class="fas fa-arrow-right"></i>`;
        }

        function confirmBooking() {
            const dest = document.getElementById('destInput').value;
            if (!dest) { alert("Saan tayo pupunta, Hero?"); return; }
            
            const overlay = document.createElement('div');
            overlay.className = 'matching-overlay';
            overlay.innerHTML = `
                <div class="loader-content">
                    <div class="spinner"></div>
                    <h3>Finding ByaHERO...</h3>
                    <p>📍 ${dest}</p>
                </div>
            `;
            document.body.appendChild(overlay);

            setTimeout(() => { window.location.href = 'home.php'; }, 3000);
        }
    </script>
</body>
</html>