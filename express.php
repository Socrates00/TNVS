<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ByaHERO - Express Delivery</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --hero-green: #00b14f;
            --hero-dark: #008f40;
            --white: #ffffff;
        }

        body { font-family: 'Inter', sans-serif; margin: 0; background: #f4f7f6; overflow: hidden; height: 100vh; }
        
        /* Fullscreen Map */
        #map { position: absolute; top: 0; left: 0; width: 100%; height: 60%; z-index: 1; }

        /* Back Button */
        .back-btn {
            position: absolute; top: 20px; left: 20px; z-index: 10;
            background: white; width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; color: black; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Bottom Booking Panel */
        .bottom-panel {
            position: absolute; bottom: 0; left: 0; right: 0; height: 45%;
            background: white; padding: 25px; border-radius: 30px 30px 0 0;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.15); z-index: 10;
            display: flex; flex-direction: column; gap: 15px;
        }

        /* Search Section inside Bottom Panel */
        .search-box {
            background: #f8fafb; border: 2px solid var(--hero-green); border-radius: 20px;
            padding: 5px 15px;
        }
        .input-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; position: relative; }
        .input-row:first-child { border-bottom: 1px solid #eee; }
        .dot { width: 8px; height: 8px; border-radius: 50%; }
        input { border: none; background: transparent; width: 100%; outline: none; font-weight: 600; font-size: 15px; }

        /* Suggestions */
        .suggestions {
            position: absolute; background: white; width: 100%; z-index: 100;
            top: 100%; left: 0; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-height: 120px; overflow-y: auto; display: none;
        }
        .s-item { padding: 12px; border-bottom: 1px solid #f0f0f0; cursor: pointer; font-size: 13px; }

        /* Vehicle Cards */
        .vehicle-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .vehicle-card {
            background: #1a1a1a; color: white; padding: 15px; border-radius: 15px;
            text-align: center; cursor: pointer; border: 3px solid transparent; transition: 0.3s;
        }
        .vehicle-card.active { border-color: var(--hero-green); box-shadow: 0 0 15px rgba(0, 177, 79, 0.3); }
        .vehicle-card i { font-size: 20px; margin-bottom: 5px; display: block; }
        .vehicle-card span { font-weight: 700; font-size: 13px; }

        /* Final Action Row */
        .action-row { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
        .fare-display p { margin: 0; color: #888; font-size: 11px; font-weight: 800; }
        .fare-display h2 { margin: 0; font-size: 28px; font-weight: 900; }

        .btn-confirm {
            background: var(--hero-green); color: white; border: none;
            padding: 15px 40px; border-radius: 15px; font-size: 16px;
            font-weight: 800; cursor: pointer; transition: 0.3s;
        }
        .btn-confirm:hover { background: var(--hero-dark); }
    </style>
</head>
<body>

<div id="map"></div>
<a href="home.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>

<div class="bottom-panel">
    <div class="search-box">
        <div class="input-row">
            <div class="dot" style="background: var(--hero-green);"></div>
            <input type="text" id="pickup" placeholder="Pick-up Location" autocomplete="off">
            <div id="pickup-results" class="suggestions"></div>
        </div>
        <div class="input-row">
            <div class="dot" style="background: #ff4757;"></div>
            <input type="text" id="dropoff" placeholder="Where to send?" autocomplete="off">
            <div id="dropoff-results" class="suggestions"></div>
        </div>
    </div>

    <div class="vehicle-grid">
        <div class="vehicle-card active" onclick="updateFare('Bike', '₱85.00', this)">
            <i class="fas fa-motorcycle"></i>
            <span>ByaHERO Bike</span>
        </div>
        <div class="vehicle-card" onclick="updateFare('Car', '₱160.00', this)">
            <i class="fas fa-car"></i>
            <span>ByaHERO Car</span>
        </div>
    </div>

    <div class="action-row">
        <div class="fare-display">
            <p id="type-label">BIKE DELIVERY</p>
            <h2 id="fare-price">₱85.00</h2>
        </div>
        <button class="btn-confirm" onclick="confirmOrder()">Confirm Booking</button>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('map', { zoomControl: false }).setView([14.5995, 120.9842], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    let marker;

    function updateFare(type, price, el) {
        document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('type-label').innerText = type.toUpperCase() + ' DELIVERY';
        document.getElementById('fare-price').innerText = price;
    }

    async function searchPlace(query, resultsId, inputId) {
        if (query.length < 3) return;
        const resp = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}&countrycodes=ph`);
        const data = await resp.json();
        const resultsDiv = document.getElementById(resultsId);
        resultsDiv.innerHTML = '';
        resultsDiv.style.display = 'block';

        data.forEach(place => {
            const item = document.createElement('div');
            item.className = 's-item';
            item.innerText = place.display_name;
            item.onclick = () => {
                document.getElementById(inputId).value = place.display_name;
                resultsDiv.style.display = 'none';
                const pos = [place.lat, place.lon];
                map.flyTo(pos, 16);
                if (marker) map.removeLayer(marker);
                marker = L.marker(pos).addTo(map);
            };
            resultsDiv.appendChild(item);
        });
    }

    document.getElementById('pickup').oninput = (e) => searchPlace(e.target.value, 'pickup-results', 'pickup');
    document.getElementById('dropoff').oninput = (e) => searchPlace(e.target.value, 'dropoff-results', 'dropoff');

    function confirmOrder() {
        alert("Hero, your booking is confirmed!");
        window.location.href = 'home.php';
    }
</script>

</body>
</html>