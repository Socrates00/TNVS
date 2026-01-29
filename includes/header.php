<?php
// PHP helper para malaman kung anong page ang kasalukuyang binubuksan
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --hero-green: #00b14f; /* Official ByaHERO Green */
        --text-dark: #1a1a1a;
        --text-gray: #666;
        --bg-white: #ffffff;
    }

    .main-header {
        background: var(--bg-white);
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 25px;
        border-bottom: 1px solid #eee;
        font-family: 'Inter', sans-serif;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .logo-section {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .logo-img {
        height: 200px; /* Hindi binago gaya ng instruction mo */
        width: auto;
        margin-right: -50px; 
        margin-left: -50px;
    }

    .brand-name {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.5px;
        white-space: nowrap;
    }

    .brand-name span {
        font-weight: 400;
        color: var(--text-gray);
    }

    .top-nav {
        display: flex;
        gap: 5px;
        background: #f8fafb;
        padding: 5px;
        border-radius: 12px;
        align-items: center;
    }

    /* Style para sa bagong Back Button */
    .back-nav {
        text-decoration: none;
        color: #e74c3c; /* Reddish para madaling makita */
        font-size: 14px;
        font-weight: 700;
        padding: 10px 15px;
        border-right: 1px solid #ddd;
        margin-right: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: 0.3s;
    }

    .back-nav:hover {
        color: #c0392b;
    }

    .nav-item {
        text-decoration: none;
        color: var(--text-gray);
        font-size: 14px;
        font-weight: 600;
        padding: 10px 18px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.3s;
    }

    .nav-item i {
        font-size: 16px;
    }

    .nav-item.active {
        background: var(--bg-white);
        color: var(--hero-green);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .nav-item:hover:not(.active) {
        color: var(--hero-green);
        background: rgba(0, 177, 79, 0.05);
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .system-status {
        background: #e8fdf0;
        color: var(--hero-green);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        border-left: 1px solid #eee;
        padding-left: 20px;
    }

    .avatar {
        width: 38px;
        height: 38px;
        background: var(--hero-green);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }

    .user-info {
        line-height: 1.3;
    }

    .admin-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .admin-role {
        font-size: 11px;
        color: var(--text-gray);
    }
</style>

<header class="main-header">
    <div class="logo-section">
        <img src="logo.png" class="logo-img" alt="ByaHERO">
        <span class="brand-name">ByaHERO <span>Admin Panel</span></span>
    </div>

    <nav class="top-nav">
        <a href="home.php" class="back-nav">
            <i class="fas fa-arrow-left"></i> Home
        </a>

        <a href="index.php" class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> Fleet Dashboard
        </a>
        <a href="dispatching.php" class="nav-item <?php echo ($current_page == 'dispatching.php') ? 'active' : ''; ?>">
            <i class="fas fa-taxi"></i> Taxi Dispatching
        </a>
        <a href="tracking.php" class="nav-item <?php echo ($current_page == 'tracking.php') ? 'active' : ''; ?>">
            <i class="fas fa-map-marker-alt"></i> Real-time Tracking
        </a>
        <a href="payments.php" class="nav-item <?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i> Payment Mgmt
        </a>
    </nav>

    <div class="header-right">
        <span class="system-status">● SYSTEM LIVE</span>
        <i class="far fa-bell" style="color: var(--text-gray); font-size: 18px; cursor: pointer;"></i>
        
        <div class="user-profile">
            <div class="avatar">AU</div>
            <div class="user-info">
                <div class="admin-name">Admin User</div>
                <div class="admin-role">Admin</div>
            </div>
        </div>
    </div>
</header>