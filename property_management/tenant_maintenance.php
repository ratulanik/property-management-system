<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'apartment_rental_system');
$tenant_id = $_SESSION['user_id'];

// Handle maintenance request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    $issue = $conn->real_escape_string($_POST['issue_description']);
    
    // Get tenant's current apartment
    $lease_query = "SELECT apartment_id FROM leases WHERE tenant_id = $tenant_id AND status = 'active' LIMIT 1";
    $lease_result = $conn->query($lease_query);
    
    if ($lease_result && $lease_result->num_rows > 0) {
        $apartment = $lease_result->fetch_assoc();
        $apartment_id = $apartment['apartment_id'];
        
        $insert_query = "INSERT INTO maintenance_requests (apartment_id, tenant_id, issue_description, status) 
                         VALUES ($apartment_id, $tenant_id, '$issue', 'pending')";
        
        if ($conn->query($insert_query)) {
            $success = "Maintenance request submitted successfully!";
        } else {
            $error = "Error submitting request: " . $conn->error;
        }
    } else {
        $error = "You don't have an active apartment lease!";
    }
}

// Get tenant's maintenance requests
$requests = [];
$requests_query = "SELECT * FROM maintenance_requests WHERE tenant_id = $tenant_id ORDER BY request_date DESC";
$requests_result = $conn->query($requests_query);

if ($requests_result) {
    while($row = $requests_result->fetch_assoc()) {
        $requests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Requests</title>
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
        
        .form-group { 
            margin-bottom: 20px; 
        }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            color: #555; 
            font-weight: 600; 
            font-size: 14px; 
        }
        
        textarea { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #e0e0e0; 
            border-radius: 8px; 
            font-size: 15px; 
            font-family: inherit;
            resize: vertical;
            min-height: 120px;
        }
        
        textarea:focus { 
            outline: none; 
            border-color: #27ae60; 
            box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1); 
        }
        
        .btn { 
            padding: 12px 24px; 
            border: none; 
            border-radius: 8px; 
            font-size: 15px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.3s; 
        }
        
        .btn-primary { 
            background: #27ae60; 
            color: white; 
        }
        
        .btn-primary:hover { 
            background: #219653; 
            transform: translateY(-2px); 
            box-shadow: 0 7px 14px rgba(0,0,0,0.1); 
        }
        
        .alert { 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
        }
        
        .alert-success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #b1dfbb; 
        }
        
        .alert-error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
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
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-in_progress {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-resolved {
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
        <a href="tenant_payments.php">💰 My Payments</a>
        <a href="tenant_maintenance.php" class="active">🔧 Maintenance</a>
        <a href="tenant_profile.php">👤 My Profile</a>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <h1>🔧 Maintenance Requests</h1>
        
        <?php if(isset($success)): ?>
        <div class="alert alert-success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
        <div class="alert alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Submit Request Form -->
        <div class="card">
            <h2>Submit New Maintenance Request</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Issue Description *</label>
                    <textarea name="issue_description" placeholder="Describe the issue in detail (e.g., Kitchen sink leaking, AC not working, Door lock broken, etc.)" required></textarea>
                </div>
                <button type="submit" name="submit_request" class="btn btn-primary">Submit Request</button>
            </form>
        </div>
        
        <!-- My Requests -->
        <div class="card">
            <h2>My Maintenance Requests</h2>
            
            <?php if(!empty($requests)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Date</th>
                        <th>Issue Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($requests as $request): ?>
                    <tr>
                        <td>#<?php echo $request['request_id']; ?></td>
                        <td><?php echo date('d M Y', strtotime($request['request_date'])); ?></td>
                        <td><?php echo $request['issue_description']; ?></td>
                        <td>
                            <?php
                            $status_class = 'status-' . $request['status'];
                            $status_text = str_replace('_', ' ', ucfirst($request['status']));
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">
                <span class="no-data-icon">🔧</span>
                <h3>No maintenance requests found</h3>
                <p>You haven't submitted any maintenance requests yet</p>
                <p style="margin-top: 20px; font-size: 14px; color: #999;">
                    Submit your first maintenance request using the form above
                </p>
            </div>
            <?php endif; ?>
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