<!DOCTYPE html>
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TNVS - Book a Ride</title>
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

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
        <div class="notif-badge"><i class="fas fa-bell"></i></div>
        <div class="profile-ring"><?php echo $initials; ?></div>
    </div>
</div>