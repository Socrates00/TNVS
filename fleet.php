<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ByaHERO - Fleet Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --hero-green: #00b14f;
            --hero-blue: #3b5998;
            --hero-black: #1a1a1a;
            --bg-light: #f8fafb;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg-light); margin: 0; padding-bottom: 30px; }

        /* Header matching your home.php */
        .fleet-header {
            background: white; padding: 20px; display: flex; align-items: center; gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .back-btn { text-decoration: none; color: black; font-size: 20px; }

        .main-content { padding: 20px; max-width: 500px; margin: 0 auto; }

        .section-title { font-size: 22px; font-weight: 900; margin-bottom: 5px; color: var(--hero-black); }
        .section-subtitle { font-size: 14px; color: #666; margin-bottom: 25px; }

        /* Service Cards Style */
        .fleet-card {
            background: white; border-radius: 20px; padding: 20px; margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05); border: 1px solid #eee;
            display: flex; align-items: center; gap: 20px; transition: 0.3s;
            cursor: pointer;
        }
        .fleet-card:hover { transform: translateY(-5px); border-color: var(--hero-green); }

        .fleet-icon {
            width: 70px; height: 70px; border-radius: 15px;
            display: flex; align-items: center; justify-content: center; font-size: 30px;
        }
        .icon-blue { background: #eef2ff; color: #3b5998; }
        .icon-green { background: #e8fdf0; color: var(--hero-green); }
        .icon-orange { background: #fff7ed; color: #f97316; }

        .fleet-info h4 { margin: 0 0 5px 0; font-size: 17px; font-weight: 700; }
        .fleet-info p { margin: 0; font-size: 13px; color: #777; line-height: 1.4; }

        /* Business Banner */
        .business-banner {
            background: linear-gradient(135deg, #1a1a1a, #333); color: white;
            padding: 25px; border-radius: 24px; margin-top: 10px; position: relative;
            overflow: hidden;
        }
        .business-banner h3 { margin: 0 0 10px 0; font-size: 20px; }
        .business-banner p { margin: 0; font-size: 13px; opacity: 0.8; margin-bottom: 15px; }
        .btn-apply {
            background: var(--hero-green); color: white; border: none;
            padding: 10px 20px; border-radius: 10px; font-weight: 700; cursor: pointer;
        }
    </style>
</head>
<body>

    <header class="fleet-header">
        <a href="home.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
        <span style="font-weight: 800; font-size: 18px;">ByaHERO Fleet</span>
    </header>

    <div class="main-content">
        <h2 class="section-title">Big Moves?</h2>
        <p class="section-subtitle">Book larger vehicles for your heavy deliveries.</p>

        <div class="fleet-card">
            <div class="fleet-icon icon-blue"><i class="fas fa-truck-ramp-box"></i></div>
            <div class="fleet-info">
                <h4>FB Body / L300</h4>
                <p>Perfect for lipat-bahay or bulk item deliveries up to 1000kg.</p>
            </div>
        </div>

        <div class="fleet-card">
            <div class="fleet-icon icon-green"><i class="fas fa-truck-moving"></i></div>
            <div class="fleet-info">
                <h4>Closed Van (4-Wheeler)</h4>
                <p>Secured transport for large furniture and business supplies.</p>
            </div>
        </div>

        <div class="fleet-card">
            <div class="fleet-icon icon-orange"><i class="fas fa-car-side"></i></div>
            <div class="fleet-info">
                <h4>High-Capacity MPV</h4>
                <p>Extra space for large parcels that won't fit in a regular car.</p>
            </div>
        </div>

        <div class="business-banner">
            <h3>ByaHERO for Business</h3>
            <p>Get exclusive rates and dedicated fleet management for your company.</p>
            <button class="btn-apply" onclick="alert('Our team will contact you for business rates!')">Contact Sales</button>
        </div>
    </div>

</body>
</html>