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