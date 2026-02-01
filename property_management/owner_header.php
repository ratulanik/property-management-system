<?php
// owner_header.php - NO session_start()
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .owner-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .logo { font-size: 24px; font-weight: bold; }
        .user-info span { margin-right: 15px; }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.3); }
        .owner-nav {
            background: white;
            padding: 10px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .owner-nav a {
            color: #555;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .owner-nav a:hover { background: #f0f3ff; color: #667eea; }
        .owner-nav a.active { background: #f0f3ff; color: #667eea; font-weight: 600; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #b1dfbb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
        .btn-primary { background: #667eea; color: white; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; }
        .table { width: 100%; border-collapse: collapse; background: white; }
        .table th, .table td { padding: 12px; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="owner-header">
        <div class="logo">🏢 Apartment Management</div>
        <div class="user-info">
            <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="owner-nav">
        <a href="owner_dashboard.php">📊 Dashboard</a>
        <a href="owner_buildings.php">🏢 Buildings</a>
        <a href="owner_apartments.php">🏠 Apartments</a>
        <a href="owner_tenants.php">👤 Tenants</a>
        <a href="owner_payments.php">💰 Payments</a>
        <a href="owner_maintenance.php">🔧 Maintenance</a>
        <a href="owner_profile.php">👤 Profile</a>
    </div>
    
    <div class="container">