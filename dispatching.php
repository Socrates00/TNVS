<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taxi Dispatching - ByaHERO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --hero-green: #00b14f; 
            --hero-bg: #f8fafb;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--hero-bg); padding: 30px; }

        .container { max-width: 1000px; margin: 0 auto; }

        /* Navigation Header */
        .top-nav { margin-bottom: 20px; }
        .back-btn { text-decoration: none; color: #666; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; }
        .back-btn:hover { color: var(--hero-green); }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 32px; font-weight: 800; color: #1a1a1a; }
        .system-status { background: #e8fdf0; color: var(--hero-green); padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 800; }

        /* Card Layouts */
        .card { background: white; border-radius: 24px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 25px; border: 1px solid #f0f0f0; }
        .card h3 { font-size: 18px; margin-bottom: 20px; color: #333; font-weight: 700; }

        /* Live Ride Requests Section */
        .request-box { border: 1.5px solid #f2f2f2; border-radius: 20px; padding: 25px; margin-top: 10px; }
        .req-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .req-id { font-size: 12px; color: #aaa; font-weight: 700; }
        .status-pending { background: #fffbeb; color: #b45309; padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 800; }
        
        .customer-info h4 { font-size: 22px; font-weight: 800; color: #1a1a1a; }
        .route-info { color: #666; font-size: 15px; margin-top: 5px; }

        .action-btns { display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-top: 20px; }
        .btn-assign { background: var(--hero-green); color: white; border: none; padding: 15px; border-radius: 12px; font-weight: 800; font-size: 15px; cursor: pointer; }
        .btn-reject { background: white; color: #ff4757; border: 1.5px solid #ff4757; padding: 15px; border-radius: 12px; font-weight: 800; font-size: 15px; cursor: pointer; }

        /* Ongoing Trips Table */
        .trips-table { width: 100%; border-collapse: collapse; }
        .trips-table th { text-align: left; padding: 15px 10px; color: #999; font-size: 13px; font-weight: 600; border-bottom: 1px solid #eee; }
        .trips-table td { padding: 20px 10px; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        
        .driver-cell { font-weight: 700; color: #1a1a1a; }
        .progress-bar { height: 6px; width: 100px; background: #eee; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--hero-green); width: 65%; border-radius: 10px; }
        .track-btn { color: var(--hero-green); text-decoration: none; font-weight: 800; font-size: 13px; }
    </style>
</head>
<body>

    <div class="container">
        <div class="top-nav">
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Admin panel</a>
        </div>

        <div class="header">
            <h1>Taxi Dispatching</h1>
            <div class="system-status">SYSTEM LIVE</div>
        </div>

        <div class="card">
            <h3>Live Ride Requests</h3>
            <div class="request-box">
                <div class="req-header">
                    <div class="customer-info">
                        <span class="req-id">REQ#772</span>
                        <h4>Maria Santos</h4>
                        <div class="route-info">SM North &rarr; Makati CBD</div>
                    </div>
                    <span class="status-pending">PENDING</span>
                </div>
                <div class="action-btns">
                    <button class="btn-assign">AUTO ASSIGN</button>
                    <button class="btn-reject">REJECT</button>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Ongoing Trips Tracking</h3>
            <table class="trips-table">
                <thead>
                    <tr>
                        <th>Trip ID</th>
                        <th>Driver</th>
                        <th>Destination</th>
                        <th>Progress</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="color: #888;">TRP-902</td>
                        <td class="driver-cell">Pedro Penduko</td>
                        <td>Quezon City</td>
                        <td>
                            <div class="progress-bar"><div class="progress-fill"></div></div>
                        </td>
                        <td><a href="#" class="track-btn">Track Live</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>