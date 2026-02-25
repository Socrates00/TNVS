<<<<<<< HEAD
<?php
session_start();
// Security check para admin lang
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<main class="container">
    <div class="header-flex">
        <h1 class="dashboard-title">Payment Management</h1>
        <div class="system-badge">SYSTEM LIVE</div>
    </div>

    <!-- Revenue Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Today's Revenue</span>
            <span class="stat-value">₱24,500</span>
            <span class="stat-trend trend-up">↑ 12% vs yesterday</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Transactions</span>
            <span class="stat-value">189</span>
            <span class="stat-sub">156 Completed</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Payments</span>
            <span class="stat-value">3</span>
            <span class="stat-trend trend-new">Requires review</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Refund Requests</span>
            <span class="stat-value">2</span>
            <span class="stat-sub">Processing</span>
        </div>
    </div>

    <!-- Payment Method Distribution -->
    <div class="main-content-layout">
        <div class="content-card">
            <div class="card-header">
                <h3>Transaction Ledger</h3>
                <div class="table-controls">
                    <select class="btn-secondary" style="padding: 8px 15px;">
                        <option>All Methods</option>
                        <option>GCash</option>
                        <option>Cash</option>
                        <option>GrabPay</option>
                        <option>Card</option>
                    </select>
                    <button class="btn-secondary" style="padding: 8px 15px;">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
            </div>

            <table class="fleet-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>TRANSACTION ID</th>
                        <th>PASSENGER</th>
                        <th>DRIVER</th>
                        <th>METHOD</th>
                        <th>AMOUNT</th>
                        <th>STATUS</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2023-12-08 09:15</td>
                        <td><strong>TXN-90822</strong></td>
                        <td>Ben Ten</td>
                        <td>Pedro Penduko</td>
                        <td><span class="method-badge badge-gcash">GCash</span></td>
                        <td><strong>₱145.00</strong></td>
                        <td><span class="status-badge status-available">COMPLETED</span></td>
                        <td><a href="#" class="btn-refund">
                            <i class="fas fa-undo"></i> Refund
                        </a></td>
                    </tr>
                    <tr>
                        <td>2023-12-08 10:20</td>
                        <td><strong>TXN-90823</strong></td>
                        <td>Liza Soberano</td>
                        <td>Maria Clara</td>
                        <td><span class="method-badge badge-cash">Cash</span></td>
                        <td><strong>₱250.00</strong></td>
                        <td><span class="status-badge status-available">COMPLETED</span></td>
                        <td><a href="#" class="btn-refund">
                            <i class="fas fa-undo"></i> Refund
                        </a></td>
                    </tr>
                    <tr>
                        <td>2023-12-08 11:45</td>
                        <td><strong>TXN-90824</strong></td>
                        <td>Dong Yan</td>
                        <td>Juan Tamad</td>
                        <td><span class="method-badge badge-grabpay">GrabPay</span></td>
                        <td><strong>₱85.50</strong></td>
                        <td><span class="status-badge status-pending">PENDING</span></td>
                        <td><a href="#" class="btn-refund">
                            <i class="fas fa-undo"></i> Refund
                        </a></td>
                    </tr>
                    <tr>
                        <td>2023-12-08 12:30</td>
                        <td><strong>TXN-90825</strong></td>
                        <td>Sarah G</td>
                        <td>Pedro Penduko</td>
                        <td><span class="method-badge badge-card">Card</span></td>
                        <td><strong>₱320.00</strong></td>
                        <td><span class="status-badge status-available">COMPLETED</span></td>
                        <td><a href="#" class="btn-refund">
                            <i class="fas fa-undo"></i> Refund
                        </a></td>
                    </tr>
                    <tr>
                        <td>2023-12-08 13:15</td>
                        <td><strong>TXN-90826</strong></td>
                        <td>Vice Ganda</td>
                        <td>Maria Clara</td>
                        <td><span class="method-badge badge-gcash">GCash</span></td>
                        <td><strong>₱175.50</strong></td>
                        <td><span class="status-badge status-available">COMPLETED</span></td>
                        <td><a href="#" class="btn-refund">
                            <i class="fas fa-undo"></i> Refund
                        </a></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="sidebar-card">
            <h3>Payment Method Split</h3>
            <div class="dist-item">
                <div class="dist-info">
                    <span>GCash</span>
                    <span>₱12,450</span>
                </div>
                <div class="progress-bar">
                    <div class="progress bg-green" style="width: 51%;"></div>
                </div>
                <small style="color: #888; font-size: 12px;">51% of total</small>
            </div>
            <div class="dist-item">
                <div class="dist-info">
                    <span>Cash</span>
                    <span>₱8,100</span>
                </div>
                <div class="progress-bar">
                    <div class="progress bg-blue" style="width: 33%;"></div>
                </div>
                <small style="color: #888; font-size: 12px;">33% of total</small>
            </div>
            <div class="dist-item">
                <div class="dist-info">
                    <span>GrabPay</span>
                    <span>₱2,450</span>
                </div>
                <div class="progress-bar">
                    <div class="progress bg-orange" style="width: 10%;"></div>
                </div>
                <small style="color: #888; font-size: 12px;">10% of total</small>
            </div>
            <div class="dist-item">
                <div class="dist-info">
                    <span>Card</span>
                    <span>₱1,500</span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: 6%; background: #8b5cf6;"></div>
                </div>
                <small style="color: #888; font-size: 12px;">6% of total</small>
            </div>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>
=======
<?php
session_start();
// Security check para admin lang
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - ByaHERO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --hero-green: #00b14f; --hero-bg: #f8fafb; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--hero-bg); padding: 20px 40px; }

        .container { max-width: 1100px; margin: 0 auto; }

        /* Back Button papunta sa Admin index.php */
        .back-btn { 
            text-decoration: none; color: #1a1a1a; font-weight: 800; 
            display: inline-flex; align-items: center; gap: 10px; 
            margin-bottom: 20px; background: white; padding: 10px 20px; 
            border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .back-btn:hover { background: var(--hero-green); color: white; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .header h1 { font-size: 28px; font-weight: 800; }
        .status-badge { background: #e8fdf0; color: var(--hero-green); padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 800; }

        /* Transaction Table Card */
        .card { background: white; border-radius: 24px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .card-title { font-size: 18px; font-weight: 800; color: #333; }

        .table-controls { display: flex; gap: 10px; }
        .btn-export { background: #f8fafb; border: 1px solid #eee; padding: 8px 15px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; }

        /* Table Styles base sa Transaction Ledger */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #999; font-size: 13px; font-weight: 600; border-bottom: 1px solid #eee; }
        td { padding: 20px 15px; border-bottom: 1px solid #f9f9f9; font-size: 14px; color: #444; }

        .tx-id { font-weight: 800; color: #1a1a1a; }
        .method-badge { background: #f0fdf4; color: #166534; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid #dcfce7; }
        .amount { font-weight: 800; color: #1a1a1a; }
        
        .status-pill { display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px; }
        .dot { width: 8px; height: 8px; border-radius: 50%; }
        .dot.completed { background: var(--hero-green); }
        .dot.pending { background: #f59e0b; }

        .btn-refund { color: #ff4757; text-decoration: none; font-weight: 800; font-size: 13px; }
    </style>
</head>
<body>

    <div class="container">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="header">
            <h1>Payment Management</h1>
            <div class="status-badge">SYSTEM LIVE</div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Transaction Ledger</div>
                <div class="table-controls">
                    <select class="btn-export">
                        <option>All Methods</option>
                        <option>GCash</option>
                        <option>Cash</option>
                    </select>
                    <button class="btn-export">Export CSV</button>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Passenger</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2023-12-08 09:15</td>
                        <td class="tx-id">TXN-90822</td>
                        <td>Ben Ten</td>
                        <td><span class="method-badge">GCash</span></td>
                        <td class="amount">₱145.00</td>
                        <td><span class="status-pill"><div class="dot completed"></div> Completed</span></td>
                        <td><a href="#" class="btn-refund">Refund</a></td>
                    </tr>
                    <tr>
                        <td>2023-12-08 10:20</td>
                        <td class="tx-id">TXN-90823</td>
                        <td>Liza Soberano</td>
                        <td><span class="method-badge">Cash</span></td>
                        <td class="amount">₱250.00</td>
                        <td><span class="status-pill"><div class="dot completed"></div> Completed</span></td>
                        <td><a href="#" class="btn-refund">Refund</a></td>
                    </tr>
                    <tr>
                        <td>2023-12-08 11:45</td>
                        <td class="tx-id">TXN-90824</td>
                        <td>Dong Yan</td>
                        <td><span class="method-badge" style="background:#eef2ff; color:#3730a3; border-color:#e0e7ff;">GrabPay</span></td>
                        <td class="amount">₱85.50</td>
                        <td><span class="status-pill" style="color:#b45309;"><div class="dot pending"></div> Pending</span></td>
                        <td><a href="#" class="btn-refund">Refund</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
>>>>>>> 749282eb7b691ce991d83ae99e804a2526595e8c
