<?php
// PHP helper para malaman kung anong page ang kasalukuyang binubuksan
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --hero-green: #00b14f;
        --text-dark: #1a1a1a;
        --text-gray: #666;
        --bg-white: #ffffff;
        --sidebar-width: 260px;
    }

    /* Sidebar layout for admin pages */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: var(--sidebar-width);
        background: #1a1a1a;
        border-right: 1px solid #333;
        padding: 24px 20px;
        display: flex;
        flex-direction: column;
        gap: 18px;
        z-index: 1100;
        font-family: 'Inter', sans-serif;
    }

    .sidebar .brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        text-align: center;
    }

    .sidebar .logo-img { height: 150px; width: auto; }

    .sidebar .brand-name { font-weight: 800; font-size: 18px; color: #fff; }

    .sidebar-nav { display:flex; flex-direction:column; gap:6px; margin-top: 12px; }

    .sidebar-nav a { text-decoration:none; color:#aaa; padding:12px 14px; border-radius:10px; display:flex; align-items:center; gap:10px; font-weight:700; transition:0.2s; }

    .sidebar-nav a:hover { color:#fff; background:rgba(255,255,255,0.05); }

    .sidebar-nav a i { width:18px; text-align:center; }

    .sidebar-nav a.active { background: var(--hero-green); color:#fff; }

    .sidebar .spacer { flex:1; }

    .sidebar .system-status { background: rgba(0,177,79,0.2); color: var(--hero-green); padding:8px 12px; border-radius:12px; font-weight:800; font-size:12px; }

    .sidebar .profile-row { display:flex; align-items:center; gap:12px; }

    .sidebar .avatar { width:44px; height:44px; border-radius:50%; background:var(--hero-green); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; }

    .sidebar .admin-meta { font-size:13px; color:#fff; }

    .sidebar .logout-link { margin-top:8px; display:inline-block; text-decoration:none; color:#e74c3c; font-weight:800; padding:8px 10px; border-radius:8px; border:1px solid #e74c3c; background:transparent; transition:0.2s; }

    .sidebar .logout-link:hover { background:#e74c3c; color:#fff; }

    /* Shift page content to the right to make room for the sidebar */
    body { margin-left: var(--sidebar-width); }

    /* Responsive: collapse sidebar on small screens */
    @media (max-width: 900px) {
        .sidebar { position:relative; width:100%; height:auto; border-right:none; flex-direction:row; align-items:center; padding:12px; }
        .sidebar-nav { flex-direction:row; gap:8px; margin-left:12px; }
        body { margin-left:0; }
    }
</style>

<aside class="sidebar" aria-label="Admin navigation">
    <div class="brand">
        <img src="logo.png" class="logo-img" alt="ByaHERO">
        <div class="brand-name">ByaHERO <div style="font-weight:400;color:var(--text-gray);font-size:12px">Admin Panel</div></div>
    </div>

    <nav class="sidebar-nav">
        <a href="admin.php" class="<?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>"><i class="fas fa-th-large"></i> Fleet Management</a>
        <a href="dispatching.php" class="<?php echo ($current_page == 'dispatching.php') ? 'active' : ''; ?>"><i class="fas fa-taxi"></i> Dispatching</a>
        <a href="tracking.php" class="<?php echo ($current_page == 'tracking.php') ? 'active' : ''; ?>"><i class="fas fa-map-marker-alt"></i> Tracking</a>
        <a href="payments.php" class="<?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>"><i class="fas fa-file-invoice-dollar"></i> Payments</a>
    </nav>

    <div class="spacer"></div>

    <div class="system-status">● SYSTEM LIVE</div>

    <div class="profile-row">
        <div class="avatar">AU</div>
        <div class="admin-meta">
            <div style="font-weight:800;color:#fff">Admin User</div>
            <div style="font-size:12px;color:#fff">Admin</div>
        </div>
    </div>
    <a href="logout.php" class="logout-link">Logout</a>
</aside>