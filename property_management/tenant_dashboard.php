<?php
// tenant_dashboard.php
session_start();

// Check if tenant is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    header("Location: index.php?error=Please login as tenant first");
    exit();
}

$tenant_id = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'property_management');

// Get tenant info
$tenant = $conn->query("SELECT * FROM tenants WHERE tenant_id = $tenant_id")->fetch_assoc();

// Get current lease
$lease = $conn->query("
    SELECT l.*, a.unit_number, a.floor, a.bedrooms, a.bathrooms, a.rent,
           b.building_name, b.address 
    FROM leases l
    JOIN apartments a ON l.apartment_id = a.apartment_id
    JOIN buildings b ON a.building_id = b.building_id
    WHERE l.tenant_id = $tenant_id AND l.status = 'active'
    ORDER BY l.start_date DESC LIMIT 1
")->fetch_assoc();

// Get recent payments
$payments = $conn->query("
    SELECT p.* FROM payments p
    JOIN leases l ON p.lease_id = l.lease_id
    WHERE l.tenant_id = $tenant_id
    ORDER BY p.payment_date DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .tenant-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .logo { font-size: 24px; font-weight: bold; }
        .user-info span { margin-right: 15px; }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
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
        .tenant-nav a:hover { background: #e9f7ef; color: #27ae60; }
        .tenant-nav a.active { background: #e9f7ef; color: #27ae60; font-weight: 600; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        .card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: #27ae60; color: white; }
        .info-row { display: flex; margin-bottom: 15px; }
        .info-label { font-weight: 600; width: 150px; color: #555; }
        .info-value { flex: 1; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="tenant-header">
        <div class="logo">🏠 Tenant Portal</div>
        <div class="user-info">
            <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="tenant-nav">
        <a href="tenant_dashboard.php" class="active">📊 Dashboard</a>
        <a href="tenant_apartment.php">🏠 My Apartment</a>
        <a href="tenant_payments.php">💰 My Payments</a>
        <a href="tenant_maintenance.php">🔧 Maintenance</a>
        <a href="tenant_profile.php">👤 My Profile</a>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <h1>Welcome, <?php echo $_SESSION['full_name']; ?>! 👋</h1>
        <p style="color: #666; margin-bottom: 30px;">Manage your apartment details and payments</p>
        
        <?php if($lease): ?>
        <!-- Current Lease Info -->
        <div class="card">
            <h2>My Current Apartment</h2>
            <div class="info-row">
                <div class="info-label">Building:</div>
                <div class="info-value"><?php echo $lease['building_name']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Unit:</div>
                <div class="info-value"><?php echo $lease['unit_number']; ?> (Floor: <?php echo $lease['floor']; ?>)</div>
            </div>
            <div class="info-row">
                <div class="info-label">Monthly Rent:</div>
                <div class="info-value"><strong>৳<?php echo number_format($lease['monthly_rent'], 0); ?></strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Lease Period:</div>
                <div class="info-value">
                    <?php echo date('d M Y', strtotime($lease['start_date'])); ?> to 
                    <?php echo date('d M Y', strtotime($lease['end_date'])); ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value"><?php echo $lease['address']; ?></div>
            </div>
        </div>
        
        <!-- Recent Payments -->
        <div class="card">
            <h2>Recent Payments</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Month</th>
                        <th>Amount</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($payments->num_rows > 0): ?>
                        <?php while($payment = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                            <td><?php echo $payment['payment_month']; ?></td>
                            <td><strong>৳<?php echo number_format($payment['amount'], 0); ?></strong></td>
                            <td><?php echo ucfirst($payment['method']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px; color: #666;">
                                No payment records found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="tenant_payments.php" class="btn btn-primary" style="margin-top: 15px;">View All Payments</a>
        </div>
        <?php else: ?>
        <div class="card">
            <div style="text-align: center; padding: 40px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🏠</div>
                <h2>No Active Lease Found</h2>
                <p style="color: #666; margin-bottom: 20px;">You don't have an active apartment lease</p>
                <p>Please contact your building owner for apartment assignment</p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div class="card">
            <h2>Quick Actions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <a href="tenant_payments.php" style="display: block; padding: 20px; background: #e9f7ef; border-radius: 8px; text-decoration: none; color: #27ae60; text-align: center;">
                    <div style="font-size: 32px; margin-bottom: 10px;">💰</div>
                    <div style="font-weight: 600;">Make Payment</div>
                </a>
                <a href="tenant_maintenance.php" style="display: block; padding: 20px; background: #fff3cd; border-radius: 8px; text-decoration: none; color: #856404; text-align: center;">
                    <div style="font-size: 32px; margin-bottom: 10px;">🔧</div>
                    <div style="font-weight: 600;">Request Maintenance</div>
                </a>
                <a href="tenant_profile.php" style="display: block; padding: 20px; background: #d1ecf1; border-radius: 8px; text-decoration: none; color: #0c5460; text-align: center;">
                    <div style="font-size: 32px; margin-bottom: 10px;">👤</div>
                    <div style="font-weight: 600;">Update Profile</div>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div style="text-align: center; margin-top: 50px; padding: 20px; color: #666; border-top: 1px solid #eee;">
        <p>Tenant Portal © <?php echo date('Y'); ?></p>
    </div>
</body>
</html>
<?php $conn->close(); ?>