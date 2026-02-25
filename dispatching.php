<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<main class="container">
    <div class="header-flex">
        <h1 class="dashboard-title">Taxi Dispatching</h1>
        <div class="system-badge">SYSTEM LIVE</div>
    </div>

    <!-- Driver Availability Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Available Drivers</span>
            <span class="stat-value">28</span>
            <span class="stat-sub">Ready for dispatch</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">On Trip</span>
            <span class="stat-value">8</span>
            <span class="stat-trend trend-up">Active now</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Avg Response Time</span>
            <span class="stat-value">2.5m</span>
            <span class="stat-trend trend-up">↑ 15% faster</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Completed Today</span>
            <span class="stat-value">189</span>
            <span class="stat-sub">Target: 200</span>
        </div>
    </div>

    <!-- Ongoing Trips Card -->
    <div class="content-card">
        <div class="card-header">
            <h3>Ongoing Trips Tracking</h3>
            <button class="btn-secondary">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
        
        <table class="fleet-table">
            <thead>
                <tr>
                    <th>TRIP ID</th>
                    <th>DRIVER</th>
                    <th>PASSENGER</th>
                    <th>DESTINATION</th>
                    <th>PROGRESS</th>
                    <th>ETA</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>TRP-902</strong></td>
                    <td>Pedro Penduko</td>
                    <td>Ana Cruz</td>
                    <td>Quezon City</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress bg-green" style="width: 65%;"></div>
                        </div>
                    </td>
                    <td>8 mins</td>
                    <td><a href="tracking.php?id=902" class="track-btn">
                        <i class="fas fa-map-marked-alt"></i> Track Live
                    </a></td>
                </tr>
                <tr>
                    <td><strong>TRP-903</strong></td>
                    <td>Maria Clara</td>
                    <td>Jose Rizal</td>
                    <td>Makati CBD</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress bg-blue" style="width: 40%;"></div>
                        </div>
                    </td>
                    <td>15 mins</td>
                    <td><a href="tracking.php?id=903" class="track-btn">
                        <i class="fas fa-map-marked-alt"></i> Track Live
                    </a></td>
                </tr>
                <tr>
                    <td><strong>TRP-904</strong></td>
                    <td>Juan Tamad</td>
                    <td>Mang Kanor</td>
                    <td>Manila Bay</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress bg-orange" style="width: 85%;"></div>
                        </div>
                    </td>
                    <td>3 mins</td>
                    <td><a href="tracking.php?id=904" class="track-btn">
                        <i class="fas fa-map-marked-alt"></i> Track Live
                    </a></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Live Ride Requests Card -->
    <div class="content-card">
        <div class="card-header">
            <h3>Live Ride Requests</h3>
            <span class="stat-trend trend-new">3 Pending</span>
        </div>
        
        <div class="request-box">
            <div class="req-header">
                <div class="customer-info">
                    <span class="req-id">REQ#772</span>
                    <h4>Maria Santos</h4>
                    <div class="route-info">
                        <i class="fas fa-map-marker-alt"></i> SM North → Makati CBD
                    </div>
                </div>
                <span class="status-badge status-pending">PENDING</span>
            </div>
            <div class="action-btns">
                <button class="btn-assign">
                    <i class="fas fa-check-circle"></i> AUTO ASSIGN
                </button>
                <button class="btn-reject">
                    <i class="fas fa-times-circle"></i> REJECT
                </button>
            </div>
        </div>

        <div class="request-box">
            <div class="req-header">
                <div class="customer-info">
                    <span class="req-id">REQ#773</span>
                    <h4>Juan Dela Cruz</h4>
                    <div class="route-info">
                        <i class="fas fa-map-marker-alt"></i> Quezon Ave → BGC Taguig
                    </div>
                </div>
                <span class="status-badge status-pending">PENDING</span>
            </div>
            <div class="action-btns">
                <button class="btn-assign">
                    <i class="fas fa-check-circle"></i> AUTO ASSIGN
                </button>
                <button class="btn-reject">
                    <i class="fas fa-times-circle"></i> REJECT
                </button>
            </div>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>
