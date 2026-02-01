<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'apartment_rental_system');
$owner_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <style>
        /* Use same CSS as owner_dashboard.php */
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        .card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h1 { color: #2c3e50; margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; }
    </style>
</head>
<body>
    <?php include 'owner_header.php'; ?>
    
    <div class="container">
        <h1>📈 Reports</h1>
        
        <div class="card">
            <h2>Financial Summary</h2>
            <?php
            // Get financial data
            $revenue = $conn->query("
                SELECT COALESCE(SUM(p.amount), 0) as total
                FROM payments p
                JOIN leases l ON p.lease_id = l.lease_id
                JOIN apartments a ON l.apartment_id = a.apartment_id
                JOIN buildings b ON a.building_id = b.building_id
                WHERE b.owner_id = $owner_id
            ")->fetch_assoc()['total'];
            
            $pending = $conn->query("
                SELECT COUNT(*) as count FROM maintenance_requests mr
                JOIN apartments a ON mr.apartment_id = a.apartment_id
                JOIN buildings b ON a.building_id = b.building_id
                WHERE b.owner_id = $owner_id AND mr.status = 'pending'
            ")->fetch_assoc()['count'];
            ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div style="padding: 20px; background: #e3f2fd; border-radius: 8px;">
                    <h3>Total Revenue</h3>
                    <h1>৳<?php echo number_format($revenue, 0); ?></h1>
                </div>
                <div style="padding: 20px; background: #f3e5f5; border-radius: 8px;">
                    <h3>Pending Maintenance</h3>
                    <h1><?php echo $pending; ?></h1>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>Export Reports</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <a href="#" style="padding: 20px; background: #e8f5e9; border-radius: 8px; text-align: center; text-decoration: none; color: #2e7d32;">
                    <div style="font-size: 32px;">📊</div>
                    <div>Financial Report</div>
                </a>
                <a href="#" style="padding: 20px; background: #fff3e0; border-radius: 8px; text-align: center; text-decoration: none; color: #ef6c00;">
                    <div style="font-size: 32px;">🏢</div>
                    <div>Building Report</div>
                </a>
                <a href="#" style="padding: 20px; background: #fce4ec; border-radius: 8px; text-align: center; text-decoration: none; color: #c2185b;">
                    <div style="font-size: 32px;">👤</div>
                    <div>Tenant Report</div>
                </a>
            </div>
        </div>
    </div>
    
    <?php include 'owner_footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>