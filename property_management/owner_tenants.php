<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'property_management');
$owner_id = $_SESSION['user_id'];

// Get all tenants (who have leases in owner's apartments)
$tenants = $conn->query("
    SELECT DISTINCT t.*, a.unit_number, b.building_name, l.start_date, l.end_date
    FROM tenants t
    JOIN leases l ON t.tenant_id = l.tenant_id
    JOIN apartments a ON l.apartment_id = a.apartment_id
    JOIN buildings b ON a.building_id = b.building_id
    WHERE b.owner_id = $owner_id AND l.status = 'active'
    ORDER BY t.full_name
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tenants Management</title>
</head>
<body>
    <?php include 'owner_header.php'; ?>
    
    <h2>👤 My Tenants</h2>
    
    <div class="card">
        <h3>Active Tenants List</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Apartment</th>
                    <th>Building</th>
                    <th>Lease Period</th>
                    <th>Move In</th>
                </tr>
            </thead>
            <tbody>
                <?php if($tenants->num_rows > 0): ?>
                    <?php while($tenant = $tenants->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $tenant['tenant_id']; ?></td>
                        <td><strong><?php echo $tenant['full_name']; ?></strong></td>
                        <td><?php echo $tenant['email']; ?></td>
                        <td><?php echo $tenant['phone']; ?></td>
                        <td><?php echo $tenant['unit_number']; ?></td>
                        <td><?php echo $tenant['building_name']; ?></td>
                        <td>
                            <?php echo date('M Y', strtotime($tenant['start_date'])); ?> - 
                            <?php echo date('M Y', strtotime($tenant['end_date'])); ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($tenant['move_in_date'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <div style="font-size: 48px;">👤</div>
                            <h3>No active tenants found</h3>
                            <p>When you rent apartments, tenants will appear here</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="card">
        <h3>Tenant Statistics</h3>
        <?php
        // Get statistics
        $total_tenants = $tenants->num_rows;
        $result = $conn->query("
            SELECT COUNT(DISTINCT t.tenant_id) as count
            FROM tenants t
            JOIN leases l ON t.tenant_id = l.tenant_id
            JOIN apartments a ON l.apartment_id = a.apartment_id
            JOIN buildings b ON a.building_id = b.building_id
            WHERE b.owner_id = $owner_id
        ");
        $all_time_tenants = $result->fetch_assoc()['count'];
        ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 32px; color: #667eea;">👥</div>
                <div style="font-size: 36px; font-weight: bold;"><?php echo $total_tenants; ?></div>
                <div>Active Tenants</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 32px; color: #28a745;">📊</div>
                <div style="font-size: 36px; font-weight: bold;"><?php echo $all_time_tenants; ?></div>
                <div>All Time Tenants</div>
            </div>
        </div>
    </div>
    
    <?php include 'owner_footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>