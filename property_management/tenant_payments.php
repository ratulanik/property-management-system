<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'apartment_rental_system');
$tenant_id = $_SESSION['user_id'];

// Get tenant's payments WITH ERROR CHECKING
$payments = [];
$query = "
    SELECT p.*, a.unit_number 
    FROM payments p
    JOIN leases l ON p.lease_id = l.lease_id
    JOIN apartments a ON l.apartment_id = a.apartment_id
    WHERE l.tenant_id = $tenant_id
    ORDER BY p.payment_date DESC
";

$result = $conn->query($query);
if ($result) {
    while($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payments</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        .tenant-header { 
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); 
            color: white; 
            padding: 15px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tenant-nav { 
            background: white; 
            padding: 10px 30px; 
            border-bottom: 1px solid #e0e0e0; 
            display: flex; 
            gap: 20px; 
            flex-wrap: wrap; 
        }
        
        .tenant-nav a { 
            color: #555; 
            text-decoration: none; 
            padding: 8px 15px; 
            border-radius: 5px; 
            transition: all 0.3s;
        }
        
        .tenant-nav a:hover { 
            background: #e9f7ef; 
            color: #27ae60; 
        }
        
        .tenant-nav a.active { 
            background: #e9f7ef; 
            color: #27ae60; 
            font-weight: 600; 
        }
        
        .container { 
            padding: 30px; 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        
        .card { 
            background: white; 
            border-radius: 10px; 
            padding: 25px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            margin-bottom: 30px; 
        }
        
        h1 { 
            color: #2c3e50; 
            margin-bottom: 30px; 
        }
        
        h2 { 
            color: #2c3e50; 
            margin-bottom: 20px; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #f5f7fa; 
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th {
            padding: 15px;
            text-align: left;
            color: #555;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        tbody tr:hover {
            background: #f9f9f9;
        }
        
        .paid-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #d4edda;
            color: #155724;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .no-data-icon {
            font-size: 48px;
            margin-bottom: 10px;
            display: block;
        }
        
        .btn {
            padding: 10px 20px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #219653;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="tenant-header">
        <div style="font-size: 24px; font-weight: bold;">🏠 Tenant Portal</div>
        <div>
            <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
            <a href="logout.php" style="background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; margin-left: 15px;">Logout</a>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="tenant-nav">
        <a href="tenant_dashboard.php">📊 Dashboard</a>
        <a href="tenant_apartment.php">🏠 My Apartment</a>
        <a href="tenant_payments.php" class="active">💰 My Payments</a>
        <a href="tenant_maintenance.php">🔧 Maintenance</a>
        <a href="tenant_profile.php">👤 My Profile</a>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <h1>💰 My Payments</h1>
        
        <div class="card">
            <h2>Payment History</h2>
            
            <?php if(!empty($payments)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Month</th>
                        <th>Unit</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $payment): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                        <td><?php echo $payment['payment_month']; ?></td>
                        <td><?php echo $payment['unit_number']; ?></td>
                        <td><strong>৳<?php echo number_format($payment['amount'], 0); ?></strong></td>
                        <td><?php echo ucfirst($payment['method']); ?></td>
                        <td><span class="paid-badge">Paid</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">
                <span class="no-data-icon">💰</span>
                <h3>No payment records found</h3>
                <p>Your payment history will appear here when you make payments</p>
                <p style="margin-top: 20px; font-size: 14px; color: #999;">
                    Contact your building owner for payment information
                </p>
            </div>
            <?php endif; ?>
            
            <!-- Make Payment Button (if needed) -->
            <div style="margin-top: 20px; text-align: center;">
                <a href="tenant_dashboard.php" class="btn">Back to Dashboard</a>
            </div>
        </div>
        
        <!-- Payment Summary -->
        <div class="card">
            <h2>Payment Summary</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 32px; color: #27ae60;">💰</div>
                    <div style="font-size: 36px; font-weight: bold;">
                        <?php 
                        $total = 0;
                        foreach($payments as $p) {
                            $total += $p['amount'];
                        }
                        echo '৳' . number_format($total, 0);
                        ?>
                    </div>
                    <div>Total Paid</div>
                </div>
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 32px; color: #667eea;">📅</div>
                    <div style="font-size: 36px; font-weight: bold;"><?php echo count($payments); ?></div>
                    <div>Payments Made</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div style="text-align: center; margin-top: 50px; padding: 20px; color: #666; border-top: 1px solid #eee;">
        <p>Tenant Portal © <?php echo date('Y'); ?></p>
        <p style="font-size: 12px; margin-top: 5px;">
            <a href="tenant_dashboard.php" style="color: #27ae60; text-decoration: none;">Dashboard</a> | 
            <a href="index.php" style="color: #27ae60; text-decoration: none;">Home</a> | 
            <a href="logout.php" style="color: #27ae60; text-decoration: none;">Logout</a>
        </p>
    </div>
</body>
</html>
<?php $conn->close(); ?>