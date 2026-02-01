<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'apartment_rental_system');
$tenant_id = $_SESSION['user_id'];

// Get tenant info
$tenant = $conn->query("SELECT * FROM tenants WHERE tenant_id = $tenant_id")->fetch_assoc();

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $sql = "UPDATE tenants SET full_name = '$full_name', phone = '$phone', email = '$email' WHERE tenant_id = $tenant_id";
    
    if ($conn->query($sql)) {
        $success = "Profile updated successfully!";
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        // Refresh tenant data
        $tenant = $conn->query("SELECT * FROM tenants WHERE tenant_id = $tenant_id")->fetch_assoc();
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($current_password == $tenant['password']) {
        if ($new_password == $confirm_password) {
            $conn->query("UPDATE tenants SET password = '$new_password' WHERE tenant_id = $tenant_id");
            $password_success = "Password changed successfully!";
        } else {
            $password_error = "New passwords don't match!";
        }
    } else {
        $password_error = "Current password is incorrect!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
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
        
        .form-control { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #e0e0e0; 
            border-radius: 8px; 
            font-size: 15px; 
            transition: border 0.3s; 
        }
        
        .form-control:focus { 
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
        
        .stats-container { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-top: 20px; 
        }
        
        .stat-card { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 8px; 
            text-align: center; 
        }
        
        .stat-icon { 
            font-size: 32px; 
            margin-bottom: 10px; 
            color: #27ae60; 
        }
        
        .stat-number { 
            font-size: 36px; 
            font-weight: bold; 
            color: #2c3e50; 
            margin-bottom: 5px; 
        }
        
        .stat-label { 
            color: #7f8c8d; 
            font-size: 14px; 
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
        <a href="tenant_maintenance.php">🔧 Maintenance</a>
        <a href="tenant_profile.php" class="active">👤 My Profile</a>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <h1>👤 My Profile</h1>
        
        <?php if(isset($success)): ?>
        <div class="alert alert-success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
        <div class="alert alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Profile Information -->
        <div class="card">
            <h2>Personal Information</h2>
            <form method="POST">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo $tenant['full_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $tenant['email']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $tenant['phone']; ?>">
                    </div>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="card">
            <h2>Change Password</h2>
            
            <?php if(isset($password_success)): ?>
            <div class="alert alert-success">✅ <?php echo $password_success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($password_error)): ?>
            <div class="alert alert-error">❌ <?php echo $password_error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
            </form>
        </div>
        
        <!-- Account Statistics -->
        <div class="card">
            <h2>Account Statistics</h2>
            <div class="stats-container">
                <?php
                // Get tenant statistics
                $payments_count = $conn->query("
                    SELECT COUNT(*) as count FROM payments p
                    JOIN leases l ON p.lease_id = l.lease_id
                    WHERE l.tenant_id = $tenant_id
                ")->fetch_assoc()['count'];
                
                $maintenance_count = $conn->query("
                    SELECT COUNT(*) as count FROM maintenance_requests 
                    WHERE tenant_id = $tenant_id
                ")->fetch_assoc()['count'];
                
                $total_paid = $conn->query("
                    SELECT COALESCE(SUM(p.amount), 0) as total FROM payments p
                    JOIN leases l ON p.lease_id = l.lease_id
                    WHERE l.tenant_id = $tenant_id
                ")->fetch_assoc()['total'];
                ?>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-number"><?php echo $payments_count; ?></div>
                    <div class="stat-label">Payments Made</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🔧</div>
                    <div class="stat-number"><?php echo $maintenance_count; ?></div>
                    <div class="stat-label">Maintenance Requests</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💵</div>
                    <div class="stat-number">৳<?php echo number_format($total_paid, 0); ?></div>
                    <div class="stat-label">Total Paid</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number"><?php echo date('M Y', strtotime($tenant['created_at'])); ?></div>
                    <div class="stat-label">Member Since</div>
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