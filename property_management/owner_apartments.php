<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'property_management');
$owner_id = $_SESSION['user_id'];

// Initialize variables
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_apartment'])) {
        // Add new apartment
        $building_id = (int)$_POST['building_id'];
        $unit_number = $conn->real_escape_string($_POST['unit_number']);
        $floor = (int)$_POST['floor'];
        $bedrooms = (int)$_POST['bedrooms'];
        $bathrooms = (int)$_POST['bathrooms'];
        $rent = (float)$_POST['rent'];
        $status = $conn->real_escape_string($_POST['status']);
        $apartment_number = $conn->real_escape_string($_POST['apartment_number']);
        
        // Check if building belongs to owner
        $check = $conn->query("SELECT * FROM buildings WHERE building_id = $building_id AND owner_id = $owner_id");
        if ($check->num_rows > 0) {
            $sql = "INSERT INTO apartments (building_id, unit_number, floor, bedrooms, bathrooms, rent, status, apartment_number) 
                    VALUES ('$building_id', '$unit_number', '$floor', '$bedrooms', '$bathrooms', '$rent', '$status', '$apartment_number')";
            
            if ($conn->query($sql)) {
                $success = "Apartment added successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        } else {
            $error = "Invalid building selection!";
        }
    }
    
    if (isset($_POST['update_apartment'])) {
        // Update apartment
        $apartment_id = (int)$_POST['apartment_id'];
        $rent = (float)$_POST['rent'];
        $status = $conn->real_escape_string($_POST['status']);
        
        // Check if apartment belongs to owner
        $check = $conn->query("
            SELECT a.* FROM apartments a 
            JOIN buildings b ON a.building_id = b.building_id 
            WHERE a.apartment_id = $apartment_id AND b.owner_id = $owner_id
        ");
        
        if ($check->num_rows > 0) {
            $sql = "UPDATE apartments SET rent = '$rent', status = '$status' WHERE apartment_id = $apartment_id";
            
            if ($conn->query($sql)) {
                $success = "Apartment updated successfully!";
            } else {
                $error = "Error updating: " . $conn->error;
            }
        } else {
            $error = "Apartment not found or access denied!";
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $apartment_id = (int)$_GET['delete'];
    
    // Check ownership before delete
    $check = $conn->query("
        SELECT a.* FROM apartments a 
        JOIN buildings b ON a.building_id = b.building_id 
        WHERE a.apartment_id = $apartment_id AND b.owner_id = $owner_id
    ");
    
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM apartments WHERE apartment_id = $apartment_id");
        $success = "Apartment deleted successfully!";
    }
}

// Get filter parameters
$building_filter = isset($_GET['building']) ? (int)$_GET['building'] : 0;
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apartments Management</title>
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
        
        /* Header (use same as owner_dashboard) */
        .owner-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .user-info span {
            margin-right: 15px;
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
        
        /* Navigation */
        .owner-nav {
            background: white;
            padding: 10px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 20px;
        }
        
        .owner-nav a {
            color: #555;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .owner-nav a:hover {
            background: #f0f3ff;
            color: #667eea;
        }
        
        .owner-nav a.active {
            background: #f0f3ff;
            color: #667eea;
            font-weight: 600;
        }
        
        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .page-title {
            color: #2c3e50;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .page-title h1 {
            font-size: 28px;
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f5f7fa;
        }
        
        /* Forms */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border 0.3s;
            background: #fafafa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23667eea' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 40px;
        }
        
        /* Buttons */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0,0,0,0.1);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #b1dfbb;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Tables */
        .table-container {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table thead {
            background: #f8f9fa;
        }
        
        .table th {
            padding: 15px;
            text-align: left;
            color: #555;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .table tbody tr:hover {
            background: #f9f9f9;
        }
        
        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-available {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rented {
            background: #fff3cd;
            color: #856404;
        }
        
        /* Filter Section */
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="owner-header">
        <div class="logo">🏢 Apartment Management</div>
        <div class="user-info">
            <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
            <span><?php echo $_SESSION['email']; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="owner-nav">
        <a href="owner_dashboard.php">📊 Dashboard</a>
        <a href="owner_buildings.php">🏢 Buildings</a>
        <a href="owner_apartments.php" class="active">🏠 Apartments</a>
        <a href="owner_tenants.php">👤 Tenants</a>
        <a href="owner_payments.php">💰 Payments</a>
        <a href="owner_maintenance.php">🔧 Maintenance</a>
        <a href="owner_profile.php">👤 Profile</a>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <!-- Page Title -->
        <div class="page-title">
            <h1>🏠 Apartments Management</h1>
        </div>
        
        <!-- Messages -->
        <?php if($success): ?>
        <div class="alert alert-success">
            ✅ <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="alert alert-error">
            ❌ <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="stats-container">
            <?php
            // Get statistics
            $total_apartments = $conn->query("
                SELECT COUNT(*) as total FROM apartments a 
                JOIN buildings b ON a.building_id = b.building_id 
                WHERE b.owner_id = $owner_id
            ")->fetch_assoc()['total'];
            
            $available_apartments = $conn->query("
                SELECT COUNT(*) as total FROM apartments a 
                JOIN buildings b ON a.building_id = b.building_id 
                WHERE b.owner_id = $owner_id AND a.status = 'available'
            ")->fetch_assoc()['total'];
            
            $rented_apartments = $conn->query("
                SELECT COUNT(*) as total FROM apartments a 
                JOIN buildings b ON a.building_id = b.building_id 
                WHERE b.owner_id = $owner_id AND a.status = 'rented'
            ")->fetch_assoc()['total'];
            
            $total_revenue = $conn->query("
                SELECT COALESCE(SUM(l.monthly_rent), 0) as revenue 
                FROM leases l
                JOIN apartments a ON l.apartment_id = a.apartment_id
                JOIN buildings b ON a.building_id = b.building_id
                WHERE b.owner_id = $owner_id AND l.status = 'active'
            ")->fetch_assoc()['revenue'];
            ?>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_apartments; ?></div>
                <div class="stat-label">Total Apartments</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $available_apartments; ?></div>
                <div class="stat-label">Available</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $rented_apartments; ?></div>
                <div class="stat-label">Rented</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">৳<?php echo number_format($total_revenue, 0); ?></div>
                <div class="stat-label">Monthly Revenue</div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <h3>Filter Apartments</h3>
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Building</label>
                    <select name="building" class="form-control" onchange="this.form.submit()">
                        <option value="0">All Buildings</option>
                        <?php
                        $buildings = $conn->query("SELECT * FROM buildings WHERE owner_id = $owner_id ORDER BY building_name");
                        while($building = $buildings->fetch_assoc()):
                        ?>
                        <option value="<?php echo $building['building_id']; ?>" 
                            <?php echo $building_filter == $building['building_id'] ? 'selected' : ''; ?>>
                            <?php echo $building['building_name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="available" <?php echo $status_filter == 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="rented" <?php echo $status_filter == 'rented' ? 'selected' : ''; ?>>Rented</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="button" class="btn btn-primary" onclick="window.location.href='owner_apartments.php'">
                        🔄 Reset Filters
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Add Apartment Form -->
        <div class="card">
            <h2>➕ Add New Apartment</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Building *</label>
                        <select name="building_id" class="form-control" required>
                            <option value="">Select Building</option>
                            <?php
                            $buildings = $conn->query("SELECT * FROM buildings WHERE owner_id = $owner_id ORDER BY building_name");
                            while($building = $buildings->fetch_assoc()):
                            ?>
                            <option value="<?php echo $building['building_id']; ?>">
                                <?php echo $building['building_name']; ?> (<?php echo $building['address']; ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Unit Number *</label>
                        <input type="text" name="unit_number" class="form-control" required placeholder="e.g., A-101">
                    </div>
                    
                    <div class="form-group">
                        <label>Apartment Number</label>
                        <input type="text" name="apartment_number" class="form-control" placeholder="Optional">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Floor *</label>
                        <input type="number" name="floor" class="form-control" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label>Bedrooms *</label>
                        <input type="number" name="bedrooms" class="form-control" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label>Bathrooms *</label>
                        <input type="number" name="bathrooms" class="form-control" required min="1" step="0.5">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Monthly Rent (BDT) *</label>
                        <input type="number" name="rent" class="form-control" required min="0" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="available">Available</option>
                            <option value="rented">Rented</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" name="add_apartment" class="btn btn-primary">
                    ➕ Add Apartment
                </button>
            </form>
        </div>
        
        <!-- Apartments List -->
        <div class="card">
            <h2>📋 All Apartments</h2>
            <div class="table-container">
                <?php
                // Build query with filters
                $query = "
                    SELECT a.*, b.building_name, b.address,
                           (SELECT COUNT(*) FROM leases l WHERE l.apartment_id = a.apartment_id AND l.status = 'active') as active_leases
                    FROM apartments a 
                    JOIN buildings b ON a.building_id = b.building_id 
                    WHERE b.owner_id = $owner_id
                ";
                
                if ($building_filter > 0) {
                    $query .= " AND a.building_id = $building_filter";
                }
                
                if ($status_filter != '') {
                    $query .= " AND a.status = '$status_filter'";
                }
                
                $query .= " ORDER BY a.building_id, a.floor, a.unit_number";
                
                $result = $conn->query($query);
                ?>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Unit No</th>
                            <th>Building</th>
                            <th>Floor</th>
                            <th>Type</th>
                            <th>Rent</th>
                            <th>Status</th>
                            <th>Lease</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($apartment = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $apartment['apartment_id']; ?></td>
                                <td>
                                    <strong><?php echo $apartment['unit_number']; ?></strong>
                                    <?php if($apartment['apartment_number']): ?>
                                    <br><small>#<?php echo $apartment['apartment_number']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $apartment['building_name']; ?>
                                    <br><small><?php echo substr($apartment['address'], 0, 30); ?>...</small>
                                </td>
                                <td><?php echo $apartment['floor']; ?></td>
                                <td>
                                    <?php echo $apartment['bedrooms']; ?> Beds,
                                    <?php echo $apartment['bathrooms']; ?> Baths
                                </td>
                                <td>
                                    <strong>৳<?php echo number_format($apartment['rent'], 0); ?></strong>
                                    <br><small>per month</small>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $apartment['status']; ?>">
                                        <?php echo ucfirst($apartment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($apartment['active_leases'] > 0): ?>
                                        <span style="color: #28a745;">● Active</span>
                                    <?php else: ?>
                                        <span style="color: #6c757d;">○ No Lease</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Quick Edit Modal Trigger -->
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick="openEditModal(<?php echo $apartment['apartment_id']; ?>, <?php echo $apartment['rent']; ?>, '<?php echo $apartment['status']; ?>')">
                                            ✏️ Edit
                                        </button>
                                        
                                        <?php if($apartment['active_leases'] == 0): ?>
                                        <a href="?delete=<?php echo $apartment['apartment_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Delete this apartment?')">
                                            🗑️ Delete
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if($apartment['status'] == 'available'): ?>
                                        <a href="owner_leases.php?add=<?php echo $apartment['apartment_id']; ?>" 
                                           class="btn btn-sm btn-success">
                                            📝 Add Lease
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: #6c757d;">
                                    <div style="font-size: 48px; margin-bottom: 10px;">🏠</div>
                                    <h3>No apartments found</h3>
                                    <p>Add your first apartment using the form above</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Export Button -->
            <div style="margin-top: 20px; text-align: right;">
                <button class="btn" onclick="exportToExcel()">
                    📊 Export to Excel
                </button>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>✏️ Edit Apartment</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="apartment_id" id="editApartmentId">
                
                <div class="form-group">
                    <label>Monthly Rent (BDT)</label>
                    <input type="number" name="rent" id="editRent" class="form-control" required min="0" step="0.01">
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="editStatus" class="form-control" required>
                        <option value="available">Available</option>
                        <option value="rented">Rented</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="update_apartment" class="btn btn-primary">
                        💾 Save Changes
                    </button>
                    <button type="button" class="btn" onclick="closeEditModal()">
                        ❌ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Edit Modal Functions
        function openEditModal(id, rent, status) {
            document.getElementById('editApartmentId').value = id;
            document.getElementById('editRent').value = rent;
            document.getElementById('editStatus').value = status;
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
        
        // Export to Excel function
        function exportToExcel() {
            // Create a simple HTML table export
            var table = document.querySelector('.table');
            var html = table.outerHTML;
            
            // Create download link
            var blob = new Blob([html], {type: 'application/vnd.ms-excel'});
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'apartments_' + new Date().toISOString().split('T')[0] + '.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
        
        // Auto-close alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>

<?php $conn->close(); ?>