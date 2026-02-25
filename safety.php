<!DOCTYPE html>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ByaHERO - Safety</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="home.css">
    <style>
        /* Override header styling */
        .home-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            height: auto;
            gap: 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .home-logo-img {
            height: 45px;
            width: auto;
            object-fit: contain;
            margin: 0;
        }

        .home-brand-text {
            font-size: 1.3rem;
            font-weight: 900;
            color: #1a1a1a;
            white-space: nowrap;
        }

        .home-brand-text .highlight {
            color: #00b14f;
        }

        .header-icons {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-shrink: 0;
        }

        .notif-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #666;
            cursor: pointer;
            transition: all 0.3s;
        }

        .notif-circle:hover {
            background: #e5e7eb;
        }

        .profile {
            position: relative;
        }

        .user-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00b14f 0%, #008f40 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .user-circle:hover {
            box-shadow: 0 4px 12px rgba(0, 177, 79, 0.3);
            transform: scale(1.05);
        }

        .profile-dropdown {
            position: absolute;
            top: 55px;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 150px;
            z-index: 100;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .profile-dropdown[aria-hidden="false"] {
            opacity: 1;
            visibility: visible;
        }

        .dropdown-item {
            display: block;
            width: 100%;
            padding: 12px 16px;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            color: #1a1a1a;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        .logout-btn {
            color: #dc2626;
            font-weight: 600;
        }

        /* Safety specific styles */
        * { box-sizing: border-box; }
        body { background: #f8f9fa; }

        /* Hero Section */
        .safety-hero {
            background: linear-gradient(135deg, #00b14f 0%, #008f40 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 40px;
        }
        .safety-hero h1 { 
            font-size: 2.5rem; 
            margin-bottom: 10px; 
            font-weight: 900;
        }
        .safety-hero p { 
            font-size: 1rem; 
            opacity: 0.95;
            margin: 0;
        }

        /* Main Container */
        .safety-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }

        /* Section Headers */
        .safety-section {
            margin-bottom: 50px;
        }
        .section-title {
            font-size: 2rem;
            font-weight: 900;
            color: #1a1a1a;
            margin-bottom: 30px;
            text-align: center;
        }

        /* Safety Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 35px 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
        }
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 177, 79, 0.15);
            border-color: #00b14f;
        }
        .feature-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #e8fdf0 0%, #c8efda 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #00b14f;
        }
        .feature-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 12px;
        }
        .feature-card p {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
        }

        /* Emergency Section */
        .emergency-box {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 2px solid #fca5a5;
            border-radius: 20px;
            padding: 50px 30px;
            text-align: center;
            margin-bottom: 40px;
        }
        .emergency-box h2 {
            font-size: 2rem;
            font-weight: 900;
            color: #dc2626;
            margin-bottom: 15px;
        }
        .emergency-box p {
            font-size: 1rem;
            color: #7f1d1d;
            margin-bottom: 25px;
            margin: 0 0 25px;
        }
        .emergency-btn {
            background: #dc2626;
            color: white;
            padding: 14px 35px;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .emergency-btn:hover {
            background: #b91c1c;
            transform: scale(1.05);
        }

        /* Report Form */
        .report-box {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            max-width: 600px;
            margin: 0 auto;
        }
        .report-box h2 {
            font-size: 1.8rem;
            font-weight: 900;
            color: #1a1a1a;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #1a1a1a;
            font-size: 0.95rem;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00b14f;
            box-shadow: 0 0 0 3px rgba(0, 177, 79, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #00b14f 0%, #008f40 100%);
            color: white;
            padding: 16px 30px;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 8px 20px rgba(0, 177, 79, 0.2);
        }
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 177, 79, 0.35);
        }
        .submit-btn:active {
            transform: translateY(-1px);
        }

        /* Footer Styling */
        .home-footer {
            background: linear-gradient(135deg, #0f172a 0%, #1a1f35 100%);
            color: white;
            padding: 60px 20px 30px;
            margin-top: 80px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 50px;
            margin-bottom: 40px;
        }
        .footer-section h4 {
            font-size: 1.3rem;
            font-weight: 900;
            color: #00b14f;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .footer-section p {
            color: #b0b9cc;
            line-height: 1.7;
            font-size: 0.95rem;
            margin: 0;
        }
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-section ul li {
            margin-bottom: 12px;
        }
        .footer-section ul li a {
            color: #b0b9cc;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s;
            display: inline-block;
        }
        .footer-section ul li a:hover {
            color: #00b14f;
            padding-left: 5px;
        }
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            text-align: center;
        }
        .footer-bottom p {
            color: #7a8499;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Tips Section */
        .tips-section {
            margin-top: 40px;
        }
        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .tip-item {
            background: white;
            border-radius: 20px;
            padding: 35px 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
        }
        .tip-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 177, 79, 0.15);
            border-color: #00b14f;
        }
        .tip-item::before {
            content: attr(data-icon);
            display: inline-block;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #e8fdf0 0%, #c8efda 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #00b14f;
            margin-bottom: 20px;
            line-height: 60px;
            text-align: center;
        }
        .tip-item h4 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .tip-item h4 i {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #e8fdf0 0%, #c8efda 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00b14f;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .tip-item p {
            color: #666;
            font-size: 0.95rem;
            margin: 0;
            line-height: 1.7;
        }

        /* Back Button */
        .back-btn-container {
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
        }
        .back-btn {
            background: white;
            color: #00b14f;
            border: 2px solid #00b14f;
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .back-btn:hover {
            background: #00b14f;
            color: white;
            transform: translateX(-5px);
            box-shadow: 0 8px 20px rgba(0, 177, 79, 0.2);
        }
        .back-btn i {
            font-size: 1.2rem;
        }
            .home-header {
                padding: 12px 15px;
            }

            .home-logo-img {
                height: 40px;
            }

            .home-brand-text {
                font-size: 1.1rem;
            }

            .header-icons {
                gap: 15px;
            }

            .notif-circle,
            .user-circle {
                width: 40px;
                height: 40px;
                font-size: 0.85rem;
            }

            .safety-hero {
                padding: 40px 20px;
            }
            .safety-hero h1 {
                font-size: 1.8rem;
            }
            .section-title {
                font-size: 1.5rem;
            }
            .report-box {
                padding: 25px;
            }
            .feature-card {
                padding: 25px 20px;
            }
            .tips-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .tip-item {
                padding: 25px 20px;
            }
            .tip-item h4 {
                font-size: 1.1rem;
            }
            .tip-item p {
                font-size: 0.9rem;
            }
            .footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            .submit-btn {
                padding: 14px 25px;
                font-size: 1rem;
            }
            .back-btn {
                padding: 10px 18px;
                font-size: 0.9rem;
            }
            .back-btn-container {
                margin-bottom: 20px;
            }
        
    </style>
</head>
<body>
    <header class="home-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="logo.png" alt="ByaHERO" class="home-logo-img">
                <span class="home-brand-text">Bya<span class="highlight">HERO</span></span>
            </div>
            <div class="header-icons">
                <div class="notif-circle"><i class="fas fa-bell"></i></div>
                <div class="profile">
                    <div class="user-circle" id="profileToggle" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $initials; ?></div>
                    <div class="profile-dropdown" id="profileDropdown" aria-hidden="true">
                        <form id="logoutForm" method="post" action="logout.php" style="margin:0;">
                            <button type="submit" class="dropdown-item logout-btn" style="width:100%;text-align:left;border:none;background:none;cursor:pointer;">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="safety-hero">
        <h1>Your Safety is Our Priority</h1>
        <p>Learn about our safety features and how to stay secure while using ByaHERO</p>
    </section>

    <div class="safety-container">
        <!-- Back Button -->
        <div class="back-btn-container">
            <a href="home.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>

        <!-- Safety Features Section -->
        <section class="safety-section">
            <h2 class="section-title">Safety Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h3>Share Your Ride</h3>
                    <p>Share your trip details with trusted contacts in real-time and let them know your location.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3>Emergency SOS</h3>
                    <p>One-tap emergency button to contact authorities and our 24/7 support team instantly.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h3>Driver Verification</h3>
                    <p>All drivers undergo thorough background checks and vehicle inspections for your peace of mind.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h3>Real-Time GPS Tracking</h3>
                    <p>Monitor your ride in real-time and get accurate ETAs throughout your journey.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Secure Payments</h3>
                    <p>All transactions are encrypted and secured to protect your financial information.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Community Standards</h3>
                    <p>Our platform enforces respect and professionalism between all riders and drivers.</p>
                </div>
            </div>
        </section>

        <!-- Emergency Section -->
        <section class="safety-section">
            <div class="emergency-box">
                <h2><i class="fas fa-exclamation-triangle"></i> In Case of Emergency</h2>
                <p>Need immediate help? Don't hesitate to reach out to emergency services or our support team.</p>
                <button class="emergency-btn" onclick="window.location.href='tel:911'">
                    <i class="fas fa-phone"></i> Call Emergency Services (911)
                </button>
            </div>
        </section>

        <!-- Safety Tips Section -->
        <section class="safety-section">
            <h2 class="section-title">Safety Tips for Riders</h2>
            <div class="tips-grid">
                <div class="tip-item">
                    <h4><i class="fas fa-user-check"></i> Verify Your Driver</h4>
                    <p>Always check the driver's name, photo, and vehicle details before getting in.</p>
                </div>
                <div class="tip-item">
                    <h4><i class="fas fa-share-nodes"></i> Share Your Trip</h4>
                    <p>Use the share ride feature to let a trusted contact know your trip details.</p>
                </div>
                <div class="tip-item">
                    <h4><i class="fas fa-eye"></i> Stay Aware</h4>
                    <p>Keep your phone charged and stay alert during your ride. Know your route.</p>
                </div>
                <div class="tip-item">
                    <h4><i class="fas fa-flag"></i> Report Concerns</h4>
                    <p>Report any uncomfortable behavior or safety concerns immediately to our support team.</p>
                </div>
                <div class="tip-item">
                    <h4><i class="fas fa-star"></i> Rate Your Experience</h4>
                    <p>After your trip, rate your driver to help us maintain high safety and service standards.</p>
                </div>
                <div class="tip-item">
                    <h4><i class="fas fa-mobile-screen"></i> Use In-App Features</h4>
                    <p>Always communicate through the app instead of sharing personal contact information.</p>
                </div>
            </div>
        </section>

        <!-- Report Section -->
        <section class="safety-section">
            <h2 class="section-title">Report an Incident</h2>
            <div class="report-box">
                <h2 style="margin-top: 0;">File a Safety Report</h2>
                <form action="report_incident.php" method="post">
                    <div class="form-group">
                        <label for="incident-type"><i class="fas fa-exclamation"></i> Incident Type</label>
                        <select id="incident-type" name="incident_type" required>
                            <option value="">Select an incident type</option>
                            <option value="harassment">Harassment or Rudeness</option>
                            <option value="accident">Accident or Vehicle Issue</option>
                            <option value="unsafe-driving">Unsafe Driving</option>
                            <option value="lost-item">Lost Item</option>
                            <option value="other">Other Safety Concern</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description"><i class="fas fa-pen"></i> Detailed Description</label>
                        <textarea id="description" name="description" placeholder="Please provide details about the incident..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="contact"><i class="fas fa-envelope"></i> Your Contact Information</label>
                        <input type="email" id="contact" name="contact" placeholder="your.email@example.com" required>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Submit Report
                    </button>
                </form>
            </div>
        </section>
    </div>

    <footer class="home-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>ByaHERO</h4>
                <p>Your trusted ride-hailing partner in the Philippines.</p>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="safety.php">Safety Guidelines</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Legal</h4>
                <ul>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Community Guidelines</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 ByaHERO. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const profileToggle = document.getElementById('profileToggle');
        const profileDropdown = document.getElementById('profileDropdown');

        if (profileToggle && profileDropdown) {
            profileToggle.addEventListener('click', function() {
                const isExpanded = profileToggle.getAttribute('aria-expanded') === 'true';
                profileToggle.setAttribute('aria-expanded', !isExpanded);
                profileDropdown.setAttribute('aria-hidden', isExpanded);
            });

            document.addEventListener('click', function(event) {
                if (!profileToggle.contains(event.target) && !profileDropdown.contains(event.target)) {
                    profileToggle.setAttribute('aria-expanded', 'false');
                    profileDropdown.setAttribute('aria-hidden', 'true');
                }
            });
        }
    </script>
</body>
</html>