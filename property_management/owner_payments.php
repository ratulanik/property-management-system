<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'property_management');
$owner_id = $_SESSION['user_id'];

// Get payments
$query = "
    SELECT p.*, t.full_name, a.unit_number, b.building_name, l.monthly_rent
    FROM payments p
    JOIN leases l ON p.lease_id = l.lease_id
    JOIN tenants t ON l.tenant_id = t.tenant_id
    JOIN apartments a ON l.apartment_id = a.apartment_id
    JOIN buildings b ON a.building_id = b.building_id
    WHERE b.owner_id = $owner_id
    ORDER BY p.payment_date DESC
";

$payments = $conn->query($query);

// Get statistics
$total_revenue = $conn->query("
    SELECT COALESCE(SUM(p.amount), 0) as total
    FROM payments p
    JOIN leases l ON p.lease_id = l.lease_id
    JOIN apartments a ON l.apartment_id = a.apartment_id
    JOIN buildings b ON a.building_id = b.building_id
    WHERE b.owner_id = $owner_id
")->fetch_assoc()['total'];

$this_month = date('Y-m');
$this_month_revenue = $conn->query("
    SELECT COALESCE(SUM(p.amount), 0) as total
    FROM payments p
    JOIN leases l ON p.lease_id = l.lease_id
    JOIN apartments a ON l.apartment_id = a.apartment_id
    JOIN buildings b ON a.building_id = b.building_id
    WHERE b.owner_id = $owner_id AND DATE_FORMAT(p.payment_date, '%Y-%m') = '$this_month'
")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payments Management</title>
    <style>
        .payment-status { padding: 5px 10px; border-radius: 20px; font-size: 12px; }
        .paid { background: #d4edda; color: #155724; }
        .pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <?php include 'owner_header.php'; ?>
    
    <h2>💰 Rent Payments</h2>
    
    <!-- Statistics -->
    <div class="card">
        <h3>Payment Statistics</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div style="text-align: center; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px;">
                <div style="font-size: 32px;">💰</div>
                <div style="font-size: 36px; font-weight: bold;">৳<?php echo number_format($total_revenue, 0); ?></div>
                <div>Total Revenue</div>
            </div>
            <div style="text-align: center; padding: 20px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border-radius: 8px;">
                <div style="font-size: 32px;">📅</div>
                <div style="font-size: 36px; font-weight: bold;">৳<?php echo number_format($this_month_revenue, 0); ?></div>
                <div>This Month</div>
            </div>
            <div style="text-align: center; padding: 20px; background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: #212529; border-radius: 8px;">
                <div style="font-size: 32px;">📊</div>
                <div style="font-size: 36px; font-weight: bold;"><?php echo $payments->num_rows; ?></div>
                <div>Total Payments</div>
            </div>
        </div>
    </div>
    
    <!-- Payments List -->
    <div class="card">
        <h3>Payment History</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Tenant</th>
                    <th>Building</th>
                    <th>Unit</th>
                    <th>Month</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($payments->num_rows > 0): ?>
                    <?php while($payment = $payments->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                        <td><?php echo $payment['full_name']; ?></td>
                        <td><?php echo $payment['building_name']; ?></td>
                        <td><?php echo $payment['unit_number']; ?></td>
                        <td><?php echo $payment['payment_month']; ?></td>
                        <td><strong>৳<?php echo number_format($payment['amount'], 0); ?></strong></td>
                        <td><?php echo ucfirst($payment['method']); ?></td>
                        <td>
                            <span class="payment-status paid">Paid</span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <div style="font-size: 48px;">💰</div>
                            <h3>No payment records found</h3>
                            <p>Payment records will appear when tenants pay rent</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Monthly Breakdown -->
    <div class="card">
        <h3>Monthly Revenue Breakdown</h3>
        <?php
        $monthly_data = $conn->query("
            SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, 
                   SUM(amount) as total,
                   COUNT(*) as count
            FROM payments p
            JOIN leases l ON p.lease_id = l.lease_id
            JOIN apartments a ON l.apartment_id = a.apartment_id
            JOIN buildings b ON a.building_id = b.building_id
            WHERE b.owner_id = $owner_id
            GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
            ORDER BY month DESC
            LIMIT 6
        ");
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Total Amount</th>
                    <th>Payments</th>
                    <th>Avg. Payment</th>
                </tr>
            </thead>
            <tbody>
                <?php while($month = $monthly_data->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo date('F Y', strtotime($month['month'].'-01')); ?></strong></td>
                    <td>৳<?php echo number_format($month['total'], 0); ?></td>
                    <td><?php echo $month['count']; ?> payments</td>
                    <td>৳<?php echo number_format($month['total']/$month['count'], 0); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <?php include 'owner_footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>