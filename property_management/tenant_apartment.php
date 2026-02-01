<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'apartment_rental_system');
$tenant_id = $_SESSION['user_id'];

// Get tenant's current apartment WITH ERROR CHECKING
$apartment = null;
$query = "
    SELECT a.*, b.building_name, b.address, l.start_date, l.end_date, l.monthly_rent
    FROM leases l
    JOIN apartments a ON l.apartment_id = a.apartment_id
    JOIN buildings b ON a.building_id = b.building_id
    WHERE l.tenant_id = $tenant_id AND l.status = 'active'
    ORDER BY l.start_date DESC LIMIT 1
";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $apartment = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Apartment</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; margin: 0; }
        .tenant-header { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .tenant-nav { background: white; padding: 10px 30px; border-bottom: 1px solid #e0e0e0; display: flex; gap: 20px; flex-wrap: wrap; }
        .tenant-nav a { color: #555; text-decoration: none; padding: 8px 15px; border-radius: 5px; }
        .tenant-nav a:hover { background: #e9f7ef; color: #27ae60; }
        .tenant-nav a.active { background: #e9f7ef; color: #27ae60; font-weight: 600; }
        .container { padding: 30px; max-width: 800px; margin: 0 auto; }
        .card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .info-row { display: flex; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .info-label { font-weight: 600; width: 200px; color: #555; }
        .info-value { flex: 1; }
        .btn { padding: 10px 20px; background: #27ae60; color: white; border: none; border-radius: 5px; text-decoration: none; display: inline-block; }
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
        <a href="tenant_apartment.php" class="active">🏠 My Apartment</a>
        <a href="tenant_payments.php">💰 My Payments</a>
        <a href="tenant_maintenance.php">🔧 Maintenance</a>
        <a href="tenant_profile.php">👤 My Profile</a>
    </div>
    
    <!-- Content -->
    <div class="container">
        <h1>🏠 My Apartment</h1>
        
        <?php if($apartment): ?>
        <div class="card">
            <h2>Apartment Details</h2>
            <div class="info-row">
                <div class="info-label">Building:</div>
                <div class="info-value"><?php echo $apartment['building_name']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Unit Number:</div>
                <div class="info-value"><?php echo $apartment['unit_number']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Floor:</div>
                <div class="info-value"><?php echo $apartment['floor']; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Type:</div>
                <div class="info-value"><?php echo $apartment['bedrooms']; ?> Bedrooms, <?php echo $apartment['bathrooms']; ?> Bathrooms</div>
            </div>
            <div class="info-row">
                <div class="info-label">Monthly Rent:</div>
                <div class="info-value"><strong>৳<?php echo number_format($apartment['monthly_rent'], 0); ?></strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Lease Period:</div>
                <div class="info-value">
                    <?php echo date('d M Y', strtotime($apartment['start_date'])); ?> to 
                    <?php echo date('d M Y', strtotime($apartment['end_date'])); ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value"><?php echo $apartment['address']; ?></div>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div style="text-align: center; padding: 40px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🏠</div>
                <h2>No Apartment Assigned</h2>
                <p>You don't have an active apartment lease</p>
                <p>Contact your building owner for apartment assignment</p>
                <a href="tenant_dashboard.php" class="btn" style="margin-top: 20px;">Back to Dashboard</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>