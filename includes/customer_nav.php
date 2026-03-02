<?php
function getInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return $initials ?: 'U';
}

$initials = getInitials($_SESSION['username'] ?? 'User');
?>

<div class="header-wrapper">
    <div class="pill-box">
        <a href="home.php" class="icon-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="vertical-divider"></div>
        <div class="brand-group">
            <img src="logo.png" alt="ByaHERO Logo" class="main-logo-img">
            <span class="brand-text">Bya<span class="highlight">HERO</span></span>
        </div>
    </div>

    <div class="profile-group">
        <a href="customer_payment_history.php" class="icon-btn" title="Payment History">
            <i class="fas fa-credit-card"></i>
        </a>
        <div class="notif-badge"><i class="fas fa-bell"></i></div>
        <div class="profile-ring"><?php echo $initials; ?></div>
    </div>
</div>