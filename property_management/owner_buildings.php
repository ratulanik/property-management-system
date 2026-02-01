<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'property_management');
$owner_id = $_SESSION['user_id'];

// Add new building
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_building'])) {
    $building_name = $conn->real_escape_string($_POST['building_name']);
    $address = $conn->real_escape_string($_POST['address']);
    $total_floors = (int)$_POST['total_floors'];
    
    $sql = "INSERT INTO buildings (owner_id, building_name, address, total_floors) 
            VALUES ('$owner_id', '$building_name', '$address', '$total_floors')";
    
    if ($conn->query($sql)) {
        $success = "Building added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Delete building
if (isset($_GET['delete'])) {
    $building_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM buildings WHERE building_id = $building_id AND owner_id = $owner_id");
    header("Location: owner_buildings.php?msg=deleted");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Buildings</title>
    <style>
        /* Use your existing dashboard CSS */
        .container { padding: 20px; }
        .form-container { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn-danger { background: #dc3545; }
        .table { width: 100%; background: white; border-collapse: collapse; }
        .table th, .table td { padding: 12px; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; }
        .actions a { margin-right: 10px; }
    </style>
</head>
<body>
    <?php include 'owner_header.php'; ?>
    
    <div class="container">
        <h2>🏢 My Buildings</h2>
        
        <?php if(isset($success)): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <!-- Add Building Form -->
        <div class="form-container">
            <h3>Add New Building</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Building Name</label>
                    <input type="text" name="building_name" required>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" required>
                </div>
                <div class="form-group">
                    <label>Total Floors</label>
                    <input type="number" name="total_floors" min="1" required>
                </div>
                <button type="submit" name="add_building" class="btn">Add Building</button>
            </form>
        </div>
        
        <!-- Buildings List -->
        <div class="form-container">
            <h3>My Buildings List</h3>
            <?php
            $buildings = $conn->query("SELECT * FROM buildings WHERE owner_id = $owner_id ORDER BY building_name");
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Building Name</th>
                        <th>Address</th>
                        <th>Floors</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($building = $buildings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $building['building_id']; ?></td>
                        <td><?php echo $building['building_name']; ?></td>
                        <td><?php echo $building['address']; ?></td>
                        <td><?php echo $building['total_floors']; ?></td>
                        <td><?php echo date('d M Y', strtotime($building['created_at'])); ?></td>
                        <td class="actions">
                            <a href="owner_apartments.php?building_id=<?php echo $building['building_id']; ?>">View Apartments</a>
                            <a href="?delete=<?php echo $building['building_id']; ?>" 
                               onclick="return confirm('Delete this building?')" 
                               style="color: #dc3545;">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>