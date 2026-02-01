<?php
// owner_dashboard.php
session_start();

// Check if owner is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php?error=Please login as owner first");
    exit();
}

$owner_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: calc(100vh - 80px);
        }
        
        /* Sidebar */
        .sidebar {
            background: white;
            border-right: 1px solid #e0e0e0;
            padding: 20px 0;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: #555;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover {
            background: #f8f9fa;
            color: #667eea;
            border-left: 3px solid #667eea;
        }
        
        .sidebar-menu a.active {
            background: #f0f3ff;
            color: #667eea;
            border-left: 3px solid #667eea;
            font-weight: 600;
        }
        
        /* Main Content */
        .main-content {
            padding: 30px;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-top: 4px solid #667eea;
        }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        /* Tables */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .table-container h3 {
            margin-bottom: 15px;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 2px solid #f5f7fa;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            color: #555;
            font-weight: 600;
            border-bottom: 2px solid #e0e0e0;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-available {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rented {
            background: #fff3cd;
            color: #856404;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        /* Welcome Message */
        .welcome-message {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        
        .welcome-message h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .welcome-message p {
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">🏢 Owner Dashboard</div>
        <div class="user-info">
            <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
            <span><?php echo $_SESSION['email']; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <!-- Dashboard Layout -->
    <div class="dashboard-grid">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="#" class="active">📊 Dashboard</a></li>
                <li><a href="owner_buildings.php">🏢 My Buildings</a></li>
                <li><a href="owner_apartments.php">🏠 Apartments</a></li>
                <li><a href="owner_tenants.php">👤 Tenants</a></li>
                <li><a href="owner_payments.php">💰 Rent Payments</a></li>
                <li><a href="owner_maintenance.php">🔧 Maintenance</a></li>
                <li><a href="owner_profile.php">👤 Profile</a></li>
                <li><a href="owner_reports.php">📈 Reports</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Message -->
            <div class="welcome-message">
                <h2>Hello, <?php echo $_SESSION['full_name']; ?>! 👋</h2>
                <p>Welcome to your apartment management dashboard. Here's an overview of your properties.</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <?php
                // Database connection
                $conn = new mysqli('localhost', 'root', '', 'property_management');
                
                // Get statistics
                // Total Buildings
                $result = $conn->query("SELECT COUNT(*) as count FROM buildings WHERE owner_id = $owner_id");
                $total_buildings = $result->fetch_assoc()['count'];
                
                // Total Apartments
                $result = $conn->query("
                    SELECT COUNT(*) as count FROM apartments a 
                    JOIN buildings b ON a.building_id = b.building_id 
                    WHERE b.owner_id = $owner_id
                ");
                $total_apartments = $result->fetch_assoc()['count'];
                
                // Available Apartments
                $result = $conn->query("
                    SELECT COUNT(*) as count FROM apartments a 
                    JOIN buildings b ON a.building_id = b.building_id 
                    WHERE b.owner_id = $owner_id AND a.status = 'available'
                ");
                $available_apartments = $result->fetch_assoc()['count'];
                
                // Total Tenants
                $result = $conn->query("
                    SELECT COUNT(DISTINCT l.tenant_id) as count 
                    FROM leases l
                    JOIN apartments a ON l.apartment_id = a.apartment_id
                    JOIN buildings b ON a.building_id = b.building_id
                    WHERE b.owner_id = $owner_id AND l.status = 'active'
                ");
                $total_tenants = $result->fetch_assoc()['count'];
                
                // Monthly Revenue
                $result = $conn->query("
                    SELECT COALESCE(SUM(l.monthly_rent), 0) as revenue
                    FROM leases l
                    JOIN apartments a ON l.apartment_id = a.apartment_id
                    JOIN buildings b ON a.building_id = b.building_id
                    WHERE b.owner_id = $owner_id AND l.status = 'active'
                ");
                $monthly_revenue = $result->fetch_assoc()['revenue'];
                ?>
                
                <div class="stat-card">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-number"><?php echo $total_buildings; ?></div>
                    <div class="stat-label">Total Buildings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-number"><?php echo $total_apartments; ?></div>
                    <div class="stat-label">Total Apartments</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?php echo $available_apartments; ?></div>
                    <div class="stat-label">Available Apartments</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $total_tenants; ?></div>
                    <div class="stat-label">Active Tenants</div>
                </div>
            </div>
            
            <!-- Recent Apartments -->
            <div class="table-container">
                <h3>My Apartments</h3>
                <?php
                $result = $conn->query("
                    SELECT a.*, b.building_name 
                    FROM apartments a 
                    JOIN buildings b ON a.building_id = b.building_id 
                    WHERE b.owner_id = $owner_id 
                    ORDER BY a.created_at DESC 
                    LIMIT 5
                ");
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Unit No</th>
                            <th>Building</th>
                            <th>Floor</th>
                            <th>Bedrooms</th>
                            <th>Rent</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['unit_number']; ?></td>
                            <td><?php echo $row['building_name']; ?></td>
                            <td><?php echo $row['floor']; ?></td>
                            <td><?php echo $row['bedrooms']; ?></td>
                            <td>৳<?php echo number_format($row['rent'], 0); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $row['status']; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div style="margin-top: 15px;">
                    <a href="owner_apartments.php" class="btn btn-primary">View All Apartments</a>
                </div>
            </div>
            
            <!-- Recent Payments -->
            <div class="table-container">
                <h3>Recent Rent Payments</h3>
                <?php
                $result = $conn->query("
                    SELECT p.*, t.full_name, a.unit_number 
                    FROM payments p
                    JOIN leases l ON p.lease_id = l.lease_id
                    JOIN tenants t ON l.tenant_id = t.tenant_id
                    JOIN apartments a ON l.apartment_id = a.apartment_id
                    JOIN buildings b ON a.building_id = b.building_id
                    WHERE b.owner_id = $owner_id 
                    ORDER BY p.payment_date DESC 
                    LIMIT 5
                ");
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tenant</th>
                            <th>Unit</th>
                            <th>Amount</th>
                            <th>Month</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['payment_date']; ?></td>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['unit_number']; ?></td>
                            <td>৳<?php echo number_format($row['amount'], 0); ?></td>
                            <td><?php echo $row['payment_month']; ?></td>
                            <td><?php echo ucfirst($row['method']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div style="margin-top: 15px;">
                    <a href="owner_payments.php" class="btn btn-success">View All Payments</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php $conn->close(); ?>
</body>
</html>