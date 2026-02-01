<?php
// owner_maintenance.php
session_start();

// Check if user is logged in as owner
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit();
}

// Database connection with error handling
$conn = new mysqli('localhost', 'root', '', 'property_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$owner_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Update status with prepared statement
if (isset($_POST['update_status'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'];
    
    // Check if request belongs to owner using prepared statement
    $check_sql = "
        SELECT mr.* FROM maintenance_requests mr
        JOIN apartments a ON mr.apartment_id = a.apartment_id
        JOIN buildings b ON a.building_id = b.building_id
        WHERE mr.request_id = ? AND b.owner_id = ?
    ";
    
    $stmt = $conn->prepare($check_sql);
    if ($stmt) {
        $stmt->bind_param("ii", $request_id, $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            // Update status with prepared statement
            $update_sql = "UPDATE maintenance_requests SET status = ? WHERE request_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt) {
                $update_stmt->bind_param("si", $status, $request_id);
                if ($update_stmt->execute()) {
                    $success_message = "Status updated successfully!";
                } else {
                    $error_message = "Error updating status: " . $conn->error;
                }
                $update_stmt->close();
            }
        } else {
            $error_message = "Maintenance request not found or access denied.";
        }
        $stmt->close();
    } else {
        $error_message = "Database error: " . $conn->error;
    }
}

// Get maintenance statistics
$stats = [
    'pending' => 0,
    'in_progress' => 0,
    'resolved' => 0,
    'total' => 0
];

$stats_sql = "
    SELECT 
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
        COUNT(*) as total
    FROM maintenance_requests mr
    JOIN apartments a ON mr.apartment_id = a.apartment_id
    JOIN buildings b ON a.building_id = b.building_id
    WHERE b.owner_id = ?
";

$stmt = $conn->prepare($stats_sql);
if ($stmt) {
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $stats = $result->fetch_assoc();
        // Ensure all values are set
        if (!$stats) {
            $stats = ['pending' => 0, 'in_progress' => 0, 'resolved' => 0, 'total' => 0];
        }
    }
    $stmt->close();
}

// Get maintenance requests with prepared statement
$requests = [];
$requests_sql = "
    SELECT mr.*, t.full_name as tenant_name, a.unit_number, b.building_name
    FROM maintenance_requests mr
    LEFT JOIN tenants t ON mr.tenant_id = t.tenant_id
    JOIN apartments a ON mr.apartment_id = a.apartment_id
    JOIN buildings b ON a.building_id = b.building_id
    WHERE b.owner_id = ?
    ORDER BY mr.request_date DESC
";

$stmt = $conn->prepare($requests_sql);
if ($stmt) {
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if query was successful BEFORE calling fetch_assoc()
    if ($result !== false) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    } else {
        $error_message = "Error fetching maintenance requests: " . $conn->error;
    }
    $stmt->close();
} else {
    $error_message = "Database query error: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Requests - Owner Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header h2 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        
        .card h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f1f1;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            text-align: center;
            padding: 25px 15px;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .table tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
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
        
        .select-status {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: white;
            font-size: 14px;
            margin-right: 10px;
            min-width: 140px;
        }
        
        .btn-update {
            padding: 8px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn-update:hover {
            background: #218838;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-icon {
            font-size: 70px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
        }
        
        .issue-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'owner_header.php'; ?>
        
        <div class="header">
            <h2>🔧 Maintenance Requests</h2>
            <p>View and manage maintenance requests from your properties</p>
        </div>
        
        <?php if($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="card">
            <h3>📊 Maintenance Overview</h3>
            <div class="stats-grid">
                <div class="stat-card" style="background: #fff3cd;">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-number"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
                <div class="stat-card" style="background: #cce5ff;">
                    <div class="stat-icon">🔧</div>
                    <div class="stat-number"><?php echo $stats['in_progress']; ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat-card" style="background: #d4edda;">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?php echo $stats['resolved']; ?></div>
                    <div class="stat-label">Resolved</div>
                </div>
                <div class="stat-card" style="background: #f8f9fa;">
                    <div class="stat-icon">📈</div>
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Requests</div>
                </div>
            </div>
        </div>
        
        <!-- Requests List -->
        <div class="card">
            <h3>📋 All Maintenance Requests</h3>
            <?php if(count($requests) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Tenant</th>
                            <th>Building</th>
                            <th>Unit</th>
                            <th>Issue</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($requests as $request): ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($request['request_id']); ?></strong></td>
                            <td><?php echo date('d M Y', strtotime($request['request_date'])); ?></td>
                            <td><?php echo htmlspecialchars($request['tenant_name'] ?: 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($request['building_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['unit_number']); ?></td>
                            <td class="issue-preview" title="<?php echo htmlspecialchars($request['issue_description']); ?>">
                                <?php echo htmlspecialchars(substr($request['issue_description'], 0, 100)); ?>...
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($request['status']); ?>">
                                    <?php echo str_replace('_', ' ', ucfirst($request['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                    <select name="status" class="select-status">
                                        <option value="pending" <?php echo $request['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $request['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $request['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🔧</div>
                    <h3>No maintenance requests found</h3>
                    <p>Maintenance requests from tenants will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'owner_footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>