<?php
// tenant_header.php - NO session_start()
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
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
        .tenant-nav a:hover { background: #e9f7ef; color: #27ae60; }
        .tenant-nav a.active { background: #e9f7ef; color: #27ae60; font-weight: 600; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="tenant-header">
        <div style="font-size: 24px; font-weight: bold;">🏠 Tenant Portal</div>
        <div>
            <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
            <a href="logout.php" style="background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; margin-left: 15px;">Logout</a>
        </div>
    </div>
    
    <div class="tenant-nav">
        <a href="tenant_dashboard.php">📊 Dashboard</a>
        <a href="tenant_apartment.php">🏠 My Apartment</a>
        <a href="tenant_payments.php">💰 My Payments</a>
        <a href="tenant_maintenance.php">🔧 Maintenance</a>
        <a href="tenant_profile.php">👤 My Profile</a>
    </div>
    
    <div class="container">