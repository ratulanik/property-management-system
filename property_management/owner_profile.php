<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'property_management');
$owner_id = $_SESSION['user_id'];

// Get owner details
$owner = $conn->query("SELECT * FROM owners WHERE owner_id = $owner_id")->fetch_assoc();

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $sql = "UPDATE owners SET full_name = '$full_name', phone = '$phone', email = '$email' WHERE owner_id = $owner_id";
    
    if ($conn->query($sql)) {
        $success = "Profile updated successfully!";
        // Update session
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        // Refresh owner data
        $owner = $conn->query("SELECT * FROM owners WHERE owner_id = $owner_id")->fetch_assoc();
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $conn->real_escape_string($_POST['current_password']);
    $new_password = $conn->real_escape_string($_POST['new_password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);
    
    // Check current password
    if ($current_password == $owner['password']) {
        if ($new_password == $confirm_password) {
            $sql = "UPDATE owners SET password = '$new_password' WHERE owner_id = $owner_id";
            if ($conn->query($sql)) {
                $password_success = "Password changed successfully!";
            }
        } else {
            $password_error = "New passwords don't match!";
        }
    } else {
        $password_error = "Current password is incorrect!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
</head>
<body>
    <?php include 'owner_header.php'; ?>
    
    <h2>👤 My Profile</h2>
    
    <?php if(isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Profile Information -->
    <div class="card">
        <h3>Personal Information</h3>
        <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo $owner['full_name']; ?>" required>
                </div>
                <div>
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $owner['email']; ?>" required>
                </div>
                <div>
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $owner['phone']; ?>">
                </div>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
    
    <!-- Change Password -->
    <div class="card">
        <h3>Change Password</h3>
        
        <?php if(isset($password_success)): ?>
        <div class="alert alert-success"><?php echo $password_success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($password_error)): ?>
        <div class="alert alert-error"><?php echo $password_error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div>
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div>
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div>
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>
            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
        </form>
    </div>
    
    <!-- Account Stats -->
    <div class="card">
        <h3>Account Statistics</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 32px; color: #667eea;">🏢</div>
                <div style="font-size: 36px; font-weight: bold;">
                    <?php echo $conn->query("SELECT COUNT(*) FROM buildings WHERE owner_id = $owner_id")->fetch_row()[0]; ?>
                </div>
                <div>Buildings</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 32px; color: #28a745;">🏠</div>
                <div style="font-size: 36px; font-weight: bold;">
                    <?php 
                    $result = $conn->query("
                        SELECT COUNT(*) FROM apartments a 
                        JOIN buildings b ON a.building_id = b.building_id 
                        WHERE b.owner_id = $owner_id
                    ");
                    echo $result->fetch_row()[0];
                    ?>
                </div>
                <div>Apartments</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 32px; color: #ffc107;">👥</div>
                <div style="font-size: 36px; font-weight: bold;">
                    <?php 
                    $result = $conn->query("
                        SELECT COUNT(DISTINCT t.tenant_id) 
                        FROM tenants t
                        JOIN leases l ON t.tenant_id = l.tenant_id
                        JOIN apartments a ON l.apartment_id = a.apartment_id
                        JOIN buildings b ON a.building_id = b.building_id
                        WHERE b.owner_id = $owner_id AND l.status = 'active'
                    ");
                    echo $result->fetch_row()[0];
                    ?>
                </div>
                <div>Active Tenants</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div style="font-size: 32px; color: #17a2b8;">📅</div>
                <div style="font-size: 36px; font-weight: bold;">
                    <?php echo date('d M Y', strtotime($owner['created_at'])); ?>
                </div>
                <div>Member Since</div>
            </div>
        </div>
    </div>
    
    <?php include 'owner_footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>