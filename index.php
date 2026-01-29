<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<main class="container">
    <div class="header-flex">
        <h1 class="dashboard-title">Fleet Dashboard</h1>
        <div class="system-badge">SYSTEM LIVE</div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Active Vehicles</span>
            <span class="stat-value">42</span>
            <span class="stat-trend trend-up">↑ 12% vs yesterday</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Trips Today</span>
            <span class="stat-value">189</span>
            <span class="stat-sub">8 Ongoing</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Daily Revenue</span>
            <span class="stat-value">₱24,500</span>
            <span class="stat-sub">Target: ₱30k</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Approvals</span>
            <span class="stat-value">3</span>
            <span class="stat-trend trend-new">New driver apps</span>
        </div>
    </div>

    <div class="main-content-layout">
        <div class="content-card fleet-section">
            <div class="card-header">
                <h3>Active Fleet Management</h3>
                <button class="btn-add">+ Add Vehicle</button>
            </div>
            <table class="fleet-table">
                <thead>
                    <tr>
                        <th>VEHICLE / PLATE</th>
                        <th>ASSIGNED DRIVER</th>
                        <th>STATUS</th>
                        <th>LAST MAINT.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Toyota Vios</strong><br><small>NGT-1284</small></td>
                        <td>Juan Dela Cruz</td>
                        <td><span class="status-badge status-available">AVAILABLE</span></td>
                        <td>2023-11-20</td>
                    </tr>
                    <tr>
                        <td><strong>Mitsubishi Mirage</strong><br><small>ABC-4432</small></td>
                        <td>Maria Clara</td>
                        <td><span class="status-badge status-trip">ON TRIP</span></td>
                        <td>2023-12-05</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="sidebar-card">
            <h3>Fleet Distribution</h3>
            <div class="dist-item">
                <div class="dist-info"><span>Available</span><span>28 Units</span></div>
                <div class="progress-bar"><div class="progress bg-green" style="width: 70%;"></div></div>
            </div>
            <div class="dist-item">
                <div class="dist-info"><span>On Trip</span><span>8 Units</span></div>
                <div class="progress-bar"><div class="progress bg-blue" style="width: 25%;"></div></div>
            </div>
        </div>
    </div>
</main>